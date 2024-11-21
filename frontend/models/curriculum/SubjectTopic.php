<?php


namespace frontend\models\curriculum;


use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubjectResource;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\Semester;
use common\models\system\classifier\TrainingType;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;

class SubjectTopic extends ECurriculumSubjectTopic
{

    public function searchForStudent(Student $student, Semester $semester, ECurriculumSubject $subject)
    {
        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->with(['semester', 'subject']);

        if ($this->search) {
            $query->orWhereLike('e_subject.name', $this->search);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC, 'e_curriculum_subject_topic.id'=>SORT_ASC],
                'attributes' => [
                    'e_curriculum_subject_topic.id',
                    '_curriculum',
                    '_subject',
                    '_semester',
                    '_training_type',
                    'code',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        $query->andFilterWhere([
           // '_education_year' => EducationYear::getCurrentYear()->code,
            //'_group' => $student->getGroupIds(),
        ]);
        $query->andFilterWhere(['e_curriculum_subject_topic.active' => self::STATUS_ENABLE]);
        $query->andFilterWhere(['_curriculum' => $student->meta->_curriculum]);
        $query->andFilterWhere(['_semester' => $semester->code]);
        $query->andFilterWhere(['_subject' => $subject->_subject]);
        //$query->andFilterWhere(['_language' => $student->meta->group->_education_lang]);
        //$query->andFilterWhere(['_training_type' => TrainingType::TRAINING_TYPE_LECTURE]);

        return $dataProvider;
    }


    public function getFormattedDate()
    {
        return upperCaseFirst(\Yii::$app->formatter->asDate($this->exam_date->getTimestamp(), 'php:l, d-F, Y'));
    }


}