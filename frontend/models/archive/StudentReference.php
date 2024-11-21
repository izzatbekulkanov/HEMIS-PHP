<?php

namespace frontend\models\archive;

use common\models\archive\EStudentReference;
use common\models\finance\EStudentContract;
use common\models\finance\EStudentContractType;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;

class StudentReference extends EStudentReference
{
    public function searchForStudent(Student $student, $params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $query->andFilterWhere(['_student' => $student->id]);

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }

        return $dataProvider;
    }

    public static function getCountReference($student)
    {
        $result="";
        $exist = self::find()
            ->where(['_student'=>$student])
            ->count();
        $exist = $exist+1;
        return $exist;
    }

}