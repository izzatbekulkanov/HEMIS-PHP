<?php

namespace frontend\models\finance;

use common\models\finance\EStudentContract;
use common\models\finance\EStudentContractType;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;

class StudentContractType extends EStudentContractType
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



        return $dataProvider;
    }
}