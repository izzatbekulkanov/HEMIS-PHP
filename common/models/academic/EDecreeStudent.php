<?php

namespace common\models\academic;

use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\Admin;
use DateTime;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "e_decree_student".
 *
 * @property int $id
 * @property int $_student
 * @property int $_decree
 * @property string[] $data
 * @property DateTime $created_at
 *
 * @property Admin $admin
 * @property EDecree $decree
 * @property EStudent $student
 * @property EStudentMeta $studentMeta
 */
class EDecreeStudent extends ActiveRecord
{
    public static function tableName()
    {
        return 'e_decree_student';
    }

    public function getDecree()
    {
        return $this->hasOne(EDecree::className(), ['id' => '_decree']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getStudentMeta()
    {
        return $this->hasOne(EStudentMeta::className(), ['id' => '_student_meta']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        return $dataProvider;
    }
}
