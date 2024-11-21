<?php

namespace common\models\system;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "e_system_message_translation".
 *
 * @property integer $id
 * @property string $language
 * @property string $translation
 *
 * @property SystemMessage $id0
 */
class SystemMessageTranslation extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'e_system_message_translation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['language'], 'string', 'max' => 16],
            [['translation'], 'string', 'max' => 4096],
            [['id', 'language'], 'unique', 'targetAttribute' => ['id', 'language'], 'message' => 'The combination of ID and Language has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => __('ID'),
            'language' => __('Language'),
            'translation' => __('Translation'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(SystemMessage::className(), ['id' => 'id']);
    }
}
