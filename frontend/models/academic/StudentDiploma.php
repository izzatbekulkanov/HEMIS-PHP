<?php

namespace frontend\models\academic;

use common\models\academic\EDecree;
use common\models\academic\EDecreeStudent;
use common\models\archive\EStudentDiploma;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;

class StudentDiploma extends EStudentDiploma
{
    public function searchForStudent(Student $student, $params)
    {
        $this->load($params);

        $query = self::find()
            ->with(['student']);

        if ($this->search) {
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 200,
            ],
        ]);

        $query->andFilterWhere(['_student' => $student->id]);
        $query->andFilterWhere(['accepted' => true]);

        return $dataProvider;
    }
}