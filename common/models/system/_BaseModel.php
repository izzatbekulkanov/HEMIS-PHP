<?php

namespace common\models\system;

use common\components\Config;
use common\components\db\PgQuery;
use common\components\file\InterlacedImage;
use common\components\Translator;
use DateTime;
use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\IntegrityException;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Class MongoModel
 * @property integer id
 * @property string[] _translations
 * @property string[] _options
 * @property DateTime created_at
 * @property integer created_by
 * @property DateTime updated_at
 * @property integer updated_by
 * @package common\models
 */
class _BaseModel extends ActiveRecord
{
    public $search;

    protected $_searchableAttributes = [];
    protected $_translatedAttributes = [];

    const SCENARIO_SEARCH = 'search';
    const SCENARIO_INSERT = 'insert';
    const SCENARIO_UPDATE = 'update';

    public function rules()
    {
        return [
            ['search', 'safe']
        ];
    }

    public function attributeLabels()
    {
        $labels = [
            'confirmation' => __('Password Confirmation'),
            '_specialty' => __('Specialty'),
            '_department' => __('Faculty'),
        ];
        foreach ($this->attributes() as $attribute) {
            $labels[$attribute] = __(Inflector::camel2words($attribute)) . (in_array($attribute, $this->_translatedAttributes) ? ' º' : '');
        }
        return $labels;
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => function () {
                    return $this->getTimestampValue();
                },
            ],
        ];
    }

    public function getTimestampValue()
    {
        return new DateTime('now');
    }

    public function getTimestampValueFormatted()
    {
        return $this->getTimestampValue()->format('Y-m-d H:i:s');
    }

    public function getId()
    {
        return (string)$this->id;
    }

    /**
     * @return PgQuery
     */
    public static function find()
    {
        return \Yii::createObject(PgQuery::class, [get_called_class()]);
    }

    public function search($params)
    {
        $this->load($params);
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => $this->attributes()
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);


        if ($this->search) {
            foreach ($this->_searchableAttributes as $attribute) {
                if ($this->hasAttribute('_translations') && in_array($attribute, $this->_translatedAttributes)) {
                    $query->orWhereLike("{$attribute}_uz", $this->search, '_translations');
                    $query->orWhereLike("{$attribute}_oz", $this->search, '_translations');
                    $query->orWhereLike("{$attribute}_ru", $this->search, '_translations');
                }
                $query->orWhereLike($attribute, $this->search);
            }
        }

        return $dataProvider;
    }

    public function beforeSave($insert)
    {
        if ($this->hasAttribute('_translations')) {
            $translations = $this->_translations ?: [];
            $language = Config::getLanguageCode();

            foreach ($this->_translatedAttributes as $attributeCode) {
                $translationAttribute = self::getLanguageAttributeCode($attributeCode, $language);

                if ($this->isNewRecord || $this->isAttributeChanged($attributeCode) || !isset($translations[$translationAttribute])) {
                    $translations[$translationAttribute] = $this->getAttribute($attributeCode);
                }
            }

            $this->_translations = $translations;
        }


        return parent::beforeSave($insert);
    }


    public function afterFind()
    {
        if ($this->hasAttribute('_translations')) {
            $translations = $this->_translations;
            $language = Config::getLanguageCode();

            foreach ($this->_translatedAttributes as $attributeCode) {
                $t = $attributeCode . '_' . $language;
                if (isset($translations[$t]) && $translations[$t] !== "" && $translations[$t] !== null) {
                    $this->setAttribute($attributeCode, $translations[$t]);
                } elseif ($language != 'uz') {
                    $t = $attributeCode . '_uz';
                    if (isset($translations[$t]) && $translations[$t] !== "" && $translations[$t] !== null) {
                        $this->setAttribute($attributeCode, $translations[$t]);
                    }
                }
            }
        }

        parent::afterFind();
    }

    public function setTranslation($attributeCode, $value, $language)
    {
        if ($this->hasAttribute('_translations')) {
            $translations = $this->_translations;
            $languageCode = Config::getLanguageCode($language);
            $attributeName = self::getLanguageAttributeCode($attributeCode, $languageCode);
            $translations[$attributeName] = $value;
            if (Yii::$app->language == $language)
                $this->$attributeCode = $value;
            $this->_translations = $translations;
        }
    }

    public function getTranslation($attributeCode, $language, $empty = false)
    {
        if ($this->hasAttribute('_translations')) {
            $languageCode = Config::getLanguageCode($language);
            $attributeName = self::getLanguageAttributeCode($attributeCode, $languageCode);
            $translations = $this->_translations;
            if (isset($translations[$attributeName])) {
                return $translations[$attributeName];
            }

            if ($empty) return '';
        }

        return $this->$attributeCode;
    }

    public function getTranslationUzbek($attributeCode)
    {
        return $this->getTranslation($attributeCode, Config::LANGUAGE_UZBEK);
    }

    public function getTranslationRussian($attributeCode)
    {
        return $this->getTranslation($attributeCode, Config::LANGUAGE_RUSSIAN);
    }

    public function getAllTranslations($attributeCode)
    {
        $result = [];
        foreach (Config::getLanguageOptions() as $language => $languageOption) {
            $result[$language] = $this->getTranslation($attributeCode, $language);
        }

        return $result;
    }

    public function dataToImage($match)
    {
        list(, $img, $type, $base64, $end) = $match;
        $bin = base64_decode($base64);
        $name = uniqid() . '.' . $type;

        $path = chr(96 + rand(1, 26)) . DS . chr(96 + rand(1, 26)) . DS;
        $dir = Yii::getAlias("@static/uploads/") . DS;

        if (!is_dir($dir . $path)) {
            FileHelper::createDirectory($dir . $path, 0777);
        }

        file_exists($dir . $path . $name) or file_put_contents($dir . $path . $name, $bin);

        $url = Yii::getAlias("@staticUrl/uploads/") . $path . $name;

        return "$img$url$end";
    }

    public static function checkFileExists($path, $dir = false, $fullPath = false)
    {
        $dir = $dir ?: \Yii::getAlias("@static") . DS . 'uploads' . DS;
        if (!empty($path)) {
            $path = $dir . $path;
            return file_exists($path) ? ($fullPath ? $path : true) : false;
        }

        return false;
    }

    protected function convertLatinQuotas($value)
    {
        if (is_string($value)) {
            return $this->_convertLatinQuotas($value);
        } elseif (is_array($value)) {
            $result = [];

            foreach ($value as $key => $item) {
                $result[$key] = $this->_convertLatinQuotas($item);
            }

            return $result;
        }

        return $value;
    }

    private function _convertLatinQuotas($value)
    {
        $value = str_replace(array_keys(Translator::$letters), array_values(Translator::$letters), $value);
        return $value;
    }

    public static function getLanguageAttributeCode($attr, $lang = false, $prefix = '_')
    {
        $lang = $lang ?: Config::getLanguageCode();
        return $attr . $prefix . $lang;
    }

    public function syncLatinCyrill($toLanguage, $update = false)
    {
        if (Config::isLatinCyrill() && $this->hasAttribute('_translations')) {
            if ($toLanguage == Config::LANGUAGE_UZBEK) {
                foreach ($this->_translatedAttributes as $attribute) {
                    $value = $this->getTranslation($attribute, Config::LANGUAGE_CYRILLIC);
                    $value = Translator::getInstance()->translateToLatin($value);
                    $this->setTranslation($attribute, $value, Config::LANGUAGE_UZBEK);
                }
                if ($update)
                    $this->updateAttributes(['_translations' => $this->_translations]);

                return 1;
            } elseif ($toLanguage == Config::LANGUAGE_CYRILLIC) {
                foreach ($this->_translatedAttributes as $attribute) {
                    $value = $this->getTranslation($attribute, Config::LANGUAGE_UZBEK);
                    $value = Translator::getInstance()->translateToCyrillic($value);
                    $this->setTranslation($attribute, $value, Config::LANGUAGE_CYRILLIC);
                }
                if ($update)
                    $this->updateAttributes(['_translations' => $this->_translations]);

                return 1;
            }
        }
    }


    public function getUploadUrl($file, $clean = false)
    {
        if (is_array($file) && isset($file['path'])) {
            if ($clean)
                $attribute['path'] = preg_replace('/[\d]{2,4}_[\d]{2,4}_/', '', $file['path']);

            $filePath = Yii::getAlias("@static/uploads/") . $file['path'];
            if (file_exists($filePath))
                return Yii::getAlias("@staticUrl/uploads/") . $file['path'];
        }
    }


    public function getUploadPath($file, $clean = false)
    {
        if (is_array($file) && isset($file['path'])) {

            if ($clean)
                $attribute['path'] = preg_replace('/[\d]{2,4}_[\d]{2,4}_/', '', $file['path']);

            $path = Yii::getAlias("@static/uploads/") . $file['path'];
            return file_exists($path) ? $path : false;
        }

        return false;
    }

    public function getFileUrl($attribute = false, $clean = false)
    {
        if ($this->hasAttribute($attribute)) {
            $attribute = $this->getAttribute($attribute);
            return $this->getUploadUrl($attribute, $clean);
        }

        return false;
    }

    public function getFilePath($attribute, $clean = false)
    {
        if ($this->hasAttribute($attribute)) {
            $attribute = $this->$attribute;
            return $this->getUploadPath($attribute, $clean);

        }

        return false;
    }

    protected static $_alpha = [
        '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i',
        'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'j',
        's', 'u', 'u', 'v', 'w', 'x', 'y', 'z', '0',
    ];

    public static function offerRandomSequence($length = 3)
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= self::$_alpha[rand(0, count(self::$_alpha) - 1)];
        }

        return $result;
    }

    public static function getCropImage($img = [], $width = 270, $height = 347, $manipulation = ManipulatorInterface::THUMBNAIL_OUTBOUND, $watermark = false, $quality = 90)
    {
        $cropDir = \Yii::getAlias("@static") . DS . 'crop' . DS;

        if (!is_dir($cropDir)) {
            FileHelper::createDirectory($cropDir, 0777);
        }

        $imagePath = Yii::getAlias('@backend/assets/app/img/profile.jpg');
        $filename = pathinfo($imagePath)['filename'];
        if (is_array($img) && isset($img['path']) && self::checkFileExists($img['path'])) {
            $img['path'] = preg_replace('/[\d]{2,4}_[\d]{2,4}_/', '', $img['path']);
            $imagePath = self::checkFileExists($img['path'], false, true);
            $filename = $img['path'];
        }

        $info = pathinfo($imagePath);
        if (!isset($info['extension']) || !in_array($info['extension'], ['gif', 'jpg', 'jpeg', 'png', 'wbmp', 'xbm'])) $info['extension'] = 'jpg';
        $imageName = crc32($filename) . '.' . $info['extension'];

        $cropPath = $imageName[0] . DS . $imageName[1] . DS;
        $cropName = $width . '_' . $height . '_' . $quality . '_' . $imageName;
        $cropFull = $cropDir . $cropPath . $cropName;

        $cropUrl = \Yii::getAlias('@staticUrl/crop/') . $cropPath . $cropName;

        if (!file_exists($cropFull)) {
            if (!is_dir($cropDir . $cropPath)) {
                FileHelper::createDirectory($cropDir . $cropPath, 0777);
            }

            if (file_exists($imagePath)) {
                if ($watermark) {
                    InterlacedImage::thumbnailWithWatermark($imagePath, $width, $height, $manipulation)
                        ->save($cropFull, ['quality' => $quality]);
                } else {
                    InterlacedImage::thumbnail($imagePath, $width, $height, $manipulation)
                        ->save($cropFull, ['quality' => $quality]);
                }
            }
        }

        return $cropUrl;
    }

    /**
     * @param mixed $condition
     * @return static
     * @throws
     */
    public static function findOne($condition)
    {
        return static::findByCondition($condition)->limit(1)->one();
    }


    public function getImageUrl($width = 120, $height = 120, $manipulation = 1)
    {
        if ($this->hasAttribute('image')) {
            $manipulation = $manipulation == 1 ? ManipulatorInterface::THUMBNAIL_OUTBOUND : ManipulatorInterface::THUMBNAIL_INSET;
            return self::getCropImage($this->image, $width, $height, $manipulation);
        }
    }

    public static function setAllShouldBeSynced($ids)
    {
        return self::updateAll(['_sync' => false], ['id' => $ids]);
    }

    public function setAsShouldBeSynced()
    {
        return $this->hasAttribute('_sync') ? $this->updateAttributes(['_sync' => false]) : 0;
    }

    public function setAsSyncPerformed()
    {
        return $this->hasAttribute('_sync') ? $this->updateAttributes(['_sync' => true, '_qid' => null]) : 0;
    }


    public function anyIssueWithDelete()
    {
        try {
            if ($this->delete()) {
                return false;
            }
        } catch (IntegrityException $e) {
            $message = __('Could not delete related data');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return $message;
    }

    public function tryToDelete(callable $function, &$message, callable $beforeDelete = null)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($beforeDelete == null || call_user_func($beforeDelete)) {
                if ($this->delete()) {
                    if (call_user_func($function)) {
                        $transaction->commit();
                        return true;
                    }
                }
            }

        } catch (IntegrityException $e) {
            $message = __('Could not delete related data');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $transaction->rollBack();
        return false;
    }

    public function getOneError()
    {
        $errors = $this->getFirstErrors();
        return array_shift($errors);
    }

    public function getCharMap()
    {
        return [
            "&ldquo;" => '"',
            "&rdquo;" => '"',
            "o'" => "o‘",
            "o`" => "o‘",
            "o’" => "o‘",
            "O'" => "O‘",
            "O`" => "O‘",
            "O’" => "O‘",
            "g'" => "g‘",
            "g`" => "g‘",
            "g’" => "g‘",
            "G'" => "G‘",
            "G`" => "G‘",
            "G’" => "G‘",
            // "`" => "’",
            '$с$' => '$c$',
        ];
    }

    public function getShortTitle($len = 6)
    {
        return StringHelper::truncateWords($this->hasAttribute('name') ? $this->name : $this->title, $len);
    }
}