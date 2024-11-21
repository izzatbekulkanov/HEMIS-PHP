<?php

namespace common\models\system;

use common\components\Config;
use common\components\Translator;
use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\Expression;

/**
 * This is the model class for table "e_system_message".
 *
 * @property integer $id
 * @property string $category
 * @property string $message
 *
 * @property SystemMessageTranslation[] $systemMessageTranslation
 */
class SystemMessage extends ActiveRecord
{
    const CACHE_TAG = 'messages';
    public $search;
    public $language = Config::LANGUAGE_DEFAULT;
    public $translation;
    public $lang;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'e_system_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category'], 'string', 'max' => 128],
            [['message'], 'string', 'max' => 4096],
            [['search', 'language'], 'safe'],
            [['lang'], 'safe'],
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->invalidateTranslation();
        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        $this->invalidateTranslation();
        parent::afterDelete();
    }


    public static function invalidateTranslation()
    {
        //TODO
        // TagDependency::invalidate(Yii::$app->cache, [self::CACHE_TAG]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => __('ID'),
            'category' => __('Category'),
            'translation' => __('Translation'),
            'language' => __('Language'),
            'message' => __('Message'),
            'search' => __('Search by Message / Translation'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSystemMessageTranslation()
    {
        return $this->hasMany(SystemMessageTranslation::className(), ['id' => 'id']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find()
            ->select(['m.*', 'translation' => 't.translation'])
            ->from('e_system_message m')
            ->leftJoin('e_system_message_translation t', "t.id = m.id and t.language = '{$this->language}'");


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['category' => SORT_ASC, 'id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'translation',
                    'message',
                    'category',
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->where(new Expression('lower(message) like lower(:search) or lower(translation) like lower(:search)', ['search' => '%' . $this->search . '%']));
        }

        return $dataProvider;
    }

    public static function addTranslations($data)
    {
        $success = 0;
        $languages = array_keys(Config::getLanguageOptions());

        if (count($data)) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                foreach ($data as $item) {
                    if (isset($item['message']) && $item['message']) {
                        $keys = [
                            'category' => isset($item['category']) && $item['category'] ? $item['category'] : 'app',
                            'message' => $item['message'],
                        ];
                        if ($message = self::findOne($keys)) {
                            SystemMessageTranslation::deleteAll(['id' => $message->id]);
                        }

                        if (!$message) {
                            $message = new self($keys);
                        }

                        $trans = [];
                        if ($message->save()) {
                            foreach ($languages as $language) {
                                if ($message->id !== null && isset($item[$language]) && !empty($item[$language])) {
                                    $trans[] = [
                                        'id' => $message->id,
                                        'language' => $language,
                                        'translation' => $item[$language],
                                    ];
                                }
                            }
                            $success++;
                        }

                        if (count($trans)) {
                            Yii::$app->db
                                ->createCommand()
                                ->batchInsert(SystemMessageTranslation::tableName(), ['id', 'language', 'translation'], $trans)
                                ->execute();
                        }
                    }
                }

                $transaction->commit();
                self::invalidateTranslation();

                return $success;
            } catch (Exception $e) {
                $transaction->rollBack();
                throw new Exception($e->getMessage());
            }
        }
        return $success;
    }

    public function loadTranslations()
    {
        foreach ($this->systemMessageTranslation as $translation) {
            $this->lang[$translation->language] = trim($translation->translation);
        }
    }

    public function updateTranslation()
    {
        $data = [];
        foreach ($this->lang as $lang => $value) {
            $data[$lang] = trim($value);
        }

        return $this->addTranslation($data);
    }

    public function addTranslation($data)
    {
        $insert = [];

        foreach ($data as $lang => $translation) {
            $insert[] = [
                'id' => $this->id,
                'language' => $lang,
                'translation' => $translation,
            ];
        }

        if (count($insert)) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                Yii::$app->db
                    ->createCommand()
                    ->delete(SystemMessageTranslation::tableName(), ['id' => $this->id])
                    ->execute();

                Yii::$app->db
                    ->createCommand()
                    ->batchInsert(SystemMessageTranslation::tableName(), ['id', 'language', 'translation'], $insert)
                    ->execute();

                $transaction->commit();
                $this->invalidateTranslation();
                return true;
            } catch (Exception $e) {
                $transaction->rollBack();
            }
        }
        return false;
    }


    public function transliterateUzbek()
    {
        $latin = false;
        $cyrill = false;

        foreach ($this->systemMessageTranslation as $translation) {
            if ($translation->language == Config::LANGUAGE_UZBEK) {
                $latin = trim($translation->translation);
            }
            if ($translation->language == Config::LANGUAGE_CYRILLIC) {
                $cyrill = trim($translation->translation);
            }
        }

        if ($latin && !$cyrill) {
            $newTranslation = new SystemMessageTranslation([
                'id' => $this->id,
            ]);


            $newTranslation->language = Config::LANGUAGE_CYRILLIC;
            $newTranslation->translation = Translator::getInstance()->translateToCyrillic($latin);
            return $newTranslation->save();
        }

        if (!$latin && $cyrill) {
            $newTranslation = new SystemMessageTranslation([
                'id' => $this->id,
            ]);

            $newTranslation->language = Config::LANGUAGE_UZBEK;
            $newTranslation->translation = Translator::getInstance()->translateToLatin($cyrill);
            return $newTranslation->save();
        }

        return false;
    }

}
