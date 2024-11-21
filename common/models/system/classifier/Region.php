<?php

namespace common\models\system\classifier;

use Yii;

/**
 * This is the model class for table "h_region".
 *
 * @property string $code
 * @property string|null $parent_code
 * @property string $name
 * @property int|null $type
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EStudent[] $eStudents
 * @property EStudent[] $eStudents0
 */
class Region extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'h_region';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'updated_at', 'created_at'], 'required'],
            [['type', 'position'], 'default', 'value' => null],
            [['type', 'position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['code', 'parent_code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 256],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Code',
            'parent_code' => 'Parent Code',
            'name' => 'Name',
            'type' => 'Type',
            'position' => 'Position',
            'active' => 'Active',
            '_translations' => 'Translations',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[EStudents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEStudents()
    {
        return $this->hasMany(EStudent::className(), ['_province' => 'code']);
    }

    /**
     * Gets query for [[EStudents0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEStudents0()
    {
        return $this->hasMany(EStudent::className(), ['_district' => 'code']);
    }
}
