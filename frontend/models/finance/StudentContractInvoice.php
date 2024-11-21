<?php

namespace frontend\models\finance;

use common\models\finance\EStudentContractInvoice;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;

class StudentContractInvoice extends EStudentContractInvoice
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
        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }
        $query->andFilterWhere(['_student' => $student->id]);



        return $dataProvider;
    }
}