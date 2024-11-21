<?php

namespace frontend\models\academic;

use common\models\academic\EDecree;
use common\models\academic\EDecreeStudent;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;

class StudentDecree extends EDecree
{
    public function searchForStudent(Student $student, $params)
    {
        $this->load($params);

        $query = self::find()
            ->rightJoin('e_decree_student', 'e_decree_student._decree=e_decree.id')
            ->with(['decreeType']);

        if ($this->search) {
            $query->orWhereLike('e_decree.number', $this->search);
            $query->orWhereLike('e_decree.name', $this->search);
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

        $query->andFilterWhere(['e_decree_student._student' => $student->id]);

        if ($this->_decree_type) {
            $query->andFilterWhere(['e_decree._decree_type' => $this->_decree_type]);
        }

        return $dataProvider;
    }
}