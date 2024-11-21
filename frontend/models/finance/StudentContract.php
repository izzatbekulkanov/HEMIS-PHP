<?php

namespace frontend\models\finance;

use common\models\finance\EStudentContract;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;

class StudentContract extends EStudentContract
{
    public function searchForStudent(Student $student, $params)
    {
        $this->load($params);

        $query = self::find();
            //->rightJoin('e_decree_student', 'e_decree_student._decree=e_decree.id')
            //->with(['decreeType']);

        /*if ($this->search) {
            $query->orWhereLike('e_decree.number', $this->search);
            $query->orWhereLike('e_decree.name', $this->search);
        }*/

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