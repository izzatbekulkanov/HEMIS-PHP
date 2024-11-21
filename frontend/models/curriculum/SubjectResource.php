<?php


namespace frontend\models\curriculum;


use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubjectResource;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\Semester;
use common\models\system\classifier\TrainingType;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;

class SubjectResource extends ESubjectResource
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['_subject', '_training_type'], 'safe']
        ]);
    }

    public static function getCountForStudent(Student $student, $semester, $subject)
    {
        return (new self())->searchForStudent([], $student, $semester, $subject, true);
    }

    public function searchForStudent($params, Student $student, $semester, $subject, $onlyCount = false)
    {
        $this->load($params);

        if ($this->_subject == null && $subject) {
            $this->_subject = $subject->hasAttribute('_subject') ? $subject->_subject : $subject->id;
        }


        $teachers = ESubjectSchedule::find()
            ->select(['_employee'])
            ->where([
                'active' => self::STATUS_ENABLE,
                '_group' => $student->meta->_group,
                '_curriculum' => $student->meta->_curriculum,
                '_subject' => $this->_subject,
            ])
            ->distinct('_employee')
            ->column();

        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->leftJoin('e_curriculum_subject_topic', 'e_curriculum_subject_topic.id=e_subject_resource._subject_topic')
            ->with(['semester', 'subject', 'employee']);

        if ($this->search) {
            $query->orWhereLike('e_subject.name', $this->search);
            $query->orWhereLike('e_subject_resource.name', $this->search);
            $query->orWhereLike('e_subject_resource.comment', $this->search);
            $query->orWhereLike('e_curriculum_subject_topic.name', $this->search);
        }

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }

        if ($this->_training_type) {
            $query->andFilterWhere(['e_subject_resource._training_type' => $this->_training_type]);
        }

        $query->andFilterWhere(['e_subject_resource.resource_type' => self::RESOURCE_TYPE_RESOURCE]);
        $query->andFilterWhere(['e_subject_resource.active' => self::STATUS_ENABLE]);
        $query->andFilterWhere(['e_subject_resource._curriculum' => $student->meta->_curriculum]);
        $query->andFilterWhere(['e_subject_resource._semester' => $semester->code]);
        $query->andFilterWhere(['e_subject_resource._subject' => $this->_subject]);
        $query->andFilterWhere(['e_subject_resource._language' => $student->meta->group->_education_lang]);
        $query->andFilterWhere(['_employee' => $teachers]);
        $query->orderBy(['e_subject_resource._training_type' => SORT_ASC, 'e_curriculum_subject_topic.position' => SORT_ASC]);

        return $onlyCount ? $query->count() : new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_subject',
                    '_subject_topic',
                    '_employee',
                    '_semester',
                    '_language',
                    '_education_year',
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
    }

    public function searchForStudentTopic(Student $student, $semester, $subject, $topic)
    {
        $teachers = ESubjectSchedule::find()
            ->select(['_employee'])
            ->where([
                'active' => self::STATUS_ENABLE,
                '_group' => $student->meta->_group,
                '_curriculum' => $student->meta->_curriculum,
                '_subject' => $subject->id,
            ])
            ->distinct('_employee')
            ->column();

        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->leftJoin('e_curriculum_subject_topic', 'e_curriculum_subject_topic.id=e_subject_resource._subject_topic')
            ->with(['semester', 'subject', 'employee']);

        if ($this->search) {
            $query->orWhereLike('e_subject.name', $this->search);
            $query->orWhereLike('e_subject_resource.name', $this->search);
            $query->orWhereLike('e_subject_resource.comment', $this->search);
            $query->orWhereLike('e_curriculum_subject_topic.name', $this->search);
        }

        if ($this->_education_year) {
            $query->andFilterWhere(['_education_year' => $this->_education_year]);
        }

        $query->andFilterWhere(['e_subject_resource.active' => self::STATUS_ENABLE]);
        $query->andFilterWhere(['e_subject_resource.resource_type' => self::RESOURCE_TYPE_RESOURCE]);
        $query->andFilterWhere(['e_subject_resource._subject_topic' => $topic->id]);
        $query->andFilterWhere(['e_subject_resource._curriculum' => $student->meta->_curriculum]);
        $query->andFilterWhere(['e_subject_resource._semester' => $semester->code]);
        $query->andFilterWhere(['e_subject_resource._subject' => $subject->id]);
        $query->andFilterWhere(['e_subject_resource._language' => $student->meta->group->_education_lang]);
        $query->andFilterWhere(['_employee' => $teachers]);
        $query->orderBy(['e_subject_resource._training_type' => SORT_ASC, 'e_curriculum_subject_topic.position' => SORT_ASC]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_subject',
                    '_subject_topic',
                    '_employee',
                    '_semester',
                    '_language',
                    '_education_year',
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
    }


    public function getFormattedDate()
    {
        return upperCaseFirst(\Yii::$app->formatter->asDate($this->exam_date->getTimestamp(), 'php:l, d-F, Y'));
    }


}