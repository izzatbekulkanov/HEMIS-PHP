<?php

namespace common\models\curriculum;

use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\classifier\Language;
use common\models\system\classifier\StudentStatus;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 *
 * @property int $id
 * @property int $_exam
 * @property int $_student
 * @property EExam $exam
 * @property EStudent $student
 */
class EExamStudentMetaResult extends EStudentMeta
{
    public $attempts;
    public $correct;
    public $percent;
    public $started_at;
    public $finished_at;
    public $excluded;

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'attempts' => __('Attempts'),
            'correct' => __('Correct'),
            'percent' => __('Percent'),
            'excluded' => __('Excluded'),
            'finished_at' => __('Finished At'),
        ]);
    }

    public function searchForStudent(EExam $exam, $group)
    {
        $query = self::find()
            ->select(['e_exam_student.*', 'e_student_meta._student', 'e_exam_exclude.excluded'])
            ->joinWith(['student'])
            ->leftJoin('e_exam_student', "e_exam_student._exam=:exam and e_exam_student._student=e_student_meta._student", ['exam' => $exam->id])
            ->leftJoin('e_exam_exclude', "e_exam_exclude._exam=:exam and e_exam_exclude._student=e_student_meta._student", ['exam' => $exam->id]);

        $query->andFilterWhere(['e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
        $query->andFilterWhere(['e_student_meta.active' => true]);
        $query->andFilterWhere(['e_student_meta._group' => $group]);
        $query->orderBy([
            'e_student.first_name' => SORT_ASC,
            'e_student.second_name' => SORT_ASC,
            'e_student.third_name' => SORT_ASC,
        ]);

        return new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 100,
                ],
            ]
        );
    }
}
