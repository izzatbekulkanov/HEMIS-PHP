<?php

namespace backend\controllers;

use backend\models\FilterForm;
use backend\models\FormUploadTrans;
use common\components\AccessResources;
use common\components\Config;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectExamType;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\GradeType;
use common\models\curriculum\MarkingSystem;

use common\models\curriculum\RatingGrade;
use common\models\curriculum\Semester;
use common\models\performance\EPerformance;
use common\models\performance\EPerformanceReport;
use common\models\performance\EStudentPtt;
use common\models\performance\EStudentPttMeta;
use common\models\student\EGroup;
use common\models\performance\EStudentGpa;
use common\models\performance\EStudentGpaMeta;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\AdminRole;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\FinalExamType;
use common\models\system\classifier\SemestrType;
use common\models\system\classifier\StudentStatus;
use common\models\system\classifier\SubjectType;
use common\models\system\classifier\TrainingType;
use frontend\models\archive\AcademicRecord;
use kartik\mpdf\Pdf;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\base\Exception;
use yii\helpers\BaseFileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;


class PerformanceController extends BackendController
{
    public $activeMenu = 'performance';

    public function actionSummary()
    {
        $searchModel = new FilterForm();
        $balls = array();
        $final_exam = array();
        $faculty = "";
        if ($this->_user()->role->isDeanOrTutorRole()) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        $searchModel->download = 1;
        if ($searchModel->load(Yii::$app->request->post())) {

            if ($this->post('download')) {
                $searchModel->download = 1;
            }
            if ($this->post('btn')) {
                $searchModel->download = 0;
            }

            //$students = EStudentMeta::getContingentByYearSemesterGroup($searchModel->_education_year, $searchModel->_semester, $searchModel->_group);

            $students = EStudentSubject::getStudentIdsByYearSemesterGroup($searchModel->_curriculum, $searchModel->_education_year, $searchModel->_semester, $searchModel->_group);
            $list_student = array();
            foreach ($students as $student) {
                $list_student[$student->_student] = $student->_student;
            }

            $subjects = EStudentSubject::getSubjectIdsByYearSemesterGroup($searchModel->_curriculum, $searchModel->_education_year, $searchModel->_semester, $searchModel->_group);
            $list_subjects = array();
            foreach ($subjects as $subject) {
                $list_subjects[$subject->_subject] = $subject->subject->name;
            }

            $curriculum_subjects = ECurriculumSubject::getSubjectByCurriculumSemester($searchModel->_curriculum, $searchModel->_semester);
            $performances = EPerformance::find()
                ->joinWith(['studentMeta'])
                ->select('e_performance._student, _subject, grade, _final_exam_type')
                ->where([
                    'e_performance._education_year' => $searchModel->_education_year,
                    'e_performance._semester' => $searchModel->_semester,
                    'e_student_meta._group' => $searchModel->_group,
                    'e_performance._exam_type' => ExamType::EXAM_TYPE_OVERALL,
                    'e_performance.passed_status' => 1,
                    'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,

                ])
                //->andWhere(['in', '_student', $list_student])
                //->groupBy(['e_performance._student', '_subject'])
                ->all();

            //foreach($curriculum_subjects as $subject)
            //{
            foreach ($performances as $performance) {
                //if($subject->_subject == $performance->_subject){
                $balls[$performance->_subject][$performance->_student] = ceil($performance->grade);
                $final_exam[$performance->_subject][$performance->_student] = $performance->_final_exam_type;
                // }
            }
            if ($searchModel->download == 1) {
                $fileName = EPerformance::generateDownloadFile($students, $list_subjects, $searchModel, $balls, $final_exam);
                return Yii::$app->response->sendFile($fileName, basename($fileName));
            }
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'balls' => $balls,
            'final_exam' => $final_exam,
            'students' => @$students,
            'curriculum_subjects' => @$curriculum_subjects,
            'faculty' => @$faculty,
            'list_subjects' => @$list_subjects,
        ]);
    }

    public function actionDebtors()
    {
        $searchModel = new FilterForm();
        $balls = array();
        $final_exam = array();
        $faculty = "";
        if ($this->_user()->role->isDeanOrTutorRole()) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $searchModel->_faculty = $faculty;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear()->code;
        }
        $dataProvider = null;
        $debtor_list = [];
        if ($searchModel->load(Yii::$app->request->get())) {

            $students = $searchModel->_group ? EStudentMeta::getStudiedContingentByYearSemester($searchModel->_education_year, $searchModel->_semester, $searchModel->_group) : [];
            $list_student = array();
            foreach ($students as $student) {
                foreach (
                    EStudentMeta::getStudentSubjects($student)
                        ->andWhere(
                            ['<>', 'e_curriculum_subject._rating_grade', RatingGrade::RATING_GRADE_GRADUATE])
                        ->andWhere(
                                ['e_curriculum_subject._semester' => $student->_semestr]
                            )
                        ->orderBy('e_curriculum_subject.position')->all() as $k => $curriculumSubject
                ) {
                    $record = $curriculumSubject->getStudentSubjectRecord($student->_student);
                    if (!$record) {
                        $teacher = null;
                        $teacher = $curriculumSubject->_rating_grade == RatingGrade::RATING_GRADE_SUBJECT ?
                            (ESubjectExamSchedule::getExamByCurriculumSubjectType($curriculumSubject->_subject, $student->_curriculum, $student->_semestr,  $student->_group, ExamType::EXAM_TYPE_FINAL) ?
                                ESubjectExamSchedule::getExamByCurriculumSubjectType($curriculumSubject->_subject, $student->_curriculum, $student->_semestr,  $student->_group, ExamType::EXAM_TYPE_FINAL)->employee->fullName : '') :
                            (ESubjectExamSchedule::getExamByCurriculumSubjectType($curriculumSubject->_subject, $student->_curriculum, $student->_semestr,  $student->_group, ExamType::EXAM_TYPE_OVERALL) ?
                                ESubjectExamSchedule::getExamByCurriculumSubjectType($curriculumSubject->_subject, $student->_curriculum, $student->_semestr,  $student->_group, ExamType::EXAM_TYPE_OVERALL)->employee->fullName : '');
                        $debtor_list [] = [
                            'name' => @$student->student->fullName,
                            'group' => @$student->group->name,
                            'subject' => $curriculumSubject->subject->name,
                            'education_year' => @$student->educationYear->name,
                            'semester' => Semester::getByCurriculumSemester($student->_curriculum, $student->_semestr)->name,
                            'teacher' => $teacher,
                            //'teacher' => ESubjectSchedule::getTeacherByCurriculumSemesterSubjectTrainingGroup($student->_curriculum, $student->_semestr, $curriculumSubject->_subject, TrainingType::TRAINING_TYPE_LECTURE, $student->_group) ? ESubjectSchedule::getTeacherByCurriculumSemesterSubjectTrainingGroup($student->_curriculum, $student->_semestr, $curriculumSubject->_subject, TrainingType::TRAINING_TYPE_LECTURE, $student->_group)->employee->fullName : '',
                        ];
                    }
                }
            }

            if ($this->get('download')) {
                $fileName = EPerformance::generateAcademicRecordFile($debtor_list);
                return Yii::$app->response->sendFile($fileName, basename($fileName));
            }

        }
        $dataProvider = new ArrayDataProvider([
            'allModels' => $debtor_list,
            'sort' => [
                //'defaultOrder' => ['name' => SORT_ASC],

                'attributes' => [
                    // 'mark',
                    'group',
                    'name' => [
                        //   'header' => __('Structure Faculty'),
                    ],
                    'subject',
                    'education_year',
                    'semester',

                    /* 'mark' => [
                         'header' => __('Mark'),
                         'asc' => ['mark' => SORT_ASC],
                         'desc' => ['mark' => SORT_DESC],
                         'default' => SORT_ASC,
                     ],*/
                ],
                'defaultOrder' => [
                    'name' => SORT_DESC
                ]
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
        return $this->renderView([
            'searchModel' => $searchModel,
            'debtor_list' => @$debtor_list,
            'faculty' => @$faculty,
            'dataProvider' => @$dataProvider,
        ]);
    }

    public function actionPerformance()
    {
        $searchModel = new ESubjectExamSchedule();
        $dataProvider = $searchModel->search_performance($this->getFilterParams());
        $dataProvider->query->select('e_subject_exam_schedule._education_year,_subject,_group,_semester,_exam_type,final_exam_type,_curriculum');
        //$dataProvider->query->andFilterWhere(['in', '_exam_type', [ExamType::EXAM_TYPE_FINAL, ExamType::EXAM_TYPE_OVERALL]]);

        $faculty = null;
        if ($this->_user()->role->isDeanOrTutorRole()) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $searchModel->_department = $faculty;
                $ids = ECurriculum::find()
                    ->select(['id'])
                    ->where(['active' => ECurriculum::STATUS_ENABLE, '_department' => $faculty])
                    ->column();
                $dataProvider->query->andFilterWhere(['e_subject_exam_schedule._curriculum' => $ids]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        $current_year = EducationYear::getCurrentYear();
        if($current_year != null) {
            $searchModel->_education_year = $current_year->code;
            $dataProvider->query->andFilterWhere(['e_subject_exam_schedule._education_year' => $current_year->code]);
        }
        $dataProvider->sort->defaultOrder = ['_group' => SORT_ASC];
        $dataProvider->query->groupBy(['e_subject_exam_schedule._education_year', '_subject', '_group', '_semester', '_exam_type', 'final_exam_type', '_curriculum']);
        if (empty($searchModel->_education_year) || empty($searchModel->_semester) || empty($searchModel->_group)) {
            $dataProvider->query->andWhere('1 <> 1');
        }
        //$all_data = ESubjectExamSchedule::getGeneralExam();

        return $this->renderView([
            'dataProvider' => @$dataProvider,
            'searchModel' => @$searchModel,
            'faculty' => @$faculty,
            'user' => $this->_user(),
            //'all_data' => @$all_data,
        ]);

    }

    public function actionRatingInfo($education_year = "", $semester = "", $group = "", $subject = "", $exam_type = "", $final_exam_type = "")
    {
        $group_model = EGroup::findOne($group);
        if ($group_model === null) {
            $this->notFoundException();
        }

        $subject = ECurriculumSubject::getByCurriculumSemesterSubject($group_model->_curriculum, $semester, $subject);
        if ($subject === null) {
            $this->notFoundException();
        }
        $students = EStudentSubject::getStudentsByYearSemesterGroup($group_model->_curriculum, $education_year, $subject->_semester, $subject->_subject, $group_model->id);
        $st = array();
        foreach ($students as $value) {
            $st[$value->_student] = $value->_student;
        }

        if($exam_type != ExamType::EXAM_TYPE_FINAL &&  $exam_type != ExamType::EXAM_TYPE_OVERALL){
            $model = ESubjectExamSchedule::getFinalExamByCurriculumSubjectType($subject->_subject, $group_model->_curriculum, $subject->_semester, $group_model->id, $exam_type, $final_exam_type);
        }
        else{
            if ($subject->_rating_grade === RatingGrade::RATING_GRADE_SUBJECT)
                $model = ESubjectExamSchedule::getFinalExamByCurriculumSubjectType($subject->_subject, $group_model->_curriculum, $subject->_semester, $group_model->id, ExamType::EXAM_TYPE_FINAL, $final_exam_type);
            else
                $model = ESubjectExamSchedule::getFinalExamByCurriculumSubjectType($subject->_subject, $group_model->_curriculum, $subject->_semester, $group_model->id, ExamType::EXAM_TYPE_OVERALL, $final_exam_type);

        }
        $examTypes = ECurriculumSubjectExamType::getAllExamTypeByCurriculumSemesterSubject($group_model->_curriculum, $subject->_semester, $subject->_subject);
        $exams = array();

        foreach ($examTypes as $value) {
            $exams[$value->_exam_type] = $value->_exam_type;
        }

        $ratings = EPerformance::getMarksByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $exams, $group_model->id);

        $current_active = true;
        $midterm_active = true;
        $current_divider = 0;
        $midterm_divider = 0;
        foreach ($examTypes as $value) {
            if ($value->examType->_parent == ExamType::EXAM_TYPE_CURRENT || $value->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                $current_active = false;
            }
            if ($value->examType->_parent == ExamType::EXAM_TYPE_MIDTERM || $value->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                $midterm_active = false;
            }
            if ($value->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST) {
                $current_divider++;
            }
            if ($value->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                $current_divider++;
            }

            if ($value->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST) {
                $midterm_divider++;
            }
            if ($value->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                $midterm_divider++;
            }
        }
        $current_divider = $current_divider == 0 ? 1 : $current_divider;
        $midterm_divider = $midterm_divider == 0 ? 1 : $midterm_divider;
        $control_divider = 1;
        if (!$current_active && !$midterm_active) {
            $control_divider = 2;
        }
        $ratings_passed = "";
        if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
            $final_exam_types = array(FinalExamType::FINAL_EXAM_TYPE_FIRST);
            $ratings_passed = EPerformance::getPassedStudentsByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $final_exam_types);
        } elseif (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
            $final_exam_types = array(FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND);
            $ratings_passed = EPerformance::getPassedStudentsByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $final_exam_types);
        }

        $ratings_passed_list = array();
        if (is_array($ratings_passed)) {
            foreach ($ratings_passed as $item) {
                $ratings_passed_list[$item->_student] = $item->_student;
            }
        }

        $rated_students = array();
        foreach ($ratings as $value) {
            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                $rated_students[$value->_student]['id'] = $value->_student;
                $rated_students[$value->_student]['name'] = $value->student->fullName;
                $rated_students[$value->_student]['student_id_number'] = $value->student->student_id_number;
            } elseif (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND || @$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                if (!in_array($value->_student, $ratings_passed_list)) {
                    $rated_students[$value->_student]['id'] = $value->_student;
                    $rated_students[$value->_student]['name'] = $value->student->fullName;
                    $rated_students[$value->_student]['student_id_number'] = $value->student->student_id_number;
                }
            }
        }


        $ball = array();
        foreach ($ratings as $rating) {
            foreach ($rated_students as $student) {
                if ($student['id'] == $rating->_student) {
                    if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                            if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] += $rating->grade / $current_divider;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            }
                        }
                        else{
                            if ($rating->examType->_parent == ExamType::EXAM_TYPE_CURRENT) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] += $rating->grade;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            }
                        }
                        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                        }
                        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                            if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] += $rating->grade / $midterm_divider;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            }
                        }
                        else{
                            if ($rating->examType->_parent == ExamType::EXAM_TYPE_MIDTERM) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] += $rating->grade;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            }
                        }

                        if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                        }

                        //if ($rating->_exam_type == ExamType::EXAM_TYPE_LIMIT) {
                            //@$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            //if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            //    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST];
                            //}
                            if (!$current_active && !$midterm_active) {
                                if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE)
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST]) / $control_divider;
                                else
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST]);
                            } elseif (!$current_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST]);
                            } elseif (!$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] = (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST]);
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST];
                            }
                        //}

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];
                            }
                        }
                        if ($rating->_exam_type == ExamType::EXAM_TYPE_OVERALL) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_OVERALL] = $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST];
                            }
                        }
                    }
                    if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                            if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] += $rating->grade / $current_divider;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            }
                        }
                        else{
                            if ($rating->examType->_parent == ExamType::EXAM_TYPE_CURRENT) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] += $rating->grade;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            }
                        }

                        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                        }

                        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                            if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] += $rating->grade / $midterm_divider;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            }
                        }
                        else{
                            if ($rating->examType->_parent == ExamType::EXAM_TYPE_MIDTERM) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] += $rating->grade;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            }
                        }

                        if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];
                        }

                        //if ($rating->_exam_type == ExamType::EXAM_TYPE_LIMIT) {
                            //@$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            //if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            //    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                            //}
                            if (!$current_active && !$midterm_active) {
                                if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE)
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND]) / $control_divider;
                                else
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND]);
                            } elseif (!$current_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND]);
                            } elseif (!$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND]);
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                            }

                       // }

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND];
                            }
                        }
                        if ($rating->_exam_type == ExamType::EXAM_TYPE_OVERALL) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_OVERALL] = $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND];
                            }
                        }
                    }

                    if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                            if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] += $rating->grade / $current_divider;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            }
                        }
                        else{
                            if ($rating->examType->_parent == ExamType::EXAM_TYPE_CURRENT) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] += $rating->grade;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            }
                        }

                        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD];
                        }
                        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                            if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] += $rating->grade / $midterm_divider;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            }
                        }
                        else{
                            if ($rating->examType->_parent == ExamType::EXAM_TYPE_MIDTERM) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] += $rating->grade;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            }
                        }

                        if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD];
                        }

                        //if ($rating->_exam_type == ExamType::EXAM_TYPE_LIMIT) {
                            //@$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            //if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            //    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD];
                           // }
                            if (!$current_active && !$midterm_active) {
                                if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE)
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD]) / $control_divider;
                                else
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD]);
                            } elseif (!$current_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD]);
                            } elseif (!$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD]);
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD];
                            }
                        //}

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD];
                            }
                        }
                        if ($rating->_exam_type == ExamType::EXAM_TYPE_OVERALL) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_OVERALL] = $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_THIRD];
                            }
                        }
                    }
                }
            }
        }

        return $this->renderView([
            'model' => $model,
            'subject' => $subject,
            'students' => $students,
            'examTypes' => $examTypes,
            'ball' => $ball,
            'exams' => $exams,
            'education_year' => $education_year,
            'group_model' => $group_model,
            'rated_students' => $rated_students,
        ]);


    }

    protected function findCurriculumModel($id)
    {
        if (($model = ECurriculum::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }


    public function actionGpa($recalculate = false, $subjects = false)
    {
        $searchModel = new EStudentGpa();
        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }

        /**
         * @var $gpa EStudentGpa
         */
        if ($recalculate) {
            if ($gpa = EStudentGpa::findOne($recalculate)) {
                if ($gpa->reCalculateGpa(true)) {
                    $this->addSuccess(__('Student GPA of `{name}` recalculated successfully', ['name' => $gpa->student->getFullName()]));
                }
            }

            return $this->redirect(['gpa']);
        }

        if ($subjects) {
            if ($gpa = EStudentGpa::findOne($subjects)) {
                return $this->renderPartial('gpa-subjects', [
                    'model' => $gpa,
                ]);
            }
        }

        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchContingent($this->getFilterParams(), $department),
        ]);
    }

    public function actionGpaAdd()
    {
        $searchModel = new EStudentGpaMeta();
        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }

        if ($items = $this->post('selection')) {
            $filters = $this->getFilterParams();

            if ($count = EStudentGpa::calculateGpa($items)) {
                $this->addSuccess(__('GAP calculated for {count} students successfully', ['count' => $count]));
            }

            return $this->redirect(['gpa-add']);
        }

        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForGpa($this->getFilterParams(), $department),
        ]);
    }


    public function actionPtt($subjects = false)
    {
        $searchModel = new EStudentPtt();
        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }

        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchContingent($this->getFilterParams(), $department),
        ]);
    }

    public function actionPttEdit($student = false, $semester = false, $ptt = false, $download = false, $delete = false)
    {
        /**
         * @var $meta EStudentMeta
         */
        $model = null;
        $meta = null;

        if ($ptt) {
            $model = EStudentPtt::findOne(['id' => $ptt]);
            if ($delete) {
                try {
                    if ($model->delete()) {
                        $this->addSuccess(__('{number} raqamli shaxsiy grafik o\'chirildi', ['number' => $model->number]));
                        return $this->redirect(['ptt']);
                    }
                } catch (\Exception $exception) {
                    $this->addError($exception->getMessage());
                    return $this->redirect(['ptt-edit', 'ptt' => $model->id]);
                }
                return $this->redirect(['ptt']);
            }

            if ($download) {
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'tempDir' => Yii::getAlias('@runtime/mpdf'),
                ]);
                $mpdf->defaultCssFile = Yii::getAlias('@backend/assets/app/css/pdf-print.css');
                $mpdf->shrink_tables_to_fit = 1;
                $mpdf->keep_table_proportions = true;

                $mpdf->SetDisplayMode('fullwidth');
                $mpdf->WriteHTML($this->renderPartial('ptt-pdf', ['model' => $model]));

                return $mpdf->Output('shaxsiy-grafik-' . $model->student->student_id_number . '.pdf', Destination::DOWNLOAD);
            }
        }

        if ($student) {
            if ($meta = EStudentPttMeta::findOne([
                'id' => $student,
                'active' => true,
                '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED
            ])) {

                $model = new EStudentPtt([
                    '_student' => $meta->_student,
                    '_curriculum' => $meta->_curriculum,
                    '_department' => $meta->_department,
                ]);
            }
        }

        if ($model) {

            if ($semester)
                $model->_semester = $semester;

            if ($model->load($this->post())) {
                if ($meta) {
                    $model->setAttributes([
                        '_student' => $meta->_student,
                        '_curriculum' => $meta->_curriculum,
                        '_education_type' => $meta->_education_type,
                        '_education_form' => $meta->_education_form,
                        '_specialty' => $meta->_specialty_id,
                        '_department' => $meta->_department,
                        '_group' => $meta->_group,
                        '_education_year' => $meta->_education_year,
                    ]);
                }

                try {
                    if ($model->save()) {
                        $this->addSuccess(__(
                            $ptt ?
                                'Talaba {name} uchun {semester} uchun shaxsiy jadval yangilandi' :
                                'Talaba {name} uchun {semester} uchun shaxsiy jadval yaratildi',
                            [
                                'name' => $model->student->getFullName(),
                                'semester' => $model->semester->name,
                            ]));
                        return $this->redirect(['ptt-edit', 'ptt' => $model->id]);
                    } else {
                        $this->addError($model->getOneError());
                    }
                } catch (\Exception $exception) {
                    $this->addError($exception->getMessage());
                    return $this->refresh();
                }

            }

            return $this->render('ptt-edit-student', [
                'model' => $model,
                'meta' => $meta,
            ]);
        }

        $searchModel = new EStudentPttMeta();
        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }

        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForPtt($this->getFilterParams(), $department),
        ]);
    }

    public function actionRatingInfoSimple($education_year = "", $semester = "", $group = "", $subject = "", $exam_type = "", $final_exam_type = "")
    {
        $group_model = EGroup::findOne($group);
        if ($group_model === null) {
            $this->notFoundException();
        }

        $subject = ECurriculumSubject::getByCurriculumSemesterSubject($group_model->_curriculum, $semester, $subject);
        if ($subject === null) {
            $this->notFoundException();
        }
        $students = EStudentSubject::getStudentsByYearSemesterGroup($group_model->_curriculum, $education_year, $subject->_semester, $subject->_subject, $group_model->id);
        $st = array();
        foreach ($students as $value) {
            $st[$value->_student] = $value->_student;
        }

        if($exam_type != ExamType::EXAM_TYPE_FINAL &&  $exam_type != ExamType::EXAM_TYPE_OVERALL){
            $model = ESubjectExamSchedule::getFinalExamByCurriculumSubjectType($subject->_subject, $group_model->_curriculum, $subject->_semester, $group_model->id, $exam_type, $final_exam_type);
        }
        else{

        }

        $ratings = EPerformance::getMarksByCurriculumSemesterFinalExam($model->_education_year, $model->_semester, $model->_subject, $st, $exam_type, $model->final_exam_type, $group_model->id);

        $ratings_passed = "";
        if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
            $final_exam_types = array(FinalExamType::FINAL_EXAM_TYPE_FIRST);
            $ratings_passed = EPerformance::getPassedStudentsByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $final_exam_types);
        } elseif (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
            $final_exam_types = array(FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND);
            $ratings_passed = EPerformance::getPassedStudentsByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $final_exam_types);
        }

        $ratings_passed_list = array();
        if (is_array($ratings_passed)) {
            foreach ($ratings_passed as $item) {
                $ratings_passed_list[$item->_student] = $item->_student;
            }
        }

        $rated_students = array();
        foreach ($ratings as $value) {
            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                $rated_students[$value->_student]['id'] = $value->_student;
                $rated_students[$value->_student]['name'] = $value->student->fullName;
                $rated_students[$value->_student]['student_id_number'] = $value->student->student_id_number;
            } elseif (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND || @$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                if (!in_array($value->_student, $ratings_passed_list)) {
                    $rated_students[$value->_student]['id'] = $value->_student;
                    $rated_students[$value->_student]['name'] = $value->student->fullName;
                    $rated_students[$value->_student]['student_id_number'] = $value->student->student_id_number;
                }
            }
        }


        $ball = array();
        foreach ($ratings as $rating) {
            foreach ($rated_students as $student) {
                if ($student['id'] == $rating->_student) {
                    @$ball[$student['id']] = $rating->grade;
             }
            }
        }

        return $this->renderView([
            'model' => $model,
            'subject' => $subject,
            'students' => $students,

            'ball' => $ball,
            'education_year' => $education_year,
            'group_model' => $group_model,
            'rated_students' => $rated_students,
        ]);


    }

}
