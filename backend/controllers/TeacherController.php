<?php

namespace backend\controllers;

use backend\models\FilterForm;
use common\components\Config;
use common\components\Translator;
use common\models\archive\ECertificateCommitteeResult;
use common\models\attendance\EAttendance;
use common\models\attendance\EAttendanceControl;
use common\models\attendance\EStudentGrade;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\ECurriculumSubjectExamType;
use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\curriculum\ECurriculumWeek;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EExam;
use common\models\curriculum\EExamStudent;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\EStudentTaskActivity;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\ESubjectResource;
use common\models\curriculum\ESubjectResourceQuestion;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\ESubjectTask;
use common\models\curriculum\ESubjectTaskStudent;
use common\models\curriculum\GradeType;
use common\models\curriculum\MarkingSystem;
use common\models\curriculum\RatingGrade;
use common\models\curriculum\Semester;
use common\models\employee\EEmployeeMeta;
use common\models\FormImportQuestion;
use common\models\performance\EPerformance;
use common\models\structure\EUniversity;
use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\AdminRole;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\FinalExamType;
use common\models\system\classifier\StudentStatus;
use common\models\system\classifier\TeacherPositionType;
use common\models\system\classifier\TrainingType;
use DateTimeImmutable;
use frontend\models\curriculum\SubjectResource;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use ZipArchive;

class TeacherController extends BackendController
{
    // public $activeMenu = '';
    public function actionTimeTable()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $this->activeMenu = 'teacher-attendance';
        $searchModel = new FilterForm();
        $tables = array();
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear() ? EducationYear::getCurrentYear()->code : date('Y');
            //$dataProvider->query->andFilterWhere(['e_subject_schedule._education_year' => $searchModel->_education_year]);
        }
        if ($searchModel->load(Yii::$app->request->get())) {
            $searchModel->_education_year = $searchModel->_education_year;
        }
        //if ($searchModel->load(Yii::$app->request->get())) {
        $query = ESubjectSchedule::find()
            ->andFilterWhere([
                //'_education_year' => EducationYear::getCurrentYear()->code,
                '_employee' => Yii::$app->user->identity->_employee,
                '_education_year' => $searchModel->_education_year,
            ])
            ->orderBy(['lesson_date' => SORT_DESC, '_lesson_pair' => SORT_ASC])
            ->all();
        //  $searchModel = new ESubjectSchedule();
        //  $dataProvider = $searchModel->search($this->getFilterParams());
        ///$dataProvider->query->select('COUNT(id) as count_lesson,_subject,_group,_semester,_training_type');
        // $dataProvider->query->andFilterWhere(['e_subject_schedule._education_year' => EducationYear::getCurrentYear()->code, '_employee' => Yii::$app->user->identity->_employee]);
        // $dataProvider->sort->defaultOrder = ['lesson_date' => SORT_DESC, '_lesson_pair' => SORT_ASC];
        ///$dataProvider->query->groupBy(['_subject', '_group','_semester', '_training_type']);

        Url::remember();
        $tables = array();
        foreach ($query as $table) {
            $event = new \yii2fullcalendar\models\Event();
            $event->id = $table->id;
            $event->title = @$table->lessonPair->period . ' | ' . $table->trainingType->name;
            if (@$table->additional != "")
                $event->nonstandard = '<b>' . @$table->subject->name . '</b>' . '<br>' . @$table->group->name . '<br>' . __('Room') . ': ' . @$table->auditorium->name . '<br>' . @$table->additional;
            else
                $event->nonstandard = '<b>' . @$table->subject->name . '</b>' . '<br>' . @$table->group->name . '<br>' . __('Room') . ': ' . @$table->auditorium->name;
            $event->start = Yii::$app->formatter->asDate($table->lesson_date, 'php:Y-m-d');
            //$event->backgroundColor = '#0d6aad';
            //         $event->url = Url::to(['teacher/check-lesson', 'education_year' => $table->_education_year, 'semester' => $table->_semester, 'group' => $table->_group, 'subject' => $table->_subject, 'training_type' => $table->_training_type]);
            $event->url = Url::to(['teacher/check-lesson', 'id' => $table->id]);
            //$event->borderColor = 'red';
            $tables[] = $event;
        }
        //}

        return $this->renderView([
            // 'searchModel' => $searchModel,
            //'dataProvider' => $dataProvider,
            'time_tables' => $tables,
            'searchModel' => $searchModel,
        ]);


    }

    public function actionTrainingList()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $this->activeMenu = 'teacher-attendance';
        $searchModel = new ESubjectSchedule();
        $dataProvider = $searchModel->search($this->getFilterParams());
        //$dataProvider->query->select('COUNT(id) as count_lesson,_subject,_group,_semester,_training_type');
        $dataProvider->query->andFilterWhere(['_employee' => Yii::$app->user->identity->_employee]);
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear()->code;
            $dataProvider->query->andFilterWhere(['e_subject_schedule._education_year' => EducationYear::getCurrentYear()->code]);
        }
        $dataProvider->sort->defaultOrder = ['lesson_date' => SORT_ASC, '_lesson_pair' => SORT_ASC];
        //$dataProvider->query->groupBy(['_subject', '_group','_semester', '_training_type']);

        Url::remember();
        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionRatingJournal($education_year = "", $semester = "", $group = "", $subject = "", $training_type = "")
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            $this->activeMenu = 'teacher-attendance';
        } else {
            $this->activeMenu = 'attendance';
        }
        if ($education_year != "" && $semester != "" && $group != "" && $subject != "" && $training_type != "") {
            if (
                $this->_user()->role->code == AdminRole::CODE_TEACHER
                || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT
            ) {
                $model = $this->findSubjectScheduleByAttributesModel(
                    $education_year,
                    $semester,
                    $group,
                    $subject,
                    $training_type,
                    Yii::$app->user->identity->_employee
                );
            } else {
                $model = $this->findSubjectScheduleByAttributesModel(
                    $education_year,
                    $semester,
                    $group,
                    $subject,
                    $training_type,
                    false
                );
            }

            $students = EStudentSubject::getStudentsByYearSemesterGroup(
                $model->_curriculum,
                $model->_education_year,
                $model->_semester,
                $model->_subject,
                $model->_group
            );

            $st = array();
            foreach ($students as $value) {
                $st[$value->_student] = $value->_student;
            }
            $lesson_date = Yii::$app->formatter->asDate($model->lesson_date, 'php:Y-m-d');

            $studentGrades = EStudentGrade::find()
                ->where([
                    '_education_year' => $model->_education_year,
                    '_semester' => $model->_semester,
                    '_subject' => $model->_subject,
                    '_training_type' => $model->_training_type,
                ])
                ->andWhere(['in', '_student', $st])
                ->all();

            $lesson_dates = ESubjectSchedule::find()
                ->where([
                    '_education_year' => $model->_education_year,
                    '_group' => $model->_group,
                    '_semester' => $model->_semester,
                    '_subject' => $model->_subject,
                    '_training_type' => $model->_training_type
                ])
                ->groupBy(['lesson_date', '_lesson_pair', '_training_type', '_subject_topic', 'id'])
                ->orderBy(['lesson_date' => SORT_ASC, '_lesson_pair' => SORT_ASC]);

            $countQuery = clone $lesson_dates;
            $pages = new Pagination(
                ['totalCount' => $countQuery->count(), 'defaultPageSize' => 5, /*'pageSizeLimit'=>2*/]
            );
            $models = $lesson_dates->offset($pages->offset)->limit($pages->limit)->all();

            $nbs = [];
            foreach ($students as $student) {
                foreach ($studentGrades as $studentGrade) {
                    if ($student->_student == $studentGrade->_student) {
                        $nbs[$student->_student][Yii::$app->formatter->asDate(
                            $studentGrade->subjectSchedule->lesson_date,
                            'php:Y-m-d'
                        )][$studentGrade->subjectSchedule->_lesson_pair] = $studentGrade->grade;
                    }
                }
            }
            Url::remember();
            return $this->renderView([
                'model' => $model,
                'students' => @$students,
                'lesson_dates' => @$lesson_dates,
                'nbs' => @$nbs,
                'pages' => $pages,
                'models' => $models,
            ]);
        } else {
            $searchModel = new ESubjectSchedule();
            $dataProvider = $searchModel->search($this->getFilterParams());
            $dataProvider->query->select('e_subject_schedule._education_year,_subject,_group,_semester,_training_type');
            if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
                $dataProvider->query->andFilterWhere(
                    [
                        //        'e_subject_schedule._education_year' => EducationYear::getCurrentYear()->code,
                        '_employee' => Yii::$app->user->identity->_employee
                    ]
                );
                if ($searchModel->_education_year == null) {
                    $searchModel->_education_year = EducationYear::getCurrentYear()->code;
                    $dataProvider->query->andFilterWhere(['e_subject_schedule._education_year' => EducationYear::getCurrentYear()->code]);
                }
            }
            $dataProvider->sort->defaultOrder = ['_group' => SORT_ASC, '_subject' => SORT_ASC];
            $dataProvider->query->groupBy(
                ['e_subject_schedule._education_year', '_subject', '_group', '_semester', '_training_type']
            );

            return $this->render('rating-journal-list', [
                'dataProvider' => @$dataProvider,
                'searchModel' => @$searchModel,
            ]);
        }
    }


    /**
     * @resource teacher/midterm-exam-table
     */
    public function actionMidtermExamTable($type = "")
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $this->activeMenu = 'teacher-examtable';
        $searchModel = new ESubjectExamSchedule();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_employee' => Yii::$app->user->identity->_employee]);
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear()->code;
            $dataProvider->query->andFilterWhere(['e_subject_exam_schedule._education_year' => EducationYear::getCurrentYear()->code]);
        }
        $dataProvider->query->andFilterWhere(['not in', '_exam_type', [ExamType::EXAM_TYPE_FINAL, ExamType::EXAM_TYPE_OVERALL]]);
        $dataProvider->sort->defaultOrder = ['exam_date' => SORT_DESC, '_lesson_pair' => SORT_ASC];
        // $all_data = ESubjectExamSchedule::getMidtermExamCount('data');
        Url::remember();
        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            //    'all_data' => $all_data,
        ]);
    }

    /**
     * @resource teacher/final-exam-table
     */
    public function actionFinalExamTable($type = "")
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $this->activeMenu = 'teacher-examtable';
        $searchModel = new ESubjectExamSchedule();
        $dataProvider = $searchModel->search_teacher($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['e_subject_exam_schedule._employee' => Yii::$app->user->identity->_employee]);
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear()->code;
            $dataProvider->query->andFilterWhere(['e_subject_exam_schedule._education_year' => EducationYear::getCurrentYear()->code]);
        }
        $dataProvider->query->andFilterWhere(['in', '_exam_type', [ExamType::EXAM_TYPE_FINAL, ExamType::EXAM_TYPE_OVERALL]]);
        $dataProvider->query->andFilterWhere(['in', '_rating_grade', [RatingGrade::RATING_GRADE_SUBJECT, RatingGrade::RATING_GRADE_SUBJECT_FINAL]]);
        $dataProvider->sort->defaultOrder = ['exam_date' => SORT_DESC, '_lesson_pair' => SORT_ASC];
        //$all_data = ESubjectExamSchedule::getFinalExamCount('data');
        Url::remember();
        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @resource teacher/other-exam-table
     */
    public function actionOtherExamTable($type = "")
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $this->activeMenu = 'teacher-examtable';
        $searchModel = new ESubjectExamSchedule();
        $dataProvider = $searchModel->search_teacher($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['e_subject_exam_schedule._employee' => Yii::$app->user->identity->_employee]);
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear()->code;
            $dataProvider->query->andFilterWhere(['e_subject_exam_schedule._education_year' => EducationYear::getCurrentYear()->code]);
        }
        $dataProvider->query->andFilterWhere(['in', '_exam_type', [ExamType::EXAM_TYPE_OVERALL]]);
        $dataProvider->query->andFilterWhere(['not in', '_rating_grade', [RatingGrade::RATING_GRADE_SUBJECT, RatingGrade::RATING_GRADE_SUBJECT_FINAL]]);

        $dataProvider->sort->defaultOrder = ['exam_date' => SORT_DESC, '_lesson_pair' => SORT_ASC];
        //$all_data = ESubjectExamSchedule::getOverallExamCount('data');
        Url::remember();
        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @resource teacher/check-lesson
     */
    public function actionCheckLesson($id = "")
    {
        $this->activeMenu = 'teacher-attendance';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }

        if ($id != "") {
            $model = $this->findSubjectScheduleModel($id, Yii::$app->user->identity->_employee);
            $model->scenario = ESubjectSchedule::SCENARIO_TEACHER;
            $prev_url = Url::previous();


            $students = EStudentSubject::getStudentsByYearSemesterGroup($model->_curriculum, $model->_education_year, $model->_semester, $model->_subject, $model->_group);
            $st = array();
            foreach ($students as $value) {
                $st[$value->_student] = $value->_student;
            }
            $lesson_date = Yii::$app->formatter->asDate($model->lesson_date, 'php:Y-m-d');
            $topics = ECurriculumSubjectTopic::getTopicByCurriculumSemesterSubjectTraining($model->_curriculum, $model->_semester, $model->_subject, $model->_training_type);

            $absents = EAttendance::find()
                ->where([
                    '_education_year' => $model->_education_year,
                    '_semester' => $model->_semester,
                    '_subject' => $model->_subject,
                    '_lesson_pair' => $model->_lesson_pair,
                    '_training_type' => $model->_training_type,
                    'lesson_date' => $lesson_date
                ])
                ->andWhere(['in', '_student', $st])
                ->all();

            $nbsz = array();
            foreach ($students as $student) {
                foreach ($absents as $absent) {
                    if ($student->_student == $absent->_student) {
                        $nbsz[$student->_student] = $absent->absent_off;
                    }
                }
            }
            $canCheck = false;
            if (Config::get(Config::CONFIG_COMMON_ATTENDANCE_CONTROL)) {
                $canCheck = Yii::$app->formatter->asDate($model->lesson_date, 'php:Y-m-d') === Yii::$app->formatter->asDate(time(), 'php:Y-m-d');
            } else
                $canCheck = true;
            //if (Yii::$app->request->post('btn') || Yii::$app->request->post('sz')) {
            if ($canCheck && isset($_POST['btn']) && Yii::$app->request->post('ESubjectSchedule')['_subject_topic']) {
                $model->_subject_topic = Yii::$app->request->post('ESubjectSchedule')['_subject_topic'];
                /*if($model->_training_type === TrainingType::TRAINING_TYPE_LECTURE){
                    $topic = ESubjectSchedule::getTopicByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, $model->_training_type, $model->_employee, $model->_group, $model->_subject_topic);
                    if ($topic !== null) {
                        $this->addError(__('This topic has already been identified '));
                        return $this->redirect(['check-lesson', 'id'=>$model->id]);
                    }
                }*/

                $insert_user = $model->_employee;
                $connection = Yii::$app->db;
                $attendance_control = EAttendanceControl::findOne(['_subject_schedule' => $model->id, '_employee' => $insert_user]);
                if ($attendance_control === null) {
                    $attendance_control = new EAttendanceControl();
                    $attendance_control->scenario = EAttendanceControl::SCENARIO_INSERT;
                    $attendance_control->attributes = $model->attributes;
                    $attendance_control->_subject_schedule = $model->id;
                    $attendance_control->lesson_date = $lesson_date;
                    if ($attendance_control->save()) {
                        $this->addSuccess(
                            __('Attendance control checked.')
                        );
                    } else {
                        $e2 = new Exception();
                        if ($e2->getCode() == 0) {
                            $this->addError(__('Attendance control have been already checked'));
                        } else {
                            $this->addError($e2->getMessage());
                        }
                        return $this->redirect($prev_url);
                    }

                }
                /* $attendance_control = new EAttendanceControl();
                 $attendance_control = $this->findAttendanceControl($model->_subject_schedule);
                 $attendance_control = $id ? $this->findDepartmentModel($id) : new EDepartment();

                 $attendance_control->scenario = EAttendanceControl::SCENARIO_CREATE;
                 $model = $this->findSemesterModel($id);
 */
                /*$sql_con = 'INSERT into ' . EAttendanceControl::tableName() . ' ("_subject_schedule", "_group", "_education_year", "_semester", "_subject",  "_training_type", "_lesson_pair", "lesson_date", "_employee", "updated_at", "created_at") VALUES ';
                $sql_con .= '(' . $model->id . ',' . $model->_group . ',' . $model->_education_year . ',' . $model->_semester . ',' . $model->_subject . ',' . $model->_training_type . ',' . $model->_lesson_pair . ',\'' . $lesson_date . '\',' . $insert_user . ',\'' . $time . '\',\'' . $time . '\'),';
                $sql_con .= ' ON CONFLICT ("_employee", "_group", "_education_year", "_semester", "_subject", "_training_type", "_lesson_pair", "lesson_date")  DO UPDATE SET lesson_date=EXCLUDED.lesson_date;';
                $command = $connection ->createCommand($sql_con);
                $command -> execute();

*/
                if (is_array(@$_POST['sz'])) {
                    $sql_sz = 'INSERT into ' . EAttendance::tableName() . ' ("_subject_schedule", "_student", "_education_year", "_semester", "_subject",  "_training_type", "_lesson_pair", "lesson_date", "_employee", "absent_on", "absent_off", "updated_at", "created_at") VALUES ';
                    $time = date('Y-m-d H:i:s', time());
                    foreach (Yii::$app->request->post('sz') as $key => $value) {
                        //if(!in_array($value, $absent_student)){
                        $sql_sz .= '(' . $model->id . ',' . @$value . ',' . $model->_education_year . ',' . $model->_semester . ',' . $model->_subject . ',' . $model->_training_type . ',' . $model->_lesson_pair . ',\'' . $lesson_date . '\',' . $insert_user . ',' . '0' . ',' . '2' . ',\'' . $time . '\',\'' . $time . '\'),';
                        //  }
                    }

                    $sql_sz = substr($sql_sz, 0, -1);
                    $sql_sz .= ' ON CONFLICT ("_student", "_semester", "_subject", "_training_type", "_lesson_pair", "lesson_date")  DO UPDATE SET absent_off=EXCLUDED.absent_off, absent_on=0;';
                    $command = $connection->createCommand($sql_sz);
                    $command->execute();

                }

                $model->save(false);
                $this->addSuccess(
                    __('Attendance `{subject}` for `{group}` edited successfully.', [
                        'subject' => $model->subject->name,
                        'group' => $model->group->name,
                    ]));
                return $this->redirect($prev_url);
            } elseif (Yii::$app->request->isAjax) {
                return $this->renderAjax('check-lesson', [
                    'model' => $model,
                    'students' => $students,
                    'nbsz' => $nbsz,
                    'prev_url' => $prev_url,
                    'topics' => $topics,
                    'canCheck' => $canCheck,
                ]);
            } else {
                return $this->renderView([
                    'model' => $model,
                    'students' => $students,
                    'nbsz' => $nbsz,
                    'prev_url' => $prev_url,
                    'topics' => $topics,
                    'canCheck' => $canCheck,
                ]);
            }
        }
    }

    /**
     * @resource teacher/check-grade
     */
    public function actionCheckGrade($id = "")
    {
        $this->activeMenu = 'teacher-attendance';
        if (
            $this->_user()->role->code !== AdminRole::CODE_TEACHER
            && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT
        ) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }

        if ($id != "") {
            /** @var ESubjectSchedule $model */
            $model = $this->findSubjectScheduleModel($id, Yii::$app->user->identity->_employee);
            $model->scenario = ESubjectSchedule::SCENARIO_TEACHER;
            $prev_url = Url::previous();

            $students = EStudentSubject::getStudentsByYearSemesterGroup(
                $model->_curriculum,
                $model->_education_year,
                $model->_semester,
                $model->_subject,
                $model->_group
            );
            $st = ArrayHelper::map($students, '_student', '_student');
            $lesson_date = Yii::$app->formatter->asDate($model->lesson_date, 'php:Y-m-d');
            $topics = ECurriculumSubjectTopic::getTopicByCurriculumSemesterSubjectTraining(
                $model->_curriculum,
                $model->_semester,
                $model->_subject,
                $model->_training_type
            );

            $studentGrades = EStudentGrade::find()
                ->where([
                    '_subject_schedule' => $model->id,
                    //'_education_year' => $model->_education_year,
                    //'_semester' => $model->_semester,
                    //'_subject_topic' => $model->_subject_topic,
                    //'_lesson_pair' => $model->_lesson_pair,
                    //'_training_type' => $model->_training_type,
                    //'lesson_date' => $lesson_date
                ])
                ->andWhere(['in', '_student', $st])
                ->all();

            $grades = ArrayHelper::map($studentGrades, '_student', 'grade');
            $date = new DateTimeImmutable('@' . $model->lesson_date->format('U'), $model->lesson_date->getTimezone());
            $today = new DateTimeImmutable('@' . time(), $model->lesson_date->getTimezone());
            $interval = $date->diff($today);
            $canGrade = $interval->y === 0 && $interval->m === 0 && $interval->days === 0;
            if ($studentGrades !== []) {
                $model->_subject_topic = $studentGrades[0]->_subject_topic ?: $model->_subject_topic;
            }
            if ($canGrade && isset($_POST['btn']) && $subject_topic = ArrayHelper::getValue($this->post(), 'ESubjectSchedule._subject_topic')) {
                if (is_array($this->post('grade'))) {
                    foreach ($this->post('grade') as $student => $grade) {
                        if (!empty($grade) && (int)$grade > 0) {
                            $sg = EStudentGrade::find()->where([
                                '_subject_schedule' => $model->id,
                                '_student' => $student,
                                //'_education_year' => $model->_education_year,
                                //'_semester' => $model->_semester,
                                //'_subject' => $model->_subject,
                                //'_subject_topic' => $subject_topic,
                                //'_lesson_pair' => $model->_lesson_pair,
                                //'_training_type' => $model->_training_type,
                                //'_employee' => $model->_employee,
                                //'lesson_date' => $lesson_date,
                            ])->one();
                            if ($sg === null) {
                                $sg = new EStudentGrade([
                                    '_subject_schedule' => $model->id,
                                    '_student' => $student,
                                    '_education_year' => $model->_education_year,
                                    '_semester' => $model->_semester,
                                    '_subject' => $model->_subject,
                                    '_subject_topic' => $subject_topic,
                                    '_lesson_pair' => $model->_lesson_pair,
                                    '_training_type' => $model->_training_type,
                                    '_employee' => $model->_employee,
                                    'lesson_date' => $lesson_date,
                                    'grade' => $grade
                                ]);
                            } else {
                                $sg->_subject_topic = $subject_topic;
                                $sg->grade = $grade;
                            }
                            $sg->save();
                            if ($model->getOldAttribute('_subject_topic') === null) {
                                $model->_subject_topic = $subject_topic;
                                $model->save(false);
                            }
                        }
                    }
                }
                $this->addSuccess(
                    __('Grade `{subject}` for `{group}` edited successfully.', [
                        'subject' => $model->subject->name,
                        'group' => $model->group->name,
                    ])
                );
                return $this->redirect($prev_url);
            } elseif (Yii::$app->request->isAjax) {
                return $this->renderAjax('check-grade', [
                    'model' => $model,
                    'students' => $students,
                    //'absentStudents' => $absentStudents,
                    'grades' => $grades,
                    'canGrade' => $canGrade,
                    'prev_url' => $prev_url,
                    'topics' => $topics,
                ]);
            } else {
                return $this->renderView([
                    'model' => $model,
                    'students' => $students,
                    //'absentStudents' => $absentStudents,
                    'grades' => $grades,
                    'canGrade' => $canGrade,
                    'prev_url' => $prev_url,
                    'topics' => $topics,
                ]);
            }
        }
        throw new NotFoundHttpException();
    }

    /**
     * @resource teacher/attendance-journal
     */
    public function actionAttendanceJournal($education_year = "", $semester = "", $group = "", $subject = "", $training_type = "")
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT)
            $this->activeMenu = 'teacher-attendance';
        else
            $this->activeMenu = 'attendance';
        if ($education_year != "" && $semester != "" && $group != "" && $subject != "" && $training_type != "") {

            if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT)
                $model = $this->findSubjectScheduleByAttributesModel($education_year, $semester, $group, $subject, $training_type, Yii::$app->user->identity->_employee);
            else
                $model = $this->findSubjectScheduleByAttributesModel($education_year, $semester, $group, $subject, $training_type, false);

            $students = EStudentSubject::getStudentsByYearSemesterGroup($model->_curriculum, $model->_education_year, $model->_semester, $model->_subject, $model->_group);

            $st = array();
            foreach ($students as $value) {
                $st[$value->_student] = $value->_student;
            }
            $lesson_date = Yii::$app->formatter->asDate($model->lesson_date, 'php:Y-m-d');

            $absents = EAttendance::find()
                ->where([
                    '_education_year' => $model->_education_year,
                    '_semester' => $model->_semester,
                    '_subject' => $model->_subject,
                    '_training_type' => $model->_training_type,
                ])
                ->andWhere(['in', '_student', $st])
                ->all();

            $absentsCount = EAttendance::find()
                ->select('lesson_date, _lesson_pair, _training_type, COUNT(id) as lessons')
                ->where(['_education_year' => $model->_education_year, '_subject' => $model->_subject, '_semester' => $model->_semester, '_training_type' => $model->_training_type])
                ->andWhere(['in', '_student', $st])
                ->groupBy(['lesson_date', '_lesson_pair', '_training_type'])
                ->all();

            $absentsControl = EAttendanceControl::find()
                //->select('lesson_date, _lesson_pair')
                ->where(['_education_year' => $model->_education_year, '_subject' => $model->_subject, '_semester' => $model->_semester, '_training_type' => $model->_training_type, '_group' => $model->_group, '_employee' => $model->_employee])
                //->groupBy(['lesson_date', '_lesson_pair'])
                ->all();

            $lesson_dates = ESubjectSchedule::find()
                //      ->select('lesson_date, _lesson_pair, _training_type, _subject_topic, id')
                ->where(['_education_year' => $model->_education_year, '_group' => $model->_group,
                    '_semester' => $model->_semester, '_subject' => $model->_subject,
                    '_training_type' => $model->_training_type])
                ->groupBy(['lesson_date', '_lesson_pair', '_training_type', '_subject_topic', 'id'])
                ->orderBy(['lesson_date' => SORT_ASC, '_lesson_pair' => SORT_ASC]);
            //  ->limit(5)
            //     ->all();

            $countQuery = clone $lesson_dates;
            $pages = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' => 4, /*'pageSizeLimit'=>2*/]);
            $models = $lesson_dates->offset($pages->offset)->limit($pages->limit)->all();

            $check = array();
            foreach ($absentsControl as $absent_control) {
                $check[Yii::$app->formatter->asDate($absent_control->lesson_date, 'php:Y-m-d')][$absent_control->_lesson_pair] = $absent_control->active;
            }
            $nbs = array();
            $nbsz = array();
            $nbs_summary = [];
            foreach ($students as $student) {
                foreach ($absents as $absent) {
                    if ($student->_student == $absent->_student) {
                        if ($absent->absent_on !== 0) {
                            @$nbs[$student->_student][Yii::$app->formatter->asDate($absent->lesson_date, 'php:Y-m-d')][$absent->_lesson_pair]['s'] = $absent->absent_on;
                            @$nbs[$student->_student][Yii::$app->formatter->asDate($absent->lesson_date, 'php:Y-m-d')][$absent->_lesson_pair]['id'] = $absent->id;
                            @$nbs_summary[$student->_student]['s'] += $absent->absent_on;
                        }
                        if (@$absent->absent_off !== 0) {
                            @$nbs[$student->_student][Yii::$app->formatter->asDate($absent->lesson_date, 'php:Y-m-d')][$absent->_lesson_pair]['sz'] = $absent->absent_off;
                            @$nbs[$student->_student][Yii::$app->formatter->asDate($absent->lesson_date, 'php:Y-m-d')][$absent->_lesson_pair]['id'] = $absent->id;
                            @$nbs_summary[$student->_student]['sz'] += $absent->absent_off;
                        }

                        /*if ($absent->absent_on !== 0)
                            $nbs[$student->_student][Yii::$app->formatter->asDate($absent->lesson_date, 'php:Y-m-d')][$absent->_lesson_pair] = $absent->absent_on;
                        if (@$absent->absent_off !== 0)
                            @$nbsz[$student->_student][Yii::$app->formatter->asDate($absent->lesson_date, 'php:Y-m-d')][$absent->_lesson_pair] = $absent->absent_off;*/
                    }
                }
            }
            Url::remember();
            return $this->renderView([
                'model' => $model,
                'students' => @$students,
                'absentsCount' => @$absentsCount,
                'lesson_dates' => @$lesson_dates,
                'nbs' => @$nbs,
                'nbsz' => @$nbsz,
                'check' => @$check,
                'pages' => $pages,
                'models' => $models,
                'nbs_summary' => @$nbs_summary,
            ]);
        } else {
            $searchModel = new ESubjectSchedule();
            $dataProvider = $searchModel->search($this->getFilterParams());
            $dataProvider->query->select('e_subject_schedule._education_year,_subject,_group,_semester,_training_type');
            if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
                $dataProvider->query->andFilterWhere(['_employee' => Yii::$app->user->identity->_employee]);
                if ($searchModel->_education_year == null) {
                    $searchModel->_education_year = EducationYear::getCurrentYear()->code;
                    $dataProvider->query->andFilterWhere(['e_subject_schedule._education_year' => EducationYear::getCurrentYear()->code]);
                }
            }
            $dataProvider->sort->defaultOrder = ['_group' => SORT_ASC, '_subject' => SORT_ASC];
            $dataProvider->query->groupBy(['e_subject_schedule._education_year', '_subject', '_group', '_semester', '_training_type']);

            return $this->render('journal-list', [
                'dataProvider' => @$dataProvider,
                'searchModel' => @$searchModel,
            ]);

        }
    }

    /**
     * @resource teacher/check-rating
     */
    public function actionCheckRating($id = "")
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }

        $this->activeMenu = 'teacher-examtable';
        $model = $this->findExamScheduleModel($id, Yii::$app->user->identity->_employee, "");
        //$model->scenario = ESubjectSchedule::SCENARIO_TEACHER;

        $prev_url = Url::current();
        $students = EStudentSubject::getStudentsByYearSemesterGroup($model->_curriculum, $model->_education_year, $model->_semester, $model->_subject, $model->_group);
        $st = array();
        foreach ($students as $value) {
            $st[$value->_student] = $value->_student;
        }
        $exam_types = array($model->_exam_type => $model->_exam_type);
        $max_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, $model->_exam_type)->max_ball;
        $ratings = EPerformance::getMarksByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $exam_types, $model->_group);

        $ratings_passed = array();
        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
            $final_exam_types = array(FinalExamType::FINAL_EXAM_TYPE_FIRST);
            $ratings_passed = EPerformance::getPassedStudentsByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $final_exam_types);
        } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
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
        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
            foreach ($students as $value) {
                $rated_students[$value->_student]['id'] = $value->_student;
                $rated_students[$value->_student]['name'] = $value->student->fullName;
            }
        }

        foreach ($ratings as $value) {
            /*if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST){
                $rated_students[$value->_student]['id'] = $value->_student;
                $rated_students[$value->_student]['name'] = $value->student->fullName;
            }*/
            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND || $model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                if (!in_array($value->_student, $ratings_passed_list)) {
                    $rated_students[$value->_student]['id'] = $value->_student;
                    $rated_students[$value->_student]['name'] = $value->student->fullName;
                    //   $rated_students[$value->_student]['student_id_number'] = $value->student->student_id_number;
                }
            }
        }
        $st_rating = array();
        foreach ($rated_students as $key => $item) {
            $st_rating[$key] = $key;
        }
        //print_r($st_rating);
        $exam_date = Yii::$app->formatter->asDate($model->exam_date, 'php:Y-m-d');
        $trainings = array();
        $tasks = "";
        $midterm = false;
        if ($model->_exam_type == ExamType::EXAM_TYPE_MIDTERM || $model->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $model->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
            $trainings = array(TrainingType::TRAINING_TYPE_LECTURE => TrainingType::TRAINING_TYPE_LECTURE);
            $midterm = true;
        } elseif ($model->_exam_type == ExamType::EXAM_TYPE_CURRENT || $model->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $model->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
            $trainings = array(TrainingType::TRAINING_TYPE_LABORATORY => TrainingType::TRAINING_TYPE_LABORATORY, TrainingType::TRAINING_TYPE_PRACTICE => TrainingType::TRAINING_TYPE_PRACTICE, TrainingType::TRAINING_TYPE_SEMINAR => TrainingType::TRAINING_TYPE_SEMINAR);
        }
        $lessons = ESubjectSchedule::getTeachersByCurriculumSemesterSubjectTraining($model->_curriculum, $model->_semester, $model->_subject, $trainings);
        $employees = array();
        foreach ($lessons as $item) {
            $employees [$item->_employee] = $item->_employee;
        }

        if ($this->get('get_tasks')) {
            $count_task = $this->get('count_task');
            return $this->renderAjax('check-rating_get_task', [
                'model' => $model,
                'count_task' => $count_task,
            ]);
        }


        $fillTest = false;

        //print_r($tasks->mark);
        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
            if ($midterm) {
                $tasks_ball = EStudentTaskActivity::find();
                $tasks_ball->select('SUM (mark)/COUNT(_subject_task) as mark, COUNT(_subject_task) as task_count, _student');
                $tasks_ball->leftJoin('e_subject_task', 'e_subject_task.id=_subject_task');
                $tasks_ball->where([
                    'e_student_task_activity._curriculum' => $model->_curriculum,
                    'e_student_task_activity._education_year' => $model->_education_year,
                    'e_student_task_activity._semester' => $model->_semester,
                    'e_student_task_activity._subject' => $model->_subject,
                    'e_subject_task._exam_type' => $model->_exam_type,
                    'e_subject_task.active' => ESubjectTask::STATUS_ENABLE,
                    'e_student_task_activity.active' => ESubjectTask::STATUS_ENABLE,
                    'e_subject_task._task_type' => ESubjectTask::TASK_TYPE_TASK,
                ]);
                //->andWhere(['<', 'e_subject_task.deadline', $exam_date])
                $tasks_ball->andWhere(['>=', 'mark', 0]);
                $tasks_ball->andWhere(['not', ['mark' => null]]);
                $tasks_ball->andWhere(['in', 'e_student_task_activity._training_type', $trainings]);
                $tasks_ball->andWhere(['in', 'e_student_task_activity._employee', $employees]);
                $tasks_ball->andWhere(['in', '_student', $st_rating]);
                if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                    $tasks_ball->andFilterWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST]]);
                } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                    $tasks_ball->andFilterWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND]]);
                } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                    $tasks_ball->andFilterWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND, FinalExamType::FINAL_EXAM_TYPE_THIRD]]);
                }
                $tasks_ball->groupBy(['_student']);
                $tasks_ball = $tasks_ball->all();

                $tasks_ball_test = EStudentTaskActivity::find();
                $tasks_ball_test->select('SUM (mark)/COUNT(_subject_task) as mark, COUNT(_subject_task) as task_count, _student');
                $tasks_ball_test->leftJoin('e_subject_task', 'e_subject_task.id=_subject_task');
                $tasks_ball_test->where([
                    'e_student_task_activity._curriculum' => $model->_curriculum,
                    'e_student_task_activity._education_year' => $model->_education_year,
                    'e_student_task_activity._semester' => $model->_semester,
                    'e_student_task_activity._subject' => $model->_subject,
                    'e_subject_task._exam_type' => $model->_exam_type,
                    'e_subject_task.active' => ESubjectTask::STATUS_ENABLE,
                    'e_student_task_activity.active' => ESubjectTask::STATUS_ENABLE,
                    'e_subject_task._task_type' => ESubjectTask::TASK_TYPE_TEST,
                ]);
                //->andWhere(['<', 'e_subject_task.deadline', $exam_date])
                $tasks_ball_test->andWhere(['>', 'mark', 0]);
                $tasks_ball_test->andWhere(['not', ['mark' => null]]);
                $tasks_ball_test->andWhere(['in', 'e_student_task_activity._training_type', $trainings]);
                $tasks_ball_test->andWhere(['in', 'e_student_task_activity._employee', $employees]);
                $tasks_ball_test->andWhere(['in', '_student', $st_rating]);
                if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                    $tasks_ball_test->andWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST]]);
                } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                    $tasks_ball_test->andWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND]]);
                } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                    $tasks_ball_test->andWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND, FinalExamType::FINAL_EXAM_TYPE_THIRD]]);
                }
                $tasks_ball_test->groupBy(['_student']);
                $tasks_ball_test = $tasks_ball_test->all();

            } else {
                $tasks_ball = EStudentTaskActivity::find();
                $tasks_ball->select('SUM (mark)/COUNT(_subject_task) as mark, COUNT(_subject_task) as task_count, _student');
                $tasks_ball->leftJoin('e_subject_task', 'e_subject_task.id=_subject_task');
                $tasks_ball->where([
                    'e_student_task_activity._curriculum' => $model->_curriculum,
                    'e_student_task_activity._education_year' => $model->_education_year,
                    'e_student_task_activity._semester' => $model->_semester,
                    'e_student_task_activity._subject' => $model->_subject,
                    'e_subject_task._exam_type' => $model->_exam_type,
                    'e_subject_task.active' => ESubjectTask::STATUS_ENABLE,
                    'e_student_task_activity.active' => ESubjectTask::STATUS_ENABLE,
                ]);
                //->andWhere(['<', 'e_subject_task.deadline', $exam_date])
                $tasks_ball->andWhere(['>', 'mark', 0]);
                $tasks_ball->andWhere(['not', ['mark' => null]]);
                $tasks_ball->andWhere(['in', 'e_student_task_activity._training_type', $trainings]);
                $tasks_ball->andWhere(['in', 'e_student_task_activity._employee', $employees]);
                $tasks_ball->andWhere(['in', '_student', $st_rating]);
                if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                    $tasks_ball->andWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST]]);
                } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                    $tasks_ball->andWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND]]);
                } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                    $tasks_ball->andWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND, FinalExamType::FINAL_EXAM_TYPE_THIRD]]);
                }
                $tasks_ball->groupBy(['_student']);
                $tasks_ball = $tasks_ball->all();


            }

        } else {
            $tasks = ESubjectTaskStudent::find()
                ->select('SUM (e_subject_task.max_ball) as mark')
                ->leftJoin('e_subject_task', 'e_subject_task.id=_subject_task')
                ->where([
                    'e_subject_task_student._curriculum' => $model->_curriculum,
                    'e_subject_task_student._education_year' => $model->_education_year,
                    'e_subject_task_student._semester' => $model->_semester,
                    'e_subject_task_student._subject' => $model->_subject,
                    'e_subject_task._exam_type' => $model->_exam_type,
                    'e_subject_task.active' => ESubjectTask::STATUS_ENABLE,
                    'e_subject_task_student.active' => ESubjectTaskStudent::STATUS_ENABLE,
                    'e_subject_task_student.final_active' => 1,
                ])
                //->andWhere(['<=', 'e_subject_task.deadline', $exam_date])
                ->andWhere(['in', 'e_subject_task_student._training_type', $trainings])
                ->andWhere(['in', 'e_subject_task_student._employee', $employees])
                ->andWhere(['in', '_student', $st_rating])
                ->groupBy(['e_subject_task_student._student'])
                ->one();

            $tasks_ball = EStudentTaskActivity::find();
            $tasks_ball->select('SUM (mark) as mark, COUNT(_subject_task) as task_count, _student');
            $tasks_ball->leftJoin('e_subject_task', 'e_subject_task.id=_subject_task');
            $tasks_ball->where([
                'e_student_task_activity._curriculum' => $model->_curriculum,
                'e_student_task_activity._education_year' => $model->_education_year,
                'e_student_task_activity._semester' => $model->_semester,
                'e_student_task_activity._subject' => $model->_subject,
                'e_subject_task._exam_type' => $model->_exam_type,
                'e_subject_task.active' => ESubjectTask::STATUS_ENABLE,
                'e_student_task_activity.active' => ESubjectTask::STATUS_ENABLE,
            ]);
            //->andWhere(['<', 'e_subject_task.deadline', $exam_date])
            $tasks_ball->andWhere(['>', 'mark', 0]);
            $tasks_ball->andWhere(['not', ['mark' => null]]);
            $tasks_ball->andWhere(['in', 'e_student_task_activity._training_type', $trainings]);
            $tasks_ball->andWhere(['in', 'e_student_task_activity._employee', $employees]);
            $tasks_ball->andWhere(['in', '_student', $st_rating]);
            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                $tasks_ball->andWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST]]);
            } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                $tasks_ball->andWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND]]);
            } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                $tasks_ball->andWhere(['in', 'e_student_task_activity._final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND, FinalExamType::FINAL_EXAM_TYPE_THIRD]]);
            }
            $tasks_ball->groupBy(['_student']);
            $tasks_ball = $tasks_ball->all();


        }

        //print_r($st);

        /* $ratings = EPerformance::find();
         $ratings->andFilterwhere([
             '_education_year' => $model->_education_year,
             '_semester' => $model->_semester,
             '_subject' => $model->_subject,
             '_exam_type' => $model->_exam_type,
         ]);
         $ratings->andFilterwhere(['in', '_student', $st]);
         $ratings = $ratings->all();*/
        $ball = array();

        $ratings_passed_all = EPerformance::find();
        $ratings_passed_all->andFilterwhere([
            '_education_year' => $model->_education_year,
            '_semester' => $model->_semester,
            '_subject' => $model->_subject,
            '_exam_type' => ExamType::EXAM_TYPE_OVERALL,
            'passed_status' => 1,
        ]);
        $ratings_passed_all->andFilterwhere(['in', '_student', $st_rating]);
        $ratings_passed_all = $ratings_passed_all->all();

        if (count($ratings_passed_all) > 0) {
            foreach ($students as $student) {
                foreach ($ratings_passed_all as $rating) {
                    if ($student->_student == $rating->_student) {
                        $ball[$student->_student][ExamType::EXAM_TYPE_OVERALL] = $rating->passed_status;
                    }
                }
            }
        }


        $ratings_final_all = EPerformance::find();
        $ratings_final_all->andFilterwhere([
            '_education_year' => $model->_education_year,
            '_semester' => $model->_semester,
            '_subject' => $model->_subject,
            '_exam_type' => ExamType::EXAM_TYPE_FINAL,
        ]);
        $ratings_final_all->andFilterwhere(['in', '_student', $st_rating]);
        $ratings_final_all = $ratings_final_all->all();

        if (count($ratings_final_all) > 0) {
            foreach ($students as $student) {
                foreach ($ratings_final_all as $rating) {
                    if ($student->_student == $rating->_student) {
                        $ball[$student->_student][ExamType::EXAM_TYPE_FINAL] = $rating->grade;
                    }
                }
            }
        }

        // echo "<pre>";
        // print_r($ball);

        $task_done_students = 0;
        $test_done_students = 0;
        $grades_students = 0;
        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
            foreach ($rated_students as $student) {
                foreach ($tasks_ball as $task) {
                    if ($student['id'] == $task->_student) {

                        $ball[$student['id']][0] = round($task->mark, 1);
                        $ball[$student['id']][3] = $task->task_count;
                        $task_done_students++;

                    }
                }
            }
            if ($midterm) {
                foreach ($rated_students as $student) {
                    foreach ($tasks_ball_test as $task) {
                        if ($student['id'] == $task->_student) {
                            $ball[$student['id']][2] = round($task->mark, 1);
                            $test_done_students++;
                        }
                    }
                }
            }

            if (count($ratings) > 0) {
                foreach ($rated_students as $student) {
                    foreach ($ratings as $rating) {
                        if ($student['id'] == $rating->_student) {
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                $ball[$student['id']][1] = $rating->grade;
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                $ball[$student['id']][12] = $rating->grade;
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                $ball[$student['id']][13] = $rating->grade;
                            }
                        }
                    }
                }
            }

            $fillCount = 0;
            if ($ft = $this->get('fill_task')) {

                $fillTest = true;
                //if($midterm) {
                /*echo "<pre>";
                foreach ($ratings as $rating) {
                    print_r($rating->_student);
                }*/
                if (count($ratings) > 0) {
                    foreach ($rated_students as $student) {
                        foreach ($ratings as $rating) {
                            if ($student['id'] == $rating->_student) {
                                if ($rating->grade === '0.0' || empty($rating->grade) || $rating->grade === 0) {
                                    if ($midterm) {
                                        if ($model->final_exam_type == $rating->_final_exam_type) {
                                            if (@$ball[$student['id']][2] > 0) {
                                                if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST)
                                                    @$ball[$student['id']][1] = round(@$ball[$student['id']][2], 0);
                                                elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND)
                                                    @$ball[$student['id']][12] = round(@$ball[$student['id']][2], 0);
                                                elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD)
                                                    @$ball[$student['id']][13] = round(@$ball[$student['id']][2], 0);
                                                $fillCount++;
                                            }
                                        }
                                    } /*else {
                                        if ($model->final_exam_type == $rating->_final_exam_type) {
                                            if (@$ball[$student['id']][0] > 0) {
                                                if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST)
                                                    @$ball[$student['id']][1] = round(@$ball[$student['id']][0], 0);
                                                elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND)
                                                    @$ball[$student['id']][12] = round(@$ball[$student['id']][0], 0);
                                                elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD)
                                                    @$ball[$student['id']][13] = round(@$ball[$student['id']][0], 0);
                                                $fillCount++;
                                            }
                                        }
                                    }*/
                                } /*else {
                                    if ($model->final_exam_type == $rating->_final_exam_type) {
                                        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST)
                                            @$ball[$student['id']][1] = @$rating->grade;
                                        elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND)
                                            @$ball[$student['id']][12] = @$rating->grade;
                                        elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD)
                                            @$ball[$student['id']][13] = @$rating->grade;
                                    }
                                    //$ball[$student->_student][2] = $rating->regrade;
                                }*/
                            }
                        }
                    }
                } else {
                    foreach ($rated_students as $student) {
                        if ($midterm) {
                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][1] = round(@$ball[$student['id']][2], 0);
                            }
                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                @$ball[$student['id']][12] = round(@$ball[$student['id']][2], 0);
                            }
                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                @$ball[$student['id']][13] = round(@$ball[$student['id']][2], 0);
                            }
                            $fillCount++;
                        } /*else {
                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][1] = round(@$ball[$student['id']][0], 0);
                            }
                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                @$ball[$student['id']][12] = round(@$ball[$student['id']][0], 0);
                            }
                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                @$ball[$student['id']][13] = round(@$ball[$student['id']][0], 0);
                            }

                        }*/
                    }
                }
            }
            if ($fillCount > 0) {
                $this->addSuccess(__('{count} ta talaba natijasi yuklab olindi', ['count' => $fillCount]));
            }
        } else {
            foreach ($rated_students as $student) {
                foreach ($tasks_ball as $task) {
                    if ($student['id'] == $task->_student) {
                        // if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                        $ball[$student['id']][0] = round($task->mark, 0);
                        $ball[$student['id']][3] = $task->task_count;
                        // }
                        /* elseif($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                             $ball[$student->_student][0] = round($task->mark, 0);
                             $ball[$student->_student][3] = $task->task_count;
                         }
                         elseif($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                             $ball[$student->_student][0] = round($task->mark, 0);
                             $ball[$student->_student][3] = $task->task_count;
                         }*/
                    }
                }
            }

            foreach ($rated_students as $student) {
                foreach ($ratings as $rating) {
                    if ($student['id'] == $rating->_student) {
                        // $ball[$student->_student][1] = $rating->grade - @$ball[$student->_student][0];
                        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                $ball[$student['id']][1] = $rating->grade - @$ball[$student['id']][0];
                            }
                        }
                        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                $ball[$student['id']][1] = $rating->grade;
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                $ball[$student['id']][12] = $rating->grade - @$ball[$student['id']][0];
                            }
                        }
                        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                $ball[$student['id']][1] = $rating->grade;
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {

                                $ball[$student['id']][12] = $rating->grade;
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                $ball[$student['id']][13] = $rating->grade - @$ball[$student['id']][0];
                            }
                        }
                    }
                }
            }
        }

        if (!$midterm) {
            $grades_ball = EStudentGrade::find();
            //$grades_ball->select('SUM (grade)/COUNT(id) as grade, COUNT(id) as grade_count, _student');
            $grades_ball->select('SUM(grade) grade, COUNT(id) as grade_count, _student');
            $grades_ball->where([
                'e_student_grade._education_year' => $model->_education_year,
                'e_student_grade._semester' => $model->_semester,
                'e_student_grade._subject' => $model->_subject,
            ]);
            $grades_ball->andWhere(['<', 'e_student_grade.lesson_date', $exam_date]);
            $grades_ball->andWhere(['>', 'grade', 0]);
            $grades_ball->andWhere(['not', ['grade' => null]]);

            $grades_ball->andWhere(['in', 'e_student_grade._training_type', $trainings]);
            $grades_ball->andWhere(['in', 'e_student_grade._employee', $employees]);
            $grades_ball->andWhere(['in', '_student', $st_rating]);

            $grades_ball->groupBy(['_student']);
            $grades_ball = $grades_ball->all();
            if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                $curr_limit = $max_ball;
            } else
                $curr_limit = $tasks !== null ? ($max_ball - $tasks->mark) : $max_ball;
            foreach ($rated_students as $student) {
                foreach ($grades_ball as $task) {
                    if ($student['id'] == $task->_student) {
                        $ball[$student['id']][100] = round($task->grade, 1);
                        $ball[$student['id']][101] = $task->grade_count > 0 ? round($task->grade / $task->grade_count, 0) : '';
                        $ball[$student['id']][102] = $task->grade_count > 0 ? round($curr_limit * $task->grade / $task->grade_count / 100, 0) : '';
                        $ball[$student['id']][103] = $task->grade_count;
                        $grades_students++;
                    }
                }
            }
        }


        if ($this->get('get_current')) {
            $grade_count = $this->get('grade_count');
            return $this->renderAjax('check-rating_get_current_grades', [
                'model' => $model,
                'grade_count' => $grade_count,
                'rated_students' => $rated_students,
                'ball' => $ball,
            ]);
        }

        $fillGradeCount = 0;
        if ($fg = $this->get('fill_grades')) {
            if (count($ratings) > 0) {
                foreach ($rated_students as $student) {
                    foreach ($ratings as $rating) {
                        if ($student['id'] == $rating->_student) {
                            if ($rating->grade === '0.0' || empty($rating->grade) || $rating->grade === 0) {
                                if (!$midterm) {
                                    if ($model->final_exam_type == $rating->_final_exam_type) {
                                        if (@$ball[$student['id']][102] > 0) {
                                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST)
                                                @$ball[$student['id']][1] = round(@$ball[$student['id']][102], 0);
                                            /*elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND)
                                                @$ball[$student['id']][12] = round(@$ball[$student['id']][102], 0);
                                            elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD)
                                                @$ball[$student['id']][13] = round(@$ball[$student['id']][102], 0);*/
                                            $fillGradeCount++;
                                        }
                                    }
                                }
                            } /*else {
                                if ($model->final_exam_type == $rating->_final_exam_type) {
                                    if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST)
                                        @$ball[$student['id']][1] = round(@$rating->grade,0);
                                    elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND)
                                        @$ball[$student['id']][12] = @$rating->grade;
                                    elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD)
                                        @$ball[$student['id']][13] = @$rating->grade;
                                }

                            }*/
                        }
                    }
                }
            } else {
                foreach ($rated_students as $student) {
                    if (!$midterm) {
                        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            @$ball[$student['id']][1] = round(@$ball[$student['id']][102], 0);
                        }
                        /*if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            @$ball[$student['id']][12] = round(@$ball[$student['id']][102], 0);
                        }
                        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            @$ball[$student['id']][13] = round(@$ball[$student['id']][102], 0);
                        }*/
                        $fillGradeCount++;
                    }
                }
            }
            if ($fillGradeCount == 0) {
                $this->addError(__('Current control estimates were not copied'));
            }
        }
        if ($fillGradeCount > 0) {
            $this->addSuccess(__('Current grades of {count} students were copied', ['count' => $fillGradeCount]));
        }

        if (!$midterm) {
            $grades_ball = EStudentGrade::find();
            //$grades_ball->select('SUM (grade)/COUNT(id) as grade, COUNT(id) as grade_count, _student');
            $grades_ball->select('SUM(grade) grade, COUNT(id) as grade_count, _student');
            $grades_ball->where([
                'e_student_grade._education_year' => $model->_education_year,
                'e_student_grade._semester' => $model->_semester,
                'e_student_grade._subject' => $model->_subject,
            ]);
            $grades_ball->andWhere(['<', 'e_student_grade.lesson_date', $exam_date]);
            $grades_ball->andWhere(['>', 'grade', 0]);
            $grades_ball->andWhere(['not', ['grade' => null]]);

            $grades_ball->andWhere(['in', 'e_student_grade._training_type', $trainings]);
            $grades_ball->andWhere(['in', 'e_student_grade._employee', $employees]);
            $grades_ball->andWhere(['in', '_student', $st_rating]);

            $grades_ball->groupBy(['_student']);
            $grades_ball = $grades_ball->all();
            if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                $curr_limit = $max_ball;
            } else
                $curr_limit = $tasks !== null ? ($max_ball - $tasks->mark) : $max_ball;
            foreach ($rated_students as $student) {
                foreach ($grades_ball as $task) {
                    if ($student['id'] == $task->_student) {
                        $ball[$student['id']][100] = round($task->grade, 1);
                        $ball[$student['id']][101] = $task->grade_count > 0 ? round($task->grade / $task->grade_count, 0) : '';
                        $ball[$student['id']][102] = $task->grade_count > 0 ? round($curr_limit * $task->grade / $task->grade_count / 100, 0) : '';
                        $ball[$student['id']][103] = $task->grade_count;
                        $grades_students++;
                    }
                }
            }
        }


        if ($this->get('get_current')) {
            $grade_count = $this->get('grade_count');
            return $this->renderAjax('check-rating_get_current_grades', [
                'model' => $model,
                'grade_count' => $grade_count,
                'rated_students' => $rated_students,
                'ball' => $ball,
            ]);
        }

        $fillGradeCount = 0;
        if ($fg = $this->get('fill_grades')) {
            if (count($ratings) > 0) {
                foreach ($rated_students as $student) {
                    foreach ($ratings as $rating) {
                        if ($student['id'] == $rating->_student) {
                            if ($rating->grade === '0.0' || empty($rating->grade) || $rating->grade === 0) {
                                if (!$midterm) {
                                    if ($model->final_exam_type == $rating->_final_exam_type) {
                                        if (@$ball[$student['id']][102] > 0) {
                                            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST)
                                                @$ball[$student['id']][1] = round(@$ball[$student['id']][102], 0);
                                            /*elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND)
                                                @$ball[$student['id']][12] = round(@$ball[$student['id']][102], 0);
                                            elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD)
                                                @$ball[$student['id']][13] = round(@$ball[$student['id']][102], 0);*/
                                            $fillGradeCount++;
                                        }
                                    }
                                }
                            } /*else {
                                if ($model->final_exam_type == $rating->_final_exam_type) {
                                    if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST)
                                        @$ball[$student['id']][1] = round(@$rating->grade,0);
                                    elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND)
                                        @$ball[$student['id']][12] = @$rating->grade;
                                    elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD)
                                        @$ball[$student['id']][13] = @$rating->grade;
                                }

                            }*/
                        }
                    }
                }
            } else {
                foreach ($rated_students as $student) {
                    if (!$midterm) {
                        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            @$ball[$student['id']][1] = round(@$ball[$student['id']][102], 0);
                        }
                        /*if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            @$ball[$student['id']][12] = round(@$ball[$student['id']][102], 0);
                        }
                        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            @$ball[$student['id']][13] = round(@$ball[$student['id']][102], 0);
                        }*/
                        $fillGradeCount++;
                    }
                }
            }
            if ($fillGradeCount == 0) {
                $this->addError(__('Current control estimates were not copied'));
            }
        }
        if ($fillGradeCount > 0) {
            $this->addSuccess(__('Current grades of {count} students were copied', ['count' => $fillGradeCount]));
        }


        if (isset($_POST['btn']) && Yii::$app->request->post('student_id')) {
            $insert_user = $model->_employee;
            $connection = Yii::$app->db;
            $time = date('Y-m-d H:i:s', time());

            $sql = 'INSERT into ' . EPerformance::tableName() . ' ("_exam_schedule", "_student", "_education_year", "_semester", "_subject",  "_exam_type","exam_date", "_employee", "grade", "regrade", "_final_exam_type", "updated_at", "created_at") VALUES ';
            if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                foreach (Yii::$app->request->post('student_id') as $key => $value) {
                    //$value = ($value=="") ? 0 : $value;
                    $value = ($value == "") ? 0 : $value;
                    if ($value > ($max_ball)) {
                        $this->addError(__('Error with input data'));
                        return $this->redirect(['teacher/check-rating', 'id' => $model->id]);
                    }
                    @$value = $value;
                    $sql .= '(' . $model->id . ',' . $key . ',' . $model->_education_year . ',' . $model->_semester . ',' . $model->_subject . ',' . $model->_exam_type . ',\'' . $exam_date . '\',' . $insert_user . ',' . $value . ',' . '0' . ',' . $model->final_exam_type . ',\'' . $time . '\',\'' . $time . '\'),';
                }
            } else {
                foreach (Yii::$app->request->post('student_id') as $key => $value) {
                    //$value = ($value=="") ? 0 : $value;
                    $value = ($value == "") ? 0 : $value;
                    //if($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                    if ($value > ($max_ball - @$tasks->mark)) {
                        $this->addError(__('Error with input data'));
                        return $this->redirect(['teacher/check-rating', 'id' => $model->id]);
                    }
                    //$this->addError($record->getOneError());
                    @$value = @$ball[$key][0] + $value;
                    //}

                    $sql .= '(' . $model->id . ',' . $key . ',' . $model->_education_year . ',' . $model->_semester . ',' . $model->_subject . ',' . $model->_exam_type . ',\'' . $exam_date . '\',' . $insert_user . ',' . $value . ',' . '0' . ',' . $model->final_exam_type . ',\'' . $time . '\',\'' . $time . '\'),';
                }
            }
            $sql = substr($sql, 0, -1);
            $sql .= ' ON CONFLICT ("_student", "_education_year", "_semester", "_subject", "_exam_type", "_final_exam_type")  DO UPDATE SET grade=EXCLUDED.grade, regrade=0';
            $command = $connection->createCommand($sql);

            //if($model->exam_date >= date("Y-m-d", time())){
            $command->execute();
            $this->addSuccess(
                __('Performance `{subject}` for `{group}` edited successfully.', [
                    'subject' => $model->subject->name,
                    'group' => $model->group->name,
                ]));
            return $this->redirect(['teacher/check-rating', 'id' => $model->id]);
            //}
        } elseif (Yii::$app->request->isAjax) {
            return $this->renderAjax('check-rating', [
                'model' => $model,
                'students' => $students,
                'ball' => $ball,
                'tasks' => $tasks,
                'prev_url' => $prev_url,
                'task_done_students' => $task_done_students,
                'test_done_students' => $test_done_students,
                'grades_students' => $grades_students,
                'midterm' => $midterm,
                'rated_students' => $rated_students,
                'max_ball' => $max_ball,

                // 'tasks_ball' => $tasks_ball,
            ]);
        } else {
            return $this->renderView([
                'model' => $model,
                'students' => $students,
                'ball' => $ball,
                'tasks' => $tasks,
                'prev_url' => $prev_url,
                'task_done_students' => $task_done_students,
                'test_done_students' => $test_done_students,
                'grades_students' => $grades_students,
                'midterm' => $midterm,
                'rated_students' => $rated_students,
                'max_ball' => $max_ball,
                //'tasks_ball' => $tasks_ball,
            ]);
        }
    }

    /**
     * @resource teacher/check-overall-rating
     */
    public function actionCheckOverallRating($id = "")
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $this->activeMenu = 'teacher-examtable';
        $model = $this->findExamScheduleModel($id, Yii::$app->user->identity->_employee, ExamType::EXAM_TYPE_FINAL);


        $fillExam = false;
        if ($fe = $this->get('fill_exam')) {
            if ($fillExam = EExam::findOne($fe)) {

            }
        }

        if ($this->get('choose_exam')) {
            $searchModel = new EExam();

            return $this->renderAjax('check-overall-rating_choose_exam', [
                'searchModel' => $searchModel,
                'model' => $model,
                'dataProvider' => $searchModel->searchForSubjectAndGroup($this->getFilterParams(), $model),
            ]);
        }

        //$model->scenario = ESubjectSchedule::SCENARIO_TEACHER;

        $prev_url = Url::previous();
        $students = EStudentSubject::getStudentsByYearSemesterGroup($model->_curriculum, $model->_education_year, $model->_semester, $model->_subject, $model->_group);
        $st = array();
        foreach ($students as $value) {
            $st[$value->_student] = $value->_student;
        }
        $examTypes = ECurriculumSubjectExamType::getAllExamTypeByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject);

        $exams = array();
        foreach ($examTypes as $value) {
            $exams[$value->_exam_type] = $value->_exam_type;
        }
        //$exams [ExamType::EXAM_TYPE_LIMIT] = ExamType::EXAM_TYPE_LIMIT;
        $ratings = EPerformance::getMarksByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $exams, $model->_group);

        $ratings_passed = "";
        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
            $final_exam_types = array(FinalExamType::FINAL_EXAM_TYPE_FIRST);
            $ratings_passed = EPerformance::getPassedStudentsByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $final_exam_types);
        } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
            $final_exam_types = array(FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND);
            $ratings_passed = EPerformance::getPassedStudentsByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $final_exam_types);
        }

        $ratings_passed_list = array();
        if (is_array($ratings_passed)) {
            foreach ($ratings_passed as $item) {
                $ratings_passed_list[$item->_student] = $item->_student;
            }
        }

        //for 1-final begin
        $ratings_passed_second_three = array();
        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
            $final_exam_types = array(FinalExamType::FINAL_EXAM_TYPE_SECOND, FinalExamType::FINAL_EXAM_TYPE_THIRD);
            $ratings_passed_second_three = EPerformance::getPassedStudentsByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $final_exam_types);
        } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
            $final_exam_types = array(FinalExamType::FINAL_EXAM_TYPE_THIRD);
            $ratings_passed_second_three = EPerformance::getPassedStudentsByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $final_exam_types);
        }
        $ratings_passed_list_for_view = array();
        if (is_array($ratings_passed_second_three)) {
            foreach ($ratings_passed_second_three as $item) {
                $ratings_passed_list_for_view[$item->_student] = $item->_student;
            }
        }
        //for 1-final end


        $rated_students = array();
        foreach ($ratings as $value) {
            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                $rated_students[$value->_student]['id'] = $value->_student;
                $rated_students[$value->_student]['name'] = $value->student->fullName;
                $rated_students[$value->_student]['student_id_number'] = $value->student->student_id_number;
            } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND || $model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                if (!in_array($value->_student, $ratings_passed_list)) {
                    $rated_students[$value->_student]['id'] = $value->_student;
                    $rated_students[$value->_student]['name'] = $value->student->fullName;
                    $rated_students[$value->_student]['student_id_number'] = $value->student->student_id_number;
                }
            }
        }


        $exam_date = Yii::$app->formatter->asDate($model->exam_date, 'php:Y-m-d');


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
        /*$ratings = EPerformance::find()
        ->where([
            '_education_year' => $model->_education_year,
            '_semester' => $model->_semester,
            '_subject' => $model->_subject,

        ])
        ->andWhere(['in', '_student', $st])
           ->andWhere(['in', '_exam_type', $exams])
        ->all();*/


        $ball = array();
        $fillCount = 0;
        $notFillCount = 0;
        $min_border = 0;
        $minimum_procent = $model->curriculum->markingSystem->minimum_limit;
        $max_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, $model->_exam_type)->max_ball;
        $overall_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, ExamType::EXAM_TYPE_OVERALL)->max_ball;
        $limit_ball = 0;
        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
            $limit_ball = $overall_ball;
            $min_border = round(GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_FIVE, GradeType::GRADE_TYPE_THREE)->min_border, 0);
        } elseif ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_RATING) {
            $limit_ball = $overall_ball - $max_ball;
            $min_border = $minimum_procent * $limit_ball / 100;
        } else {
            $limit_ball = $overall_ball - $max_ball;
            $min_border = $minimum_procent * $limit_ball / 100;

        }
//echo $min_border;

        if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
            $current = 0;
            $midterm = 0;
            foreach ($ratings as $rating) {
                foreach ($rated_students as $student) {
                    if ($student['id'] == $rating->_student) {

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_OVERALL) {
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                    @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST];
                                }
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                    @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND];
                                }
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                    @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_THIRD];
                                }
                            }
                        }

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                    @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];
                                }
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                    @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND];
                                }
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                    @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD];
                                }
                            }
                            //$ball[$student->_student][$rating->_exam_type] = $rating->grade;

                            /* if ($fillExam) {
                                 //**
                                 // * @var $studentResult EExamStudent
                                  //
                                 if ($studentResult = EExamStudent::findOne(['_exam' => $fillExam->id, '_student' => $student['id']])) {
                                     if ($model->final_exam_type == $rating->_final_exam_type) {
                                         $ball[$student['id']][$rating->_exam_type] = $studentResult->mark;
                                         $fillCount++;
                                     }
                                 }
                             }*/
                        }

                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] += $rating->grade / $current_divider;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                            }

                            if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] += $rating->grade / $midterm_divider;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                            }

                        }

                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] += $rating->grade / $current_divider;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < $min_border) {
                                    if (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                    } else
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                                } else {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                                }
                            }


                            if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] += $rating->grade / $midterm_divider;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            }

                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < $min_border) {
                                    if (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                    } else
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];

                                } else {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];
                                }
                            }
                        } else {
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < $min_border) {
                                    if (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                    } else
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                                } else {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                                }
                            }

                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < $min_border) {
                                    if (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                    } else
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];

                                } else {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];
                                }
                            }
                        }

                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] += $rating->grade / $current_divider;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            }

                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < $min_border || @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] < $min_border) {
                                    if (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                    } else if (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] >= $min_border) {
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                                    } else
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD];
                                } else {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD];
                                }
                            }


                            if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] += $rating->grade / $midterm_divider;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            }

                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < $min_border || @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] < $min_border) {
                                    if (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                    } else if (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] >= $min_border) {
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];
                                    } else
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD];
                                } else {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD];
                                }
                            }


                        } else {
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < $min_border || @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] < $min_border) {
                                    if (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                    } else if (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] >= $min_border) {
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                                    } else
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD];
                                } else {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD];
                                }
                            }

                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] < $min_border || @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] < $min_border) {
                                    if (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                    } else if (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] >= $min_border) {
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];
                                    } else
                                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD];
                                } else {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD];
                                }
                            }


                        }

                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            if (!$current_active && !$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST]) / $control_divider;
                            } elseif (!$current_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST]);
                            } elseif (!$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] = (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST]);
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST];
                            }
                        }
                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            if (!$current_active && !$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND]) / $control_divider;
                            } elseif (!$current_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND]);
                            } elseif (!$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND]);
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                            }
                        } else {
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                if (!$current_active && !$midterm_active) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND]) / $control_divider;
                                } elseif (!$current_active) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND]);
                                } elseif (!$midterm_active) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND]);
                                }
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                                }
                            }
                        }
                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            //  echo @$ball[$student->_student][ExamType::EXAM_TYPE_CURRENT];

                            if (!$current_active && !$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD]) / $control_divider;
                            } elseif (!$current_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD]);
                            } elseif (!$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD]);
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD];
                            }
                        } else {
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                if (!$current_active && !$midterm_active) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD]) / $control_divider;
                                } elseif (!$current_active) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD]);
                                } elseif (!$midterm_active) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD]);
                                }
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD];
                                }
                            }
                        }

                    }
                }
            }
        } else {
            foreach ($ratings as $rating) {
                foreach ($rated_students as $student) {
                    if ($student['id'] == $rating->_student) {

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_OVERALL) {
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                    @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST];
                                }
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                    @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND];
                                }
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                    @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_THIRD];
                                }
                            }
                        }

                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] += $rating->grade;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                            }

                            if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] += $rating->grade;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                            }
                        }

                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] += $rating->grade;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                            }

                            if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] += $rating->grade;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];
                            }


                        }
                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] += $rating->grade;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD];
                            }

                            if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] += $rating->grade;
                            } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD];
                            }

                        }

                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            if (!$current_active && !$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST]);
                            } elseif (!$current_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST]);
                            } elseif (!$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] = (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST]);
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST];
                            }
                        }
                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                //if(@$ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST] < $min_border) {
                                //if (@$ball[$student->_student][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] >= @$ball[$student->_student][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND]) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                }
                                // }
                                //}
                            }
                            if (!$current_active && !$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND]);
                            } elseif (!$current_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND]);
                            } elseif (!$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND]);
                            }

                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                //if(@$ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST] < $min_border) {
                                //if (@$ball[$student->_student][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] >= @$ball[$student->_student][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND]) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST];
                                } else {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                                }
                                /*} else {
                                    @$ball[$student->_student][ExamType::EXAM_TYPE_LIMIT] = $ball[$student->_student][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                                }*/
                                //}
                                /*else{
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                                }*/
                            }
                        } else {

                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                //if(@$ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST] < $min_border) {
                                //if (@$ball[$student->_student][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] >= @$ball[$student->_student][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND]) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST];
                                } else {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                                }
                                /*} else {
                                    @$ball[$student->_student][ExamType::EXAM_TYPE_LIMIT] = $ball[$student->_student][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                                }*/
                                //}
                                /*else{
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                                }*/
                            }
                        }
                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                //if (@$ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST] < $min_border && @$ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND] < $min_border) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                } else if (@$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] >= $min_border) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];
                                }
                                //}
                            }

                            if (!$current_active && !$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD]);
                            } elseif (!$current_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD]);
                            } elseif (!$midterm_active) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = (@$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD]);
                            }
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                //if (@$ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST] < $min_border && @$ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND] < $min_border) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST];
                                } else if (@$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] >= $min_border) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                                } else {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD];
                                }
                                //}
                                /*else{
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD];
                                }*/
                            }
                        } else {
                            if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                //if (@$ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST] < $min_border && @$ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND] < $min_border) {
                                if (@$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] >= $min_border) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST];
                                } else if (@$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] >= $min_border) {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                                } else {
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD];
                                }
                                //}
                                /*else{
                                    @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD];
                                }*/
                            }
                        }

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                    @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];
                                }
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                    @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND];
                                }
                            }
                            if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                                if (@$model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                    @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD];
                                }
                            }

                            /*if ($fillExam) {
                                //**
                                 // @var $studentResult EExamStudent
                                 //*
                                if ($studentResult = EExamStudent::findOne(['_exam' => $fillExam->id, '_student' => $student['id']])) {
                                    if ($model->final_exam_type == $rating->_final_exam_type) {
                                        $ball[$student['id']][$rating->_exam_type] = $studentResult->mark;
                                        $fillCount++;
                                    }
                                }
                            }*/
                        }
                    }
                }
            }
        }
        foreach ($rated_students as $student) {
            if ($fillExam) {
                //**
                // * @var $studentResult EExamStudent
                //
                $markingSystem = $model->curriculum->markingSystem;

                if ($studentResult = EExamStudent::findOne(['_exam' => $fillExam->id, '_student' => $student['id']])) {
                    //if ($model->final_exam_type == $rating->_final_exam_type) {
                    if ($markingSystem->isRatingSystem() || $studentResult->percent >= $markingSystem->minimum_limit) {
                        $ball[$student['id']][ExamType::EXAM_TYPE_FINAL] = round($studentResult->mark);
                        $fillCount++;
                    } else {
                        $notFillCount++;
                    }
                    //}
                }
            }
        }
        if ($fillCount) {
            $this->addSuccess(__('{count} ta talaba natijasi yuklab olindi', ['count' => $fillCount]));
        }
        if ($notFillCount) {
            $this->addWarning(__('{count} ta talaba {name} baxolash tizimining minimal {limit}% chegarasidan o\'tmadi', ['count' => $notFillCount, 'name' => $markingSystem->name, 'limit' => $markingSystem->minimum_limit]));
        }

        if ($this->get('access')) {
            $phpWord = new  \PhpOffice\PhpWord\PhpWord();
            $section = $phpWord->createSection();
            $template = "access.docx";
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(Yii::getAlias('@root/templates/' . $template));
            $templateProcessor->setValue('faculty', htmlspecialchars($model->curriculum->department->name));
            $templateProcessor->setValue('semester', htmlspecialchars($model->semester->name));
            $templateProcessor->setValue('group', htmlspecialchars($model->group->name));
            $templateProcessor->setValue('subject', htmlspecialchars($model->subject->name));
            $templateProcessor->cloneRow('student', count($rated_students));
            $j = 0;
            $access = "";
            $access_cur = "";
            $access_mid = "";
            $current = 0;
            $midterm = 0;

            foreach ($rated_students as $key => $val) {
                $j++;
                $templateProcessor->setValue('student#' . $j, htmlspecialchars($val['name']));
                $templateProcessor->setValue('n#' . $j, htmlspecialchars($val['student_id_number']));
                $current = @number_format($ball[$val['id']][ExamType::EXAM_TYPE_CURRENT], 0, ',', '');
                $midterm = @number_format($ball[$val['id']][ExamType::EXAM_TYPE_MIDTERM], 0, ',', '');


                if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                    //if(!in_array(ExamType::EXAM_TYPE_CURRENT, $exams)){
                    $access_cur = $current >= $min_border ? true : false;
                    //}
                    //if(!in_array(ExamType::EXAM_TYPE_MIDTERM, $exams)){
                    $access_mid = $midterm >= $min_border ? true : false;
                    //}
                    if (!$current_active && !$midterm_active) {
                        if ($access_cur && $access_mid)
                            $access = __('Access');
                        else
                            $access = __('Not Access');
                    } elseif ($current_active) {
                        if ($access_mid)
                            $access = __('Access');
                        else
                            $access = __('Not Access');
                    } elseif ($midterm_active) {
                        if ($access_cur)
                            $access = __('Access');
                        else
                            $access = __('Not Access');
                    }
                } else {
                    $max_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, ExamType::EXAM_TYPE_FINAL)->max_ball;
                    $overall_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, ExamType::EXAM_TYPE_OVERALL)->max_ball;
                    $limit = $model->curriculum->markingSystem->minimum_limit;
                    //if(!in_array(ExamType::EXAM_TYPE_CURRENT, $exams)){
                    $access_cur = ($current + $midterm) >= (($overall_ball - $max_ball) * $limit / 100) ? true : false;
                    //}
                    if ($access_cur)
                        $access = __('Access');
                    else
                        $access = __('Not Access');
                }
                $templateProcessor->setValue('c#' . $j, $current);
                $templateProcessor->setValue('m#' . $j, $midterm);
                $templateProcessor->setValue('a#' . $j, $access);

            }

            $time = time();
            $file = Yii::getAlias("@runtime/access_{$time}.docx");
            $templateProcessor->saveAs($file);
            $content = file_get_contents($file);
            unlink($file);
            return Yii::$app->response->sendContentAsFile($content, $model->group->name . '-' . $model->subject->name . '.docx');


        }
        if (isset($_POST['btn']) && Yii::$app->request->post('student_id')) {
            $insert_user = $model->_employee;
            $connection = Yii::$app->db;
            $time = date('Y-m-d H:i:s', time());
            $sql = 'INSERT into ' . EPerformance::tableName() . ' ("_exam_schedule", "_student", "_education_year", "_semester", "_subject",  "_exam_type","exam_date", "_employee", "grade", "regrade", "_final_exam_type", "passed_status","updated_at", "created_at") VALUES ';

            if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                foreach (Yii::$app->request->post('student_id') as $key => $examType) {
                    $value = 0;
                    $sum = 0;
                    $final_exam_type = 0;
                    $passed_status = 0;

                    foreach ($examType as $item => $value) {
                        @$value = (@$value == "") ? 0 : @$value;
                        $exam_type_one = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, $item);
                        if (@$exam_type_one->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                            if ($value > ($max_ball)) {
                                $this->addError(__('Error with input data'));
                                return $this->redirect(['teacher/check-overall-rating', 'id' => $model->id]);
                            }
                        }
                        if (@$exam_type_one->_exam_type !== ExamType::EXAM_TYPE_OVERALL && @$exam_type_one->_exam_type !== ExamType::EXAM_TYPE_CURRENT && @$exam_type_one->_exam_type !== ExamType::EXAM_TYPE_MIDTERM) {
                            //if(@$value >= 0){
                            $sum = @$value;
                            //}
                        }

                        if (@$exam_type_one->_exam_type === ExamType::EXAM_TYPE_OVERALL) {
                            $value = @$sum;

                            if ($value >= $min_border) {
                                $passed_status = 1;
                            } else {
                                $passed_status = 0;
                                //$value = 0;
                            }
                        }
                        // $value = ($value=="") ? 0 : $value;
                        $sql .= '(' . $model->id . ',' . $key . ',' . $model->_education_year . ',' . $model->_semester . ',' . $model->_subject . ',' . $item . ',\'' . $exam_date . '\',' . $insert_user . ',' . @$value . ',' . '0' . ',' . $model->final_exam_type . ',' . $passed_status . ',\'' . $time . '\',\'' . $time . '\'),';
                    }
                }
            } else {
                foreach (Yii::$app->request->post('student_id') as $key => $examType) {
                    $value = 0;
                    $sum = 0;
                    $final_exam_type = 0;
                    $passed_status = 0;
                    foreach ($examType as $item => $value) {
                        @$value = (@$value == "") ? 0 : @$value;
                        $exam_type_one = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, $item);

                        if (@$exam_type_one->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                            if ($value > ($max_ball)) {
                                $this->addError(__('Error with input data'));
                                return $this->redirect(['teacher/check-overall-rating', 'id' => $model->id]);
                            }
                        }

                        if (@$exam_type_one->_exam_type !== ExamType::EXAM_TYPE_OVERALL && @$exam_type_one->_exam_type !== ExamType::EXAM_TYPE_CURRENT && @$exam_type_one->_exam_type !== ExamType::EXAM_TYPE_MIDTERM) {

                            //if(@$value >= 0){
                            $sum += @$value;
                            //}
                        }

                        if (@$exam_type_one->_exam_type === ExamType::EXAM_TYPE_OVERALL) {
                            $value = @$sum;
                            //   echo $value;
                            //  echo $minimum_procent;
                            if ($value >= $overall_ball * $minimum_procent / 100) {
                                $passed_status = 1;
                            } else {
                                $passed_status = 0;
                                //$value = 0;
                            }

                        }
                        // $value = ($value=="") ? 0 : $value;
                        $sql .= '(' . $model->id . ',' . $key . ',' . $model->_education_year . ',' . $model->_semester . ',' . $model->_subject . ',' . $item . ',\'' . $exam_date . '\',' . $insert_user . ',' . @$value . ',' . '0' . ',' . $model->final_exam_type . ',' . $passed_status . ',\'' . $time . '\',\'' . $time . '\'),';
                    }
                }

                // $sql .= '('. $model->id.','. $key.',' . $model->_education_year. ','. $model->_semester.',' .$model->_subject.','. $model->_exam_type.',\''.$exam_date.'\','. $insert_user.','. $value.','.'0'. ',\'' . $time . '\',\'' . $time . '\'),';
            }
            $sql = substr($sql, 0, -1);
            $sql .= ' ON CONFLICT ("_student", "_education_year", "_semester", "_subject", "_exam_type", "_final_exam_type")  DO UPDATE SET grade=EXCLUDED.grade, passed_status=EXCLUDED.passed_status, regrade=0;';
            $command = $connection->createCommand($sql);
            if (Config::get(Config::CONFIG_COMMON_PERFORMANCE_CONTROL)) {
                $input = false;

                if (Yii::$app->formatter->asDate($model->exam_date, 'php:Y-m-d') >= date("Y-m-d", time())) {
                    $command->execute();
                    $this->addSuccess(
                        __('Performance`{subject}` for `{group}` edited successfully.', [
                            'subject' => $model->subject->name,
                            'group' => $model->group->name,
                        ]));
                    $input = true;

                } else if (date('Y-m-d', strtotime(Yii::$app->formatter->asDate($model->exam_date, 'php:Y-m-d') . "2 day")) >= date("Y-m-d", time())) {

                    $command->execute();
                    $this->addSuccess(
                        __('Performance`{subject}` for `{group}` edited successfully.', [
                            'subject' => $model->subject->name,
                            'group' => $model->group->name,
                        ]));
                    $input = true;

                } else {
                    $this->addError(
                        __('Performance`{subject}` for `{group}` cannot save.', [
                            'subject' => $model->subject->name,
                            'group' => $model->group->name,
                        ]));
                }
            } else {
                $command->execute();
                $this->addSuccess(
                    __('Performance`{subject}` for `{group}` edited successfully.', [
                        'subject' => $model->subject->name,
                        'group' => $model->group->name,
                    ]));
                $input = true;
            }


            return $this->redirect(['teacher/check-overall-rating', 'id' => $id]);
        } elseif (Yii::$app->request->isAjax) {
            return $this->renderAjax('check-overall-rating', [
                'model' => $model,
                'students' => $students,
                'examTypes' => $examTypes,
                'ball' => $ball,
                'prev_url' => $prev_url,
                'exams' => $exams,
                'current_active' => $current_active,
                'midterm_active' => $midterm_active,
                'minimum_procent' => $minimum_procent,
                'max_ball' => $max_ball,
                'overall_ball' => $overall_ball,
                'limit_ball' => $limit_ball,
                'min_border' => $min_border,
                'rated_students' => $rated_students,
                'ratings_passed_list_for_view' => $ratings_passed_list_for_view,

            ]);
        } else {

            return $this->renderView([
                'model' => $model,
                'students' => $students,
                'examTypes' => $examTypes,
                'ball' => $ball,
                'prev_url' => $prev_url,
                'exams' => $exams,
                'current_active' => $current_active,
                'midterm_active' => $midterm_active,
                'minimum_procent' => $minimum_procent,
                'max_ball' => $max_ball,
                'overall_ball' => $overall_ball,
                'limit_ball' => $limit_ball,
                'min_border' => $min_border,
                'rated_students' => $rated_students,
                'ratings_passed_list_for_view' => $ratings_passed_list_for_view,

            ]);
        }
    }

    /**
     * @resource teacher/check-overall
     */
    public function actionCheckOverall($id = "")
    {
        $this->activeMenu = 'teacher-examtable';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }

        $model = $this->findExamScheduleModel($id, Yii::$app->user->identity->_employee /*, ExamType::EXAM_TYPE_OVERALL*/);

        $prev_url = Url::previous();

        $students = EStudentSubject::getStudentsByYearSemesterGroup($model->_curriculum, $model->_education_year, $model->_semester, $model->_subject, $model->_group);

        $st = array();
        foreach ($students as $value) {
            $st[$value->_student] = $value->_student;
        }

        $exam_types = array(ExamType::EXAM_TYPE_OVERALL);
        $ratings = EPerformance::getMarksByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $exam_types, $model->_group);
        $ratings_passed = array();
        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
            $final_exam_types = array(FinalExamType::FINAL_EXAM_TYPE_FIRST);
            $ratings_passed = EPerformance::getPassedStudentsByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $final_exam_types);
        } elseif ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
            $final_exam_types = array(FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND);
            $ratings_passed = EPerformance::getPassedStudentsByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $final_exam_types);
        }
        $ratings_passed_list = array();
        if (is_array($ratings_passed)) {
            foreach ($ratings_passed as $item) {
                $ratings_passed_list[$item->_student] = $item->_student;
            }
        }

        //for 1-final begin
        $ratings_passed_second_three = array();
        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
            $final_exam_types = array(FinalExamType::FINAL_EXAM_TYPE_SECOND, FinalExamType::FINAL_EXAM_TYPE_THIRD);
            $ratings_passed_second_three = EPerformance::getPassedStudentsByCurriculumSemester($model->_education_year, $model->_semester, $model->_subject, $st, $final_exam_types);
        }
        $ratings_passed_list_for_view = array();
        if (is_array($ratings_passed_second_three)) {
            foreach ($ratings_passed_second_three as $item) {
                $ratings_passed_list_for_view[$item->_student] = $item->_student;
            }
        }
        //for 1-final end

        $rated_students = array();

        if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
            foreach ($students as $value) {
                $rated_students[$value->_student]['id'] = $value->_student;
                $rated_students[$value->_student]['name'] = $value->student->fullName;
            }
        }

        foreach ($ratings as $value) {
            if ($model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND || $model->final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                if (!in_array($value->_student, $ratings_passed_list)) {
                    $rated_students[$value->_student]['id'] = $value->_student;
                    $rated_students[$value->_student]['name'] = $value->student->fullName;
                    //   $rated_students[$value->_student]['student_id_number'] = $value->student->student_id_number;
                }
            }
        }

        $exam_date = Yii::$app->formatter->asDate($model->exam_date, 'php:Y-m-d');

        $examTypes = ECurriculumSubjectExamType::getAllExamTypeByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject);
        $exams = array();
        foreach ($examTypes as $value) {
            $exams[$value->_exam_type] = $value->_exam_type;
        }
        /*$ratings = EPerformance::find()
            ->where([
                '_education_year' => $model->_education_year,
                '_semester' => $model->_semester,
                '_subject' => $model->_subject,
                '_exam_type' => ExamType::EXAM_TYPE_OVERALL,
            ])
            ->andWhere(['in', '_student', $st])
            ->all();*/

        $ball = array();
        foreach ($students as $student) {
            foreach ($ratings as $rating) {
                if ($student->_student == $rating->_student) {
                    if ($rating->_exam_type == ExamType::EXAM_TYPE_OVERALL) {

                        //$ball[$student->_student][$rating->_exam_type] = $rating->grade;

                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            $ball[$student->_student][FinalExamType::FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                        }
                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            $ball[$student->_student][FinalExamType::FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                        }
                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            $ball[$student->_student][FinalExamType::FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                        }

                    }
                }
            }
        }

        if (isset($_POST['btn']) && Yii::$app->request->post('student_id')) {
            $insert_user = $model->_employee;
            $connection = Yii::$app->db;
            $time = date('Y-m-d H:i:s', time());
            $passed_status = 0;
            $sql = 'INSERT into ' . EPerformance::tableName() . ' ("_exam_schedule", "_student", "_education_year", "_semester", "_subject",  "_exam_type","exam_date", "_employee", "grade", "regrade", "_final_exam_type", "passed_status", "updated_at", "created_at") VALUES ';
            $min_border = 0;
            $minimum_procent = $model->curriculum->markingSystem->minimum_limit;
            $overall_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject, ExamType::EXAM_TYPE_OVERALL)->max_ball;
            if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                $min_border = round(GradeType::getGradeByCode(MarkingSystem::MARKING_SYSTEM_FIVE, GradeType::GRADE_TYPE_THREE)->min_border, 0);
            } else {
                $min_border = $overall_ball * $minimum_procent / 100;
            }
            foreach (Yii::$app->request->post('student_id') as $key => $value) {
                $final_exam_type = 0;
                @$value = (@$value == "") ? 0 : @$value;
                if ($value >= $min_border) {
                    $passed_status = 1;
                } else {
                    $passed_status = 0;
                }
                $sql .= '(' . $model->id . ',' . $key . ',' . $model->_education_year . ',' . $model->_semester . ',' . $model->_subject . ',' . $model->_exam_type . ',\'' . $exam_date . '\',' . $insert_user . ',' . @$value . ',' . '0' . ',' . $model->final_exam_type . ',' . $passed_status . ',\'' . $time . '\',\'' . $time . '\'),';
            }

            $sql = substr($sql, 0, -1);
            $sql .= ' ON CONFLICT ("_student", "_education_year", "_semester", "_subject", "_exam_type", "_final_exam_type")  DO UPDATE SET grade=EXCLUDED.grade, passed_status=EXCLUDED.passed_status, regrade=0;';
            $command = $connection->createCommand($sql);

            //if($model->exam_date >= date("Y-m-d", time())){
            if (Config::get(Config::CONFIG_COMMON_PERFORMANCE_CONTROL)) {
                $input = false;
                if (Yii::$app->formatter->asDate($model->exam_date, 'php:Y-m-d') >= date("Y-m-d", time())) {
                    $command->execute();
                    $this->addSuccess(
                        __('Performance`{subject}` for `{group}` edited successfully.', [
                            'subject' => $model->subject->name,
                            'group' => $model->group->name,
                        ]));
                    $input = true;

                } else if (date('Y-m-d', strtotime(Yii::$app->formatter->asDate($model->exam_date, 'php:Y-m-d') . "2 day")) >= date("Y-m-d", time())) {

                    $command->execute();
                    $this->addSuccess(
                        __('Performance`{subject}` for `{group}` edited successfully.', [
                            'subject' => $model->subject->name,
                            'group' => $model->group->name,
                        ]));
                    $input = true;

                } else {
                    $this->addError(
                        __('Performance`{subject}` for `{group}` cannot save.', [
                            'subject' => $model->subject->name,
                            'group' => $model->group->name,
                        ]));
                }
            } else {
                $command->execute();
                $this->addSuccess(
                    __('Performance`{subject}` for `{group}` edited successfully.', [
                        'subject' => $model->subject->name,
                        'group' => $model->group->name,
                    ]));
                $input = true;
            }


            return $this->redirect(['teacher/check-overall', 'id' => $id]);

            //}
        } elseif (Yii::$app->request->isAjax) {
            return $this->renderAjax('check-overall', [
                'model' => $model,
                'students' => $students,
                'examTypes' => $examTypes,
                'ball' => $ball,
                'prev_url' => $prev_url,
                'exams' => $exams,
                'rated_students' => $rated_students,
                'ratings_passed_list_for_view' => $ratings_passed_list_for_view,
            ]);
        } else {
            return $this->renderView([
                'model' => $model,
                'students' => $students,
                'examTypes' => $examTypes,
                'ball' => $ball,
                'prev_url' => $prev_url,
                'exams' => $exams,
                'rated_students' => $rated_students,
                'ratings_passed_list_for_view' => $ratings_passed_list_for_view,
            ]);
        }

    }

    /**
     * @resource teacher/print-rating
     */
    public function actionPrintRating($education_year = "", $semester = "", $group = "", $subject = "", $final_exam_type = "")
    {
        $this->activeMenu = 'teacher-examtable';
        /**
         * @var $group_model EGroup
         * @var $subject ECurriculumSubject
         */
        $group_model = EGroup::findOne($group);
        if ($group_model === null) {
            $this->notFoundException();
        }
        $prev_url = Url::previous();

        $subject = ECurriculumSubject::getByCurriculumSemesterSubject($group_model->_curriculum, $semester, $subject);
        if ($subject === null) {
            $this->notFoundException();
        }

        $students = EStudentSubject::getStudentsByYearSemesterGroup($group_model->_curriculum, $education_year, $subject->_semester, $subject->_subject, $group_model->id);

        $st = array();
        $level = "";
        foreach ($students as $value) {
            $st[$value->_student] = $value->_student;
            //$level  = $value->level->name;
        }

        $ratings = EPerformance::find();
        $ratings->leftJoin('e_student', 'e_student.id=_student');
        $ratings->leftJoin('e_student_meta', 'e_student_meta._student=e_performance._student AND e_student_meta._education_year=e_performance._education_year AND e_student_meta._semestr=e_performance._semester');
//        $ratings->leftJoin('e_student_meta', 'e_student_meta._student=e_performance._student');
        $ratings->where([
            'e_performance._education_year' => $education_year,
            '_semester' => $subject->_semester,
            '_subject' => $subject->_subject,
            'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
            'e_student_meta._semestr' => $semester,
            'e_student_meta._education_year' => $education_year,
            'e_student_meta._group' => $group,
        ]);
        if ($final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
            $ratings->andFilterWhere(['in', '_final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND, FinalExamType::FINAL_EXAM_TYPE_THIRD]]);
        } elseif ($final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
            $ratings->andFilterWhere(['in', '_final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_SECOND]]);
        } elseif ($final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
            $ratings->andFilterWhere(['in', '_final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_THIRD]]);
        }
        $ratings->andFilterWhere(['in', 'e_performance._student', $st]);
        $ratings->orderBy(['e_student.second_name' => SORT_ASC, 'e_student.first_name' => SORT_ASC, 'e_student.third_name' => SORT_ASC]);
        $ratings = $ratings->all();
        $ratings_passed = "";
        if ($final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
            $ratings_passed = EPerformance::find();
            $ratings_passed->where([
                '_education_year' => $education_year,
                '_semester' => $subject->_semester,
                '_subject' => $subject->_subject,
                '_exam_type' => ExamType::EXAM_TYPE_OVERALL,
                '_final_exam_type' => FinalExamType::FINAL_EXAM_TYPE_FIRST,
                'passed_status' => 1,
            ]);
            $ratings_passed->andFilterWhere(['in', '_student', $st]);
            $ratings_passed = $ratings_passed->all();
        } elseif ($final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
            $ratings_passed = EPerformance::find();
            $ratings_passed->where([
                '_education_year' => $education_year,
                '_semester' => $subject->_semester,
                '_subject' => $subject->_subject,
                '_exam_type' => ExamType::EXAM_TYPE_OVERALL,
                'passed_status' => 1,
            ]);
            $ratings_passed->andFilterWhere(['in', '_final_exam_type', [FinalExamType::FINAL_EXAM_TYPE_FIRST, FinalExamType::FINAL_EXAM_TYPE_SECOND]]);
            $ratings_passed->andFilterWhere(['in', '_student', $st]);
            $ratings_passed = $ratings_passed->all();
        }
        $ratings_passed_list = array();
        if (is_array($ratings_passed)) {
            foreach ($ratings_passed as $item) {
                $ratings_passed_list[$item->_student] = $item->_student;
            }
        }

        $rated_students = array();
        foreach ($ratings as $value) {
            if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                //    if($value->_exam_type == ExamType::EXAM_TYPE_OVERALL) {
                $rated_students[$value->_student]['id'] = $value->_student;
                $rated_students[$value->_student]['name'] = $value->student->fullName;
                $rated_students[$value->_student]['student_id_number'] = $value->student->student_id_number;
                //  }
            } elseif (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND || @$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                if (!in_array($value->_student, $ratings_passed_list)) {
                    $rated_students[$value->_student]['id'] = $value->_student;
                    $rated_students[$value->_student]['name'] = $value->student->fullName;
                    $rated_students[$value->_student]['student_id_number'] = $value->student->student_id_number;
                }
            }

            //$level  = $value->level->name;
        }
        $balls = array();
        @$lectureTeacher = ESubjectSchedule::getTeacherByCurriculumSemesterSubjectTrainingGroup($group_model->_curriculum, $semester, $subject->_subject, TrainingType::TRAINING_TYPE_LECTURE, $group_model->id);
        @$practiceTeacher = ESubjectSchedule::getTeacherByCurriculumSemesterSubjectTrainingGroup($group_model->_curriculum, $semester, $subject->_subject, TrainingType::TRAINING_TYPE_PRACTICE, $group_model->id);
        @$laboratoryTeacher = ESubjectSchedule::getTeacherByCurriculumSemesterSubjectTrainingGroup($group_model->_curriculum, $semester, $subject->_subject, TrainingType::TRAINING_TYPE_LABORATORY, $group_model->id);
        if ($subject->_rating_grade == RatingGrade::RATING_GRADE_SUBJECT) {
            @$examSchedule = ESubjectExamSchedule:: getFinalExamByCurriculumSubjectType($subject->_subject, $group_model->_curriculum, $semester, $group_model->id, ExamType::EXAM_TYPE_FINAL, $final_exam_type);
            $max_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($group_model->_curriculum, $semester, $subject->_subject, ExamType::EXAM_TYPE_FINAL)->max_ball;
        } else {
            @$examSchedule = ESubjectExamSchedule:: getFinalExamByCurriculumSubjectType($subject->_subject, $group_model->_curriculum, $semester, $group_model->id, ExamType::EXAM_TYPE_OVERALL, $final_exam_type);
        }
        $marking_system = $group_model->curriculum->_marking_system;
        @$employee_department = $lectureTeacher->employee->teachers->id;
        $five = GradeType::getGradeByCode($marking_system, GradeType::GRADE_TYPE_FIVE);
        $four = GradeType::getGradeByCode($marking_system, GradeType::GRADE_TYPE_FOUR);
        $three = GradeType::getGradeByCode($marking_system, GradeType::GRADE_TYPE_THREE);
        $two = GradeType::getGradeByCode($marking_system, GradeType::GRADE_TYPE_TWO);
        $minimum_procent = $group_model->curriculum->markingSystem->minimum_limit;

        $ball = array();
        foreach ($ratings as $rating) {
            foreach ($rated_students as $student) {
                if ($student['id'] == $rating->_student) {
                    //$rating->grade = ($rating->grade != 0 ? $rating->grade : '');

                    if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                        if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] += @$rating->grade;
                        } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST] = @$rating->grade;
                        }
                        if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST];
                        }

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] += $rating->grade;
                        } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                        }
                        if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST];
                        }
                        if ($rating->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];
                            }
                        }

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_OVERALL) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST];
                            }
                        }

                        // echo @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];
                        /*if ($rating->_exam_type == ExamType::EXAM_TYPE_LIMIT) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                        }
                        if(@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST){
                            @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST];
                        }*/

                    }

                    if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                        if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] += $rating->grade;
                        } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                        }
                        if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND];
                        }

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] += $rating->grade;
                        } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                        }
                        if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND];
                        }

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_FINAL) {

                            if (@$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] >= ($minimum_procent * $max_ball / 100)) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];
                            } else {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            }
                            if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND];
                            }
                            //echo @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];
                        }

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_OVERALL) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND];
                            }
                        }
                        /*if ($rating->_exam_type == ExamType::EXAM_TYPE_LIMIT) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                        }
                        if(@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND){
                            @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND];
                        }*/
                    }

                    if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                        if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_CURRENT_SECOND) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] += $rating->grade;
                        } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                        }
                        if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = $ball[$student['id']][ExamType::EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD];
                        }

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_FIRST || $rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM_SECOND) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] += $rating->grade;
                        } elseif ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                        }
                        if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = $ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD];
                        }

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                            if (@$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] >= ($minimum_procent * $max_ball / 100)) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];
                            } elseif (@$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] >= ($minimum_procent * $max_ball / 100)) {
                                @$ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND];
                            } else {
                                $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            }
                            if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD];
                            }
                        }

                        if ($rating->_exam_type == ExamType::EXAM_TYPE_OVERALL) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_THIRD];
                            }
                        }

                        /*if ($rating->_exam_type == ExamType::EXAM_TYPE_LIMIT) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                        }
                        if(@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD){
                            @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = $ball[$student['id']][ExamType::EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD];
                        }*/

                    }

                    /*if ($rating->_exam_type == ExamType::EXAM_TYPE_CURRENT) {
                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] = @$rating->grade;
                    }
                    if ($rating->_exam_type == ExamType::EXAM_TYPE_MIDTERM) {
                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] = @$rating->grade;
                    }
                    if($rating->examType->_parent == ExamType::EXAM_TYPE_CURRENT){
                        @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] += @$rating->grade;
                    }
                    if($rating->examType->_parent == ExamType::EXAM_TYPE_MIDTERM){
                        @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM] += @$rating->grade;
                    }*/


                    // @$ball[$student['id']][ExamType::EXAM_TYPE_LIMIT] = @$ball[$student['id']][ExamType::EXAM_TYPE_CURRENT] + @$ball[$student['id']][ExamType::EXAM_TYPE_MIDTERM];
                    /*if ($rating->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                        @$ball[$student['id']][$rating->_exam_type] = @$rating->grade;
                    }*/
                    /*if ($rating->_exam_type == ExamType::EXAM_TYPE_OVERALL) {
                        @$ball[$student['id']][$rating->_exam_type] = @$rating->grade;
                    }*/


                    /*if ($rating->_exam_type == ExamType::EXAM_TYPE_FINAL) {
                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST] = $rating->grade;
                            if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_FIRST) {
                                @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST];
                            }
                        }
                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND] = $rating->grade;
                            if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
                                @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND];
                            }
                        }
                        if ($rating->_final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                            $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD] = $rating->grade;
                            if (@$final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
                                @$ball[$student['id']][$rating->_exam_type] = $ball[$student['id']][ExamType::EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD];
                            }
                        }
                    }*/

                }
            }
        }

        //die();

        $phpWord = new  \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->createSection();
        /*  $simple_template = $subject->rating_grade_id;
          if($simple_template==1){
              if($model->spec_type == 1 && $model->spec_id == 7){
                  $template = "master_form.docx";
              }
              elseif($model->spec_type == 2 && $model->spec_id == 8){
                  $template = "qual_fourth.docx";
              }
              elseif($model->spec_type == 2 && $model->spec_id == 9){
                  $template = "qual_ten.docx";
              }
          }
          else{
              $template = $subject->ratingGrade->template;
          }

          $inter = RatingTypeSpec::findOne(array('study_years_id'=>$study_years_id, 'subject_id'=>$subject_id, 'spec_id'=>$model->spec_id, 'rating_type_id' => 3));
          $current = RatingTypeSpec::findOne(array('study_years_id'=>$study_years_id, 'subject_id'=>$subject_id, 'spec_id'=>$model->spec_id, 'rating_type_id' => 4));
          $final = RatingTypeSpec::findOne(array('study_years_id'=>$study_years_id, 'subject_id'=>$subject_id, 'spec_id'=>$model->spec_id, 'rating_type_id' => 5));
          $summary = RatingTypeSpec::findOne(array('study_years_id'=>$study_years_id, 'subject_id'=>$subject_id, 'spec_id'=>$model->spec_id, 'rating_type_id' => 6));

          $profile = User::findOne(array('faculty_id'=>$model->faculties_id, 'role'=>30));
          $leader = Timetable::findOne(array('study_years_id'=>$study_years_id, 'subjects_id'=>$subject_id, 'semestr'=>$semestr, 'groups_id'=>$groups_id, 'occupation_id' => 1));
  */
        if ($marking_system == MarkingSystem::MARKING_SYSTEM_RATING) {
            if ($subject->_rating_grade == RatingGrade::RATING_GRADE_SUBJECT) {
                $template = "12-shakl.docx";
            } else if ($subject->_rating_grade == RatingGrade::RATING_GRADE_SUBJECT_FINAL) {
                $template = "simple-form-add-ball.docx";
            } else {
                $template = "simple-form-ball.docx";
            }
        } elseif ($marking_system == MarkingSystem::MARKING_SYSTEM_CREDIT) {
            if ($subject->_rating_grade == RatingGrade::RATING_GRADE_SUBJECT) {
                $template = "c-form.docx";
            } else if ($subject->_rating_grade == RatingGrade::RATING_GRADE_SUBJECT_FINAL) {
                $template = "simple-form-add-ball.docx";
            } else {
                $template = "simple-form-ball.docx";
            }
        } elseif ($marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
            if ($subject->_rating_grade == RatingGrade::RATING_GRADE_SUBJECT) {
                $template = "5-shakl.docx";
            } else if ($subject->_rating_grade == RatingGrade::RATING_GRADE_SUBJECT_FINAL) {
                $template = "simple-form-add-mark.docx";
            } else {
                $template = "simple-form-mark.docx";
            }
        }
        $form = "";
        if ($final_exam_type == FinalExamType::FINAL_EXAM_TYPE_SECOND) {
            $form = "a";
        } else if ($final_exam_type == FinalExamType::FINAL_EXAM_TYPE_THIRD) {
            $form = "b";
        }
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(Yii::getAlias('@root/templates/' . $template));
        $university = EUniversity::findCurrentUniversity();

        $templateProcessor->setValue('form', $form);
        $templateProcessor->setValue('faculty', htmlspecialchars($group_model->curriculum->department->name));

        $templateProcessor->setValue('university', htmlspecialchars(strtoupper($university->name)));
        $templateProcessor->setValue('semester', htmlspecialchars($subject->semester->name));
        $templateProcessor->setValue('group', htmlspecialchars($group_model->name));
        $templateProcessor->setValue('subject', htmlspecialchars($subject->subject->name));
        if ($subject->_rating_grade == RatingGrade::RATING_GRADE_SUBJECT) {
            $templateProcessor->setValue('lectureTeacher', htmlspecialchars(@$lectureTeacher->employee->fullName));
        } else {
            $templateProcessor->setValue('lectureTeacher', htmlspecialchars(@$examSchedule->employee->fullName));
        }
        $templateProcessor->setValue('controlTeacher', htmlspecialchars(@$examSchedule->employee->fullName));
        $templateProcessor->setValue('practiceTeacher', htmlspecialchars(@$practiceTeacher->employee->fullName));
        $templateProcessor->setValue('laboratoryTeacher', htmlspecialchars(@$laboratoryTeacher->employee->fullName));
        $templateProcessor->setValue('dean', htmlspecialchars(@EEmployeeMeta::getLeaderName(@$group_model->_department, TeacherPositionType::TEACHER_POSITION_TYPE_DEAN)->employee->fullName));
        $templateProcessor->setValue('leader', '');
        $templateProcessor->setValue('department', htmlspecialchars(@EEmployeeMeta::getLeaderName(@$subject->_department, TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT)->employee->fullName));
        $templateProcessor->setValue('credit', htmlspecialchars($subject->credit));
        $templateProcessor->setValue('time', htmlspecialchars($subject->total_acload));
        if ($examSchedule) $templateProcessor->setValue('controldate', Yii::$app->formatter->asDate($examSchedule->exam_date, 'php:d.m.Y'));
        $templateProcessor->cloneRow('student', count($rated_students));
        $j = 0;
        $excellent = 0;
        $fine = 0;
        $satisfactory = 0;
        $unsatisfactory = 0;
        $jb = 0;
        $yb = 0;
        $gb = 0;
        foreach ($rated_students as $key => $val) {
            $j++;
            $templateProcessor->setValue('student#' . $j, htmlspecialchars($val['name']));
            $templateProcessor->setValue('n#' . $j, htmlspecialchars($val['student_id_number']));
            $current = @number_format($ball[$val['id']][ExamType::EXAM_TYPE_CURRENT], 0, ',', '');
            $midterm = @number_format($ball[$val['id']][ExamType::EXAM_TYPE_MIDTERM], 0, ',', '');
            $limit = $current + $midterm;
            $final = @number_format($ball[$val['id']][ExamType::EXAM_TYPE_FINAL], 0, ',', '');
            if ($subject->_rating_grade == RatingGrade::RATING_GRADE_SUBJECT) {
                if ($marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                    $overall = @number_format($ball[$val['id']][ExamType::EXAM_TYPE_OVERALL], 0, ',', '');
                } else {
                    $overall = $current + $midterm + $final;
                }
            } else {
                $overall = @number_format($ball[$val['id']][ExamType::EXAM_TYPE_OVERALL], 0, ',', '');
            }
            $course = @number_format($ball[$val['id']][ExamType::EXAM_TYPE_OVERALL], 0, ',', '');
            $templateProcessor->setValue('i#' . $j, htmlspecialchars($current));
            $templateProcessor->setValue('f#' . $j, htmlspecialchars($midterm));
            $templateProcessor->setValue('j#' . $j, htmlspecialchars($limit));

            $templateProcessor->setValue('l#' . $j, htmlspecialchars($final));
            $templateProcessor->setValue('t#' . $j, htmlspecialchars($overall));
            $templateProcessor->setValue('c#' . $j, htmlspecialchars($course));
            $templateProcessor->setValue('r#' . $j, htmlspecialchars(round(($overall * $subject->total_acload) / 100)));
            if (($current + $midterm) * 2 >= $five->min_border)
                $jb = $five->name;
            elseif (($current + $midterm) * 2 >= $four->min_border)
                $jb = $four->name;
            elseif (($current + $midterm) * 2 >= $three->min_border)
                $jb = $three->name;
            else
                $jb = @$two->name;

            if ($final * 2 >= $five->min_border)
                $yb = $five->name;
            elseif ($final * 2 >= $four->min_border)
                $yb = $four->name;
            elseif ($final * 2 >= $three->min_border)
                $yb = $three->name;
            else
                $yb = @$two->name;


            if ($overall >= $five->min_border) {
                $gb = $five->name;
                $excellent++;
            } elseif ($overall >= $four->min_border) {
                $gb = $four->name;
                $fine++;
            } elseif ($overall >= $three->min_border) {
                $gb = $three->name;
                $satisfactory++;
            } else {
                $gb = @$two->name;
                $unsatisfactory++;
            }
            $templateProcessor->setValue('jb#' . $j, $jb);
            $templateProcessor->setValue('lb#' . $j, $yb);
            $templateProcessor->setValue('tb#' . $j, $gb);

            @$level = EStudentMeta::findOne(['_student' => $val['id'], '_semestr' => $subject->_semester, '_group' => $group_model->id])->level->name;
        }
        $templateProcessor->setValue('level', htmlspecialchars(@$level));
        $templateProcessor->setValue('five', $excellent);
        $templateProcessor->setValue('four', $fine);
        $templateProcessor->setValue('three', $satisfactory);
        $templateProcessor->setValue('two', $j - $excellent - $fine - $satisfactory);
        $templateProcessor->setValue('count', $j);
        $templateProcessor->setValue('dean', htmlspecialchars(""));
        $templateProcessor->setValue('department', htmlspecialchars(""));

        $time = time();
        $file = Yii::getAlias("@runtime/rating_{$time}.docx");
        $templateProcessor->saveAs($file);
        $content = file_get_contents($file);
        unlink($file);
        //$phpWord = \PhpOffice\PhpWord\IOFactory::load($file);
        //\PhpOffice\PhpWord\Settings::setPdfRendererPath(Yii::getAlias("/../vendor/phpoffice/phpword/src/PhpWord/Writer/PDF/DomPDF.php"));
        //  \PhpOffice\PhpWord\Settings::setPdfRendererPath(Yii::getAlias("@root/vendor/mpdf/mpdf"));
        //  \PhpOffice\PhpWord\Settings::setPdfRendererName('MPDF');
//Save it
        // $temp = \PhpOffice\PhpWord\IOFactory::load(Yii::getAlias("@runtime/rating_{$time}.docx"));
        // $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($temp , 'PDF');
        // $xmlWriter->save(Yii::getAlias("@runtime/rating_{$time}.pdf"), TRUE);
        //$pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($temp, 'PDF');
        //$pdfWriter->save( Yii::getAlias("@runtime/rating_{$time}.pdf"), TRUE);
        //$content = file_get_contents(Yii::getAlias("@runtime/rating_{$time}.pdf"));
        //readfile(Yii::getAlias("@runtime/rating_{$time}.docx"));
        //$xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord , 'PDF');
        // $xmlWriter->save('result.pdf');
        return Yii::$app->response->sendContentAsFile($content, $group_model->name . '-' . $subject->subject->name . '.docx');
    }

    public function actionSubjectTopics()
    {
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $department = null;
        $searchModel = new ECurriculumSubject();
        $dataProvider = $searchModel->search($this->getFilterParams());
        if ($this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            /*if ($this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
                $this->addInfo(
                    __('This page is for the department leader only.')
                );
                return $this->goHome();
            }*/
            if (Yii::$app->user->identity->employee->headDepartments) {
                $department = Yii::$app->user->identity->employee->headDepartments->id;
                $dataProvider->query->andFilterWhere(['_department' => $department]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        } else if ($this->_user()->role->code == AdminRole::CODE_TEACHER) {
            $dataProvider->query->andFilterWhere(['_employee' => Yii::$app->user->identity->_employee]);
            $department = Yii::$app->user->identity->employee->teachers->id;
        }

        $dataProvider->query->orderBy(['_semester' => SORT_ASC]);

        if ($code = $this->get('code')) {
            $selectedSubject = ECurriculumSubject::findOne(['id' => $code]);
            $selectedSubject->scenario = ECurriculumSubject::SCENARIO_EMPLOYEE;
            $teachers = EEmployeeMeta::getTeachers($department);
            if ($selectedSubject->load(Yii::$app->request->post())) {
                if ($selectedSubject->save())
                    return $this->redirect(['teacher/subject-topics']);
            }
            return $this->renderAjax('subject-responsible', [
                'selectedSubject' => $selectedSubject,
                'teachers' => $teachers
            ]);
        }

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'department' => $department,
        ]);
    }

    public function actionSubjectTopicInfo($id)
    {
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        if ($this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            if (Yii::$app->user->identity->employee->headDepartments) {
                $department = Yii::$app->user->identity->employee->headDepartments->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
            $employee = "";
            $curriculum_subject = $this->findCurriculumSubjectModel($id, $department, "");
        } else if ($this->_user()->role->code == AdminRole::CODE_TEACHER) {
            if (Yii::$app->user->identity->employee->teachers) {
                $department = Yii::$app->user->identity->employee->teachers->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
            $employee = Yii::$app->user->identity->_employee;
            $curriculum_subject = $this->findCurriculumSubjectModel($id, "", $employee);
        }
        //$curriculum_subject = $this->findCurriculumSubjectModel($id, $department, $employee);

        $searchModel = new ECurriculumSubjectTopic();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_curriculum' => $curriculum_subject->_curriculum]);
        $dataProvider->query->andFilterWhere(['_subject' => $curriculum_subject->_subject]);
        $dataProvider->query->andFilterWhere(['_semester' => $curriculum_subject->_semester]);

        $model = new ECurriculumSubjectTopic();
        $model->scenario = ECurriculumSubjectTopic::SCENARIO_CREATE_DEPARTMENT;
        if ($code = $this->get('code')) {
            if ($model = ECurriculumSubjectTopic::findOne(['id' => $code])) {
                $model->scenario = ECurriculumSubjectTopic::SCENARIO_CREATE_DEPARTMENT;
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Item [{code}] of Curriculum Subject Topic is deleted successfully', ['code' => $model->id]));
                        }
                    } catch (\Exception $e) {
                        if ($e->getCode() == 23503) {
                            $this->addError(__('Could not delete related data'));
                        } else {
                            $this->addError($e->getMessage());
                        }
                    }
                    return $this->redirect(['teacher/subject-topic-info', 'id' => $curriculum_subject->id]);

                }
            } else {
                return $this->redirect(['teacher/subject-topic-info', 'id' => $curriculum_subject->id, 'code' => $model->id]);
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->_subject = $curriculum_subject->_subject;
            $model->_semester = $curriculum_subject->_semester;
            $model->_curriculum = $curriculum_subject->_curriculum;
            $model->_department = $curriculum_subject->_department;
            $training = ECurriculumSubjectDetail::getTrainingTypeByCurriculumSemesterSubject($curriculum_subject->_curriculum, $curriculum_subject->_semester, $curriculum_subject->_subject, $model->_training_type);
            if ($model->isNewRecord) {
                if ($training->academic_load <= ((count($training->trainingTopics) * 2))) {
                    $this->addError(__('The limit for the number of marked topics has been exceeded'));
                    return $this->redirect(['teacher/subject-topic-info', 'id' => $curriculum_subject->id]);
                }
            }

            if ($model->save()) {
                $this->addSuccess(__('Item [{code}] added to Curriculum Subject Topic', ['code' => $model->id]));
                return $this->redirect(['teacher/subject-topic-info', 'id' => $curriculum_subject->id]);
            }

        }

        return $this->render('subject-topic-info', [
            'model' => $model,
            'curriculum_subject' => $curriculum_subject,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionSubjectResources()
    {
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }

        $searchModel = new ESubjectSchedule();
        $dataProvider = $searchModel->search_group($this->getFilterParams());
        $dataProvider->query->select('e_subject_schedule._curriculum, e_subject_schedule._employee, e_subject_schedule._education_year,_subject,e_group._education_lang,_semester,_training_type');
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            $dataProvider->query->andFilterWhere([
                //  'e_subject_schedule._education_year' => EducationYear::getCurrentYear()->code,
                '_employee' => Yii::$app->user->identity->_employee,
                //'_training_type' => TrainingType::TRAINING_TYPE_LECTURE,
            ]);
            if ($searchModel->_education_year == null) {
                $searchModel->_education_year = EducationYear::getCurrentYear()->code;
                $dataProvider->query->andFilterWhere(['e_subject_schedule._education_year' => EducationYear::getCurrentYear()->code]);
            }
            $dataProvider->query->andFilterWhere([
                '<>', '_training_type', TrainingType::TRAINING_TYPE_INDEPENDENT,
            ]);
        }
        $dataProvider->sort->defaultOrder = ['e_subject_schedule._education_year' => SORT_DESC, '_semester' => SORT_ASC, '_subject' => SORT_ASC];
        $dataProvider->query->groupBy(['e_subject_schedule._curriculum', 'e_subject_schedule._employee', 'e_subject_schedule._education_year', '_subject', 'e_group._education_lang', '_semester', '_training_type']);

        //$dataProvider->query->orderBy(['_semester' => SORT_ASC]);
        return $this->render('subject-resource', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionSubjectTopicResource()
    {
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        Url::remember();

        if ($curriculum = $this->get('curriculum')) {
            $curriculum_model = ECurriculum::findOne($curriculum);
            if ($curriculum_model === null) {
                $this->notFoundException();
            }
        }
        $semester = $this->get('semester');
        $education_lang = $this->get('education_lang');
        $training_type = $this->get('training_type');
        if ($subject = $this->get('subject')) {
            $subject = ECurriculumSubject::getByCurriculumSemesterSubject($curriculum_model->id, $semester, $subject);
            if ($subject === null) {
                $this->notFoundException();
            }
        }


        $searchModel = new ECurriculumSubjectTopic();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_curriculum' => $curriculum_model->id]);
        $dataProvider->query->andFilterWhere(['_subject' => $subject->_subject]);
        $dataProvider->query->andFilterWhere(['_semester' => $subject->_semester]);
        $dataProvider->query->andFilterWhere(['_training_type' => $training_type]);

        $dataProviderResources = "";
        $selectedTopic = "";
        if ($code = $this->get('code')) {
            $selectedTopic = ECurriculumSubjectTopic::findOne(['id' => $code]);
            $searchModelResources = new ESubjectResource();
            $dataProviderResources = $searchModelResources->search($this->getFilterParams());
            $dataProviderResources->query->andFilterWhere(['_curriculum' => $curriculum_model->id]);
            $dataProviderResources->query->andFilterWhere(['_subject' => $subject->_subject]);
            $dataProviderResources->query->andFilterWhere(['_semester' => $subject->_semester]);
            $dataProviderResources->query->andFilterWhere(['_training_type' => $training_type]);
            $dataProviderResources->query->andFilterWhere(['_subject_topic' => $code]);
            $dataProviderResources->query->andFilterWhere(['_language' => $education_lang]);
            $dataProviderResources->query->andFilterWhere(['_employee' => Yii::$app->user->identity->_employee]);


            if ($this->get('download')) {
                $files = [];
                /**
                 * @var $resource ESubjectResource
                 */
                foreach ($dataProviderResources->query->all() as $resource) {
                    if (is_array($resource->filename)) {
                        foreach ($resource->filename as $item) {
                            if ($path = $resource->getUploadPath($item, true)) {
                                $files[$item['name']] = $path;
                            }
                        }
                    }
                }
                if (count($files)) {
                    $zipFile = Yii::getAlias("@runtime/{$this->_user()->id}.zip");

                    $zip = new ZipArchive();
                    if ($zip->open($zipFile, ZipArchive::CREATE)) {
                        foreach ($files as $name => $path) {
                            $zip->addFile($path, $name);
                        }
                    }

                    $zip->close();
                    $content = file_get_contents($zipFile);
                    unlink($zipFile);
                    $name = Translator::getInstance()->translateToSlug($selectedTopic->name) . '.zip';
                    return Yii::$app->response->sendContentAsFile($content, $name);
                }
            }else{
                return $this->renderAjax('subject-topic-resource-list', [
                    'subject' => $subject,
                    'dataProviderResources' => $dataProviderResources,
                    'selectedTopic' => $selectedTopic
                ]);
            }


        }
        $groups = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($curriculum_model->id, $subject->_semester, $subject->_subject, $training_type, $education_lang, Yii::$app->user->identity->_employee);
        $group_labels = "";
        foreach ($groups as $group) {
            $group_labels .= $group->group->name . ', ';
        }
        $group_labels = substr($group_labels, 0, -2);

        return $this->render('subject-topic-resource', [
            'subject' => $subject,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'education_lang' => $education_lang,
            'group_labels' => $group_labels,
            'training_type' => $training_type,
        ]);
    }

    public function actionSubjectTopicTest()
    {
        return $this->actionSubjectTopicResourceEdit(true);
    }

    public function actionTestExport()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goBack();
        }

        $education_lang = "";
        if ($l = $this->get('education_lang', false)) {
            $education_lang = $l;
        }

        if ($t = $this->get('code', false)) {
            /**
             * @var $topic_model ECurriculumSubjectTopic
             */
            $topic_model = ECurriculumSubjectTopic::findOne($t);
            if ($topic_model === null) {
                $this->notFoundException();
            }
        }

        $subject = ECurriculumSubject::getByCurriculumSemesterSubject($topic_model->_curriculum, $topic_model->_semester, $topic_model->_subject);

        $subjectTopicResource = ESubjectResource::getTopicTestResource($topic_model, $this->_user()->_employee, $education_lang);
        $contents = array_map(function ($q) {
            return $q->content;
        }, $subjectTopicResource->testQuestions);
        $filename = $subject->subject->name . '-' . $topic_model->name;
        $this->exportQuestions($contents, $filename);
        return;
    }

    public function actionTestImport()
    {
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }

        $model = new FormImportQuestion();
        $education_lang = "";
        if ($l = $this->get('education_lang', false)) {
            $education_lang = $l;
        }

        if ($t = $this->get('code', false)) {
            /**
             * @var $topic_model ECurriculumSubjectTopic
             */
            $topic_model = ECurriculumSubjectTopic::findOne($t);
            if ($topic_model === null) {
                $this->notFoundException();
            }
            $model->topic_id = $topic_model->id;
            $model->subject_id = $topic_model->_subject;
        }

        $subject = ECurriculumSubject::getByCurriculumSemesterSubject($topic_model->_curriculum, $topic_model->_semester, $topic_model->_subject);

        $subjectTopicResource = ESubjectResource::getTopicTestResource($topic_model, $this->_user()->_employee, $education_lang);

        $prev_url = ['teacher/subject-topic-resource',
            'subject' => $subject->_subject,
            'curriculum' => $topic_model->_curriculum,
            'semester' => $topic_model->_semester,
            'training_type' => TrainingType::TRAINING_TYPE_LECTURE,
            'education_lang' => $education_lang,
        ];
        $key = 'TEST_QUESTIONS_' . $subjectTopicResource->id;


        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->session->set($key, $model->content);

            if ($this->post('import', false)) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $count = 0;
                    foreach ($model->normalizedContent() as $item) {
                        $question = new ESubjectResourceQuestion(
                            [
                                'scenario' => ESubjectResourceQuestion::SCENARIO_CREATE,
                                'content' => $item['content'],
                                '_subject' => $topic_model->_subject,
                                '_subject_topic' => $topic_model->id,
                                '_language' => $education_lang,
                                '_curriculum' => $topic_model->_curriculum,
                                '_training_type' => $topic_model->_training_type,
                                '_education_year' => $topic_model->semester->educationYear->code,
                                '_semester' => $topic_model->_semester,
                                '_employee' => $this->_user()->_employee,
                                '_subject_resource' => $subjectTopicResource->id,
                            ]
                        );
                        if ($question->save()) {
                            $count++;
                        }
                    }
                    $subjectTopicResource->updateQuestionsCount();
                    $transaction->commit();
                    $this->addSuccess(__('{count} questions imported', ['count' => $count]));
                    Yii::$app->session->offsetUnset($key);
                    return $this->redirect(['teacher/subject-topic-test', 'education_lang' => $education_lang, 'code' => $topic_model->id]);
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    $this->addError($e->getMessage());
                }
                return $this->refresh();
            }
        } else {
            if ($this->isGet()) {
                if ($content = Yii::$app->session->get($key)) {
                    $model->content = $content;
                }
            }
        }

        $group_labels = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($topic_model->_curriculum, $topic_model->_semester, $topic_model->_subject, $topic_model->_training_type, $education_lang, Yii::$app->user->identity->_employee, true);


        return $this->render(
            'test-import',
            [
                'subject' => $subject,
                'model' => $model,
                'topic_model' => $topic_model,
                'education_lang' => $education_lang,
                'subjectTopicResource' => $subjectTopicResource,
                'group_labels' => $group_labels,
                'prev_url' => $prev_url,
            ]
        );
    }


    public function actionSubjectTopicResourceEdit($isTest = false)
    {
        /**
         * @var  $topic_model ECurriculumSubjectTopic
         * @var  $model ESubjectResource
         */
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $education_lang = $this->get('education_lang');
        if ($code = $this->get('code')) {

            $topic_model = ECurriculumSubjectTopic::findOne($code);
            if ($topic_model === null) {
                $this->notFoundException();
            }
        }

        $subject = ECurriculumSubject::getByCurriculumSemesterSubject($topic_model->_curriculum, $topic_model->_semester, $topic_model->_subject);
        if ($subject === null) {
            $this->notFoundException();
        }

        $prev_url = ['teacher/subject-topic-resource',
            'curriculum' => $topic_model->_curriculum,
            'semester' => $topic_model->_semester,
            'subject' => $topic_model->_subject,
            'training_type' => $topic_model->_training_type,
            'education_lang' => $education_lang,
        ];

        $educationYear = Semester::getByCurriculumSemester($topic_model->_curriculum, $topic_model->_semester);

        if ($isTest) {
            $model = ESubjectResource::getTopicTestResource($topic_model, $this->_user()->_employee, $education_lang);
        } else {
            if ($id = $this->get('id')) {
                if (!($model = ESubjectResource::findOne(['id' => $id]))) {
                    return $this->redirect($prev_url);
                } else {
                    if ($model->resource_type == SubjectResource::RESOURCE_TYPE_TEST) {
                        return $this->redirect(['subject-topic-test', 'code' => $topic_model->id, 'education_lang' => $education_lang]);
                    }
                }
            }
        }

        if (!isset($model)) {
            $model = new ESubjectResource();
        } else {
            if ($model->_employee != $this->_user()->_employee) {
                return $this->redirect($prev_url);
            }
        }
        $model->scenario = $isTest ? ESubjectResource::SCENARIO_TEST : ESubjectResource::SCENARIO_RESOURCE;

        if ($model->isNewRecord) {
            $model->resource_type = $isTest ? ESubjectResource::RESOURCE_TYPE_TEST : ESubjectResource::RESOURCE_TYPE_RESOURCE;
            $model->_curriculum = $topic_model->_curriculum;
            $model->_subject = $topic_model->_subject;
            $model->_language = $education_lang;
            $model->_training_type = $topic_model->_training_type;
            $model->_subject_topic = $topic_model->id;
            $model->_education_year = $educationYear->educationYear->code;
            $model->_semester = $topic_model->_semester;
            $model->_employee = $this->_user()->_employee;
            if ($isTest && $model->isNewRecord) {

                $model->test_random = true;
                $model->name = __('Test');
                $model->comment = __('{name}dan test savollari', ['name' => $topic_model->name]);
            }
        }


        if ($this->get('download')) {
            if (is_array($model->filename)) {
                if (count($model->filename) > 1) {
                    $zipFile = Yii::getAlias("@runtime/{$this->_user()->id}.zip");

                    $zip = new ZipArchive();
                    if ($zip->open($zipFile, ZipArchive::CREATE)) {
                        foreach ($model->filename as $file) {
                            if ($path = $model->getUploadPath($file, true)) {
                                $zip->addFile($path, $file['name']);
                            }
                        }
                    }

                    $zip->close();
                    $content = file_get_contents($zipFile);
                    unlink($zipFile);
                    $name = Translator::getInstance()->translateToSlug($model->name) . '.zip';
                    return Yii::$app->response->sendContentAsFile($content, $name);
                } else {
                    if ($path = $model->getUploadPath($model->filename[0], true)) {
                        return Yii::$app->response->sendFile($path, $model->filename[0]['name']);
                    }
                }
            }
        }

        if ($this->get('delete')) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(__('Item [{code}] of subject resource is deleted successfully', ['code' => $model->name]));
                }
            } catch (\Exception $e) {
                $this->addError($e->getMessage());
            }
            return $this->redirect($prev_url);
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $groups = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($topic_model->_curriculum, $topic_model->_semester, $topic_model->_subject, $topic_model->_training_type, $education_lang, Yii::$app->user->identity->_employee);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            foreach ($groups as $group) {
                $students = EStudentSubject::getStudentsByYearSemesterGroup($model->_curriculum, $model->_education_year, $model->_semester, $model->_subject, $group->_group);


                foreach ($students as $st) {
                    if ($st->_group == $group->_group) {
                        $student = ESubjectTaskStudent::findOne([
                            '_student' => $st->_student,
                            '_subject_resource' => $model->id,
                            '_curriculum' => $model->_curriculum,
                            '_subject' => $model->_subject,
                        ]);
                        if ($student === null) {
                            $student = new ESubjectTaskStudent();
                        }

                        $student->scenario = ESubjectTaskStudent::SCENARIO_CREATE;
                        $student->_subject_resource = $model->id;
                        $student->_subject = $model->_subject;
                        $student->_curriculum = $model->_curriculum;
                        $student->_education_year = $model->_education_year;
                        $student->_training_type = $model->_training_type;
                        $student->_semester = $model->_semester;
                        $student->_employee = $model->_employee;
                        $student->_student = $st->_student;
                        $student->_group = $st->_group;
                        $student->active = ESubjectTaskStudent::STATUS_ENABLE;
                        $student->_task_type = ESubjectTask::TASK_TYPE_TEST;

                        $student->save(false);
                    }
                }
            }

            if (!$model->isNewRecord) {
                $this->addSuccess(__('Subject Resource [{code}] updated successfully', ['code' => $model->name]));
            } else {
                $this->addSuccess(__('Subject Resource [{code}] created successfully', ['code' => $model->name]));
            }
            if ($isTest) {
                return $this->redirect(['subject-topic-test', 'education_lang' => $education_lang, 'code' => $topic_model->id]);
            } else {
                return $this->redirect(['subject-topic-resource-edit', 'education_lang' => $education_lang, 'code' => $topic_model->id, 'id' => $model->id]);
            }
        }

        $group_labels = implode(', ', ArrayHelper::getColumn($groups, function ($group) {
            return $group->group->name;
        }));

        return $this->renderView([
            'subject' => $subject,
            'model' => $model,
            'topic_model' => $topic_model,
            'prev_url' => $prev_url,
            'education_lang' => $education_lang,
            'group_labels' => $group_labels,
        ]);
    }

    public function actionSubjectTasks()
    {
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        Url::remember();
        $searchModel = new ESubjectSchedule();
        $dataProvider = $searchModel->search_group($this->getFilterParams());
        $dataProvider->query->select('e_subject_schedule._curriculum,e_subject_schedule._employee, e_subject_schedule._education_year,_subject, e_group._education_lang,_semester,_training_type');
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            $dataProvider->query->andFilterWhere([
                //  'e_subject_schedule._education_year' => EducationYear::getCurrentYear()->code,
                '_employee' => Yii::$app->user->identity->_employee,
            ]);
            if ($searchModel->_education_year == null) {
                $searchModel->_education_year = EducationYear::getCurrentYear()->code;
                $dataProvider->query->andFilterWhere(['e_subject_schedule._education_year' => EducationYear::getCurrentYear()->code]);
            }
            $dataProvider->query->andFilterWhere([
                'in', '_training_type', [
                    TrainingType::TRAINING_TYPE_LECTURE,
                    TrainingType::TRAINING_TYPE_LABORATORY,
                    TrainingType::TRAINING_TYPE_PRACTICE,
                    TrainingType::TRAINING_TYPE_SEMINAR,
                    TrainingType::TRAINING_TYPE_TRAINING,
                ],
            ]);
        }
        $dataProvider->sort->defaultOrder = ['e_subject_schedule._education_year' => SORT_DESC, '_semester' => SORT_ASC, '_subject' => SORT_ASC];
        $dataProvider->query->groupBy(['e_subject_schedule._curriculum', 'e_subject_schedule._employee', 'e_subject_schedule._education_year', '_subject', 'e_group._education_lang', '_semester', '_training_type']);

        //$dataProvider->query->orderBy(['_semester' => SORT_ASC]);
        return $this->render('subject-task', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionSubjectTaskList()
    {
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        if ($curriculum = $this->get('curriculum')) {
            $curriculum_model = ECurriculum::findOne($curriculum);
            if ($curriculum_model === null) {
                $this->notFoundException();
            }
        }
        $semester = $this->get('semester');
        $education_lang = $this->get('education_lang');
        $training_type = $this->get('training_type');

        if ($subject = $this->get('subject')) {
            $subject = ECurriculumSubject::getByCurriculumSemesterSubject($curriculum_model->id, $semester, $subject);
            if ($subject === null) {
                $this->notFoundException();
            }
        }

        //$prev_url = Url::previous();
        $prev_url = null;
        $prev_url = $thisUrl = ['teacher/subject-task-list',
            'curriculum' => $curriculum_model->id,
            'semester' => $subject->_semester,
            'subject' => $subject->_subject,
            'training_type' => $training_type,
            'education_lang' => $education_lang,
        ];
        $groups = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($curriculum_model->id, $subject->_semester, $subject->_subject, $training_type, $education_lang, Yii::$app->user->identity->_employee);

        $group_labels = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($curriculum_model->id, $subject->_semester, $subject->_subject, $training_type, $education_lang, Yii::$app->user->identity->_employee, true);

        /**
         * @var $model ESubjectTask
         */
        $model = new ESubjectTask();
        $model->scenario = ESubjectTask::SCENARIO_CREATE;
        $examTypes = ECurriculumSubjectExamType::getExamTypeByCurriculumSemesterSubjectTraining($curriculum_model->id, $subject->_semester, $subject->_subject, $training_type);
        $subject_topics = ECurriculumSubjectTopic::getTopicByCurriculumSemesterSubjectTraining($curriculum_model->id, $subject->_semester, $subject->_subject, $training_type);
        $ball_summary = 0;
        $exams = 0;
        foreach ($examTypes as $exam) {
            $ball_summary += $exam->max_ball;
            $exams++;
        }
        if ($curriculum_model->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
            $ball_summary = $exams > 0 ? round($ball_summary / $exams) : '';
        }
        if ($attribute = $this->get('attribute')) {
            if ($model = ESubjectTask::findOne(['id' => $this->get('id')])) {
                $model->$attribute = !$model->$attribute;
                $noError = true;
                @$last_week_date = ECurriculumWeek::getLastWeekByCurriculumSemester($model->_curriculum, $model->_semester);
                if (@$last_week_date->end_date->getTimestamp() <= time()) {
                    $this->addError(__('The theoretical semester weeks have expired'));
                    return $this->redirect(Yii::$app->request->referrer);
                    $noError = false;
                }
                if (ESubjectTaskStudent::getExistStudentsByTaskCurriculumSubject($model->id, $model->_curriculum, $model->_subject) > 0) {

                    if ($model->active === ESubjectTask::STATUS_ENABLE) {
                        if ($model->getActiveTestQuestions()->count() < $model->question_count) {
                            $this->addError(__('Kiritilgan test savollari soni belgilangan sondan kam'));
                            return $this->redirect(Yii::$app->request->referrer);
                            $noError = false;
                        }

                        $limit_ball = ESubjectTask::getLimitBallByCurriculumSubjectDetail($curriculum_model->id, $subject->_subject, $education_lang, $training_type, $subject->_semester, Yii::$app->user->identity->_employee, $model->_exam_type);
                        $exam_type_limit = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($curriculum_model->id, $subject->_semester, $subject->_subject, $model->_exam_type);
                        if ($model->curriculum->_marking_system != MarkingSystem::MARKING_SYSTEM_FIVE) {
                            if (@$exam_type_limit->max_ball < (@$limit_ball->max_ball + @$model->max_ball)) {
                                $this->addError(__('Maksimal ballar yig\'indisi fanga ajratilgan ball limitidan oshib ketdi'));
                                return $this->redirect(Yii::$app->request->referrer);
                                $noError = false;
                            }
                        }
                    }
                    if ($noError && $model->save()) {
                        if ($model->id) {
                            if ($model->active === ESubjectTask::STATUS_ENABLE) {


                                ESubjectTaskStudent::updateAll(
                                    [
                                        'active' => ESubjectTaskStudent::STATUS_ENABLE,
                                    ],
                                    [
                                        '_subject_task' => $model->id,
                                    ]
                                );
                            } else if ($model->active === ESubjectTask::STATUS_DISABLE) {
                                ESubjectTaskStudent::updateAll(
                                    [
                                        'active' => ESubjectTaskStudent::STATUS_DISABLE,
                                    ],
                                    [
                                        '_subject_task' => $model->id,
                                    ]
                                );
                            }
                            $this->addSuccess(__('Item [{id}] of [{task}] is enabled', ['id' => $model->id]), true, false);
                        } else {

                            $this->addSuccess(__('Item [{id}] of [{task}] is disabled', ['id' => $model->id]), true, false);
                        }
                        return $this->redirect(Yii::$app->request->referrer);
                        //Yii::$app->response->format = Response::FORMAT_JSON;
                        // return [];
                    }
                } else {
                    $this->addError(__('This task has not yet been given to students'));
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
        }

        if ($code = $this->get('code')) {
            if ($model = ESubjectTask::findOne(['id' => $code])) {

                if ($this->get('download')) {
                    if (is_array($model->filename)) {
                        if (count($model->filename) > 1) {
                            $zipFile = Yii::getAlias("@runtime/{$this->_user()->id}.zip");

                            $zip = new ZipArchive();
                            if ($zip->open($zipFile, ZipArchive::CREATE)) {
                                foreach ($model->filename as $file) {
                                    if ($path = $model->getUploadPath($file, true)) {
                                        $zip->addFile($path, $file['name']);
                                    }
                                }
                            }

                            $zip->close();
                            $content = file_get_contents($zipFile);
                            unlink($zipFile);
                            $name = Translator::getInstance()->translateToSlug($model->name) . '.zip';
                            return Yii::$app->response->sendContentAsFile($content, $name);
                        } else {
                            if ($path = $model->getUploadPath($model->filename[0], true)) {
                                return Yii::$app->response->sendFile($path, $model->filename[0]['name']);
                            }
                        }
                    }

                    return $this->redirect(currentTo(['download' => null]));
                }

                $model->scenario = ESubjectTask::SCENARIO_CREATE;
                $thisUrl['code'] = $model->id;
                if ($this->get('questions')) {
                    if ($this->get('import')) {
                        return $this->testImport($model);
                    }

                    if ($this->get('export')) {
                        $questions = $model->testQuestions;
                        $contents = array_map(function ($q) {
                            return $q->content;
                        }, $questions);
                        $filename = str_replace('/', '-', $subject->subject->name . '_' . $model->name);
                        $this->exportQuestions($contents, $filename);
                        return;
                    }

                    return $this->render('subject-task-list-test', [
                        'model' => $model,
                        'subject' => $subject,
                        'group_labels' => $group_labels,
                    ]);
                }

                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Item [{code}] of subject task is deleted successfully', ['code' => $model->id]));
                            //  return $this->refresh();
                        }
                    } catch (\Exception $e) {
                        if ($e->getCode() == 23503) {
                            $this->addError(__('Could not delete related data'));
                        } else {
                            $this->addError($e->getMessage());
                        }


                    }
                    return $this->redirect(Yii::$app->request->referrer);
                    //return $this->redirect($prev_url);
                }
                if ($this->get('active') || $this->get('newactive')) {
                    $prev_url = $thisUrl = ['teacher/subject-task-list',
                        'curriculum' => $curriculum_model->id,
                        'semester' => $subject->_semester,
                        'subject' => $subject->_subject,
                        'training_type' => $training_type,
                        'education_lang' => $education_lang,
                        'code' => $model->id,
                        'edit' => 1,
                    ];
                    try {

                        $searchTaskModel = "";
                        $taskDataProvider = "";
                        $minimum_procent = $model->curriculum->markingSystem->minimum_limit;

                        $min_border = $minimum_procent * $model->max_ball / 100;

                        $searchTaskModel = new ESubjectTaskStudent();
                        $taskDataProvider = $searchTaskModel->search_by_task($this->getFilterParams());
                        $taskDataProvider->query->andFilterWhere(['_employee' => Yii::$app->user->identity->_employee]);
                        $taskDataProvider->query->andFilterWhere(['e_subject_task_student._curriculum' => $model->_curriculum]);
                        $taskDataProvider->query->andFilterWhere(['_subject' => $model->_subject]);
                        $taskDataProvider->query->andFilterWhere(['_semester' => $model->_semester]);
                        $taskDataProvider->query->andFilterWhere(['_subject_task' => $model->id]);
                        $taskDataProvider->query->andFilterWhere(['final_active' => 1]);
                        $taskDataProvider->query->andFilterWhere(['e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
                        $gr_ids = array();
                        foreach ($groups as $group) {
                            $gr_ids[$group->_group] = $group->_group;
                        }
                        $final_exam_types = FinalExamType::getFinalExamTypeOptions($curriculum_model->markingSystem->count_final_exams);
                        if (Yii::$app->request->isAjax) {
                            return $this->render('subject-task-student-list', [
                                'model' => $model,
                                'gr_ids' => $gr_ids,
                                //'students' => $students,
                                // 'searchTaskModel' => $searchTaskModel,
                                'taskDataProvider' => $taskDataProvider,
                                'final_exam_types' => $final_exam_types,
                                'subject' => $subject,
                                'training_type' => $training_type,
                                'education_lang' => $education_lang,
                                'group_labels' => $group_labels,
                                'min_border' => $min_border,
                                'prev_url' => $prev_url,
                                //'selectedTopic' => $selectedTopic
                            ]);
                        } else {
                            // return $this->redirect($prev_url);
                            return $this->render('subject-task-student-list', [
                                'model' => $model,
                                'gr_ids' => $gr_ids,
                                //'students' => $students,
                                // 'searchTaskModel' => $searchTaskModel,
                                'taskDataProvider' => $taskDataProvider,
                                'final_exam_types' => $final_exam_types,
                                'subject' => $subject,
                                'training_type' => $training_type,
                                'education_lang' => $education_lang,
                                'group_labels' => $group_labels,
                                'min_border' => $min_border,
                                'prev_url' => $prev_url,

                                //'selectedTopic' => $selectedTopic
                            ]);
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect($prev_url);
                }
            } else {
                return $this->redirect($prev_url);
            }
        }


        $model->_curriculum = $curriculum_model->id;
        $model->_subject = $subject->_subject;
        $model->_language = $education_lang;
        $model->_training_type = $training_type;
        $model->_education_year = Semester::getByCurriculumSemester($curriculum_model->id, $subject->_semester)->educationYear->code;
        $model->_semester = $subject->_semester;
        $model->_employee = Yii::$app->user->identity->_employee;
        $model->_marking_category = $subject->curriculum->_marking_system;
        if ($model->isNewRecord)
            $model->active = ESubjectTask::STATUS_DISABLE;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);

        }
        $old_ball = $model->max_ball;
        if ($model->load(Yii::$app->request->post())) {
            $exam_type_limit = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($curriculum_model->id, $subject->_semester, $subject->_subject, $model->_exam_type);
            $limit_ball = ESubjectTask::getLimitBallByCurriculumSubjectDetail($curriculum_model->id, $subject->_subject, $education_lang, $training_type, $subject->_semester, Yii::$app->user->identity->_employee, $model->_exam_type);
            $noError = true;
            //   echo @$exam_type_limit->max_ball;
            // echo @$limit_ball->max_ball;
            if ($model->curriculum->_marking_system != MarkingSystem::MARKING_SYSTEM_FIVE) {
                if ($model->isNewRecord) {
                    if (@$exam_type_limit->max_ball < (@$limit_ball->max_ball)) {
                        $this->addError(__('Maksimal ballar yig\'indisi fanga ajratilgan ball limitidan oshib ketdi'));
                        //return $this->redirect(['teacher/subject-task-list', 'id' => $curriculum_subject->id]);
                        $noError = false;
                        //  return $this->redirect(Yii::$app->request->referrer);
                    }
                } else {
                    if ($exam_type_limit->max_ball < ($limit_ball->max_ball - $old_ball + $model->max_ball)) {
                        $this->addError(__('Maksimal ballar yig\'indisi fanga ajratilgan ball limitidan oshib ketdi'));

                        //return $this->redirect(['teacher/subject-task-list', 'id' => $curriculum_subject->id]);
                        $noError = false;
                        // return $this->redirect(Yii::$app->request->referrer);
                    }
                }
            }
            if ($noError && $model->save()) {
                //$thisUrl['code'] = $model->id;
                if ($code) {
                    $this->addSuccess(__('Subject Task [{code}] updated successfully', ['code' => $model->id]));
                } else {
                    $this->addSuccess(__('Subject Task [{code}] created successfully', ['code' => $model->id]));
                }
                return $this->redirect($prev_url);
            }
        }

        /*
                if ($model->load(Yii::$app->request->post()) && $model->save()) {
                    $thisUrl['code'] = $model->id;

                    if ($code) {
                        $this->addSuccess(__('Subject Task [{code}] updated successfully', ['code' => $model->id]));
                    } else {
                        $this->addSuccess(__('Subject Task [{code}] created successfully', ['code' => $model->id]));
                    }
                    return $this->redirect($thisUrl);
                }*/


        $searchModel = new ESubjectTask();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_curriculum' => $curriculum_model->id]);
        $dataProvider->query->andFilterWhere(['_subject' => $subject->_subject]);
        $dataProvider->query->andFilterWhere(['_semester' => $subject->_semester]);
        $dataProvider->query->andFilterWhere(['_training_type' => $training_type]);
        $dataProvider->query->andFilterWhere(['_language' => $education_lang]);
        $dataProvider->query->andFilterWhere(['_employee' => Yii::$app->user->identity->_employee]);
        if ($this->get('edit')) {
            //$thisUrl['edit'] = 1;
            return $this->render('subject-task-edit', [
                'subject' => $subject,

                'model' => $model,
                'subject_topics' => $subject_topics,
                'training_type' => $training_type,
                'education_lang' => $education_lang,
                'group_labels' => $group_labels,
                'examTypes' => $examTypes,
                'groups' => $groups,
                'prev_url' => $prev_url,
            ]);
        } else {
            return $this->render('subject-task-list', [
                'subject' => $subject,
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'model' => $model,
                'subject_topics' => $subject_topics,
                'training_type' => $training_type,
                'education_lang' => $education_lang,
                'group_labels' => $group_labels,
                'examTypes' => $examTypes,
                'groups' => $groups,
                'prev_url' => $prev_url,
                'ball_summary' => $ball_summary,

            ]);
        }
    }

    protected function testImport(ESubjectTask $task)
    {
        $model = new FormImportQuestion();
        $model->subject_id = $task->subject->id;
        $model->task_id = $task->id;

        $subject = ECurriculumSubject::getByCurriculumSemesterSubject($task->_curriculum, $task->_semester, $task->_subject);

        $key = 'TEST_QUESTIONS_SUBJECT_TASK_' . $task->id;


        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->session->set($key, $model->content);

            if ($this->post('import', false)) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $count = 0;
                    foreach ($model->normalizedContent() as $item) {
                        $question = new ESubjectResourceQuestion(
                            [
                                'scenario' => ESubjectResourceQuestion::SCENARIO_CREATE,
                                'content' => $item['content'],
                                '_subject' => $subject->subject->id,
                                '_language' => $task->_language,
                                '_curriculum' => $task->_curriculum,
                                '_training_type' => $task->_training_type,
                                '_education_year' => $subject->semester->educationYear->code,
                                '_semester' => $task->_semester,
                                '_employee' => $this->_user()->_employee,
                                '_subject_task' => $task->id,
                            ]
                        );
                        if ($question->save()) {
                            $count++;
                        }
                    }
                    if ($count) {
                        $transaction->commit();
                        $this->addSuccess(__('{count} questions imported', ['count' => $count]));
                        Yii::$app->session->offsetUnset($key);
                        $url = $url1 = ['teacher/subject-task-list',
                            'curriculum' => $task->_curriculum,
                            'semester' => $task->_semester,
                            'subject' => $task->_subject,
                            'training_type' => $task->_training_type,
                            'education_lang' => $task->_language,
                            'code' => $task->id,
                            'questions' => 1,
                        ];

                        return $this->redirect($url);
                    } else {
                        if (isset($question)) {
                            if ($error = $question->getOneError()) {
                                $this->addError($error);
                            }
                        }
                    }
                    $transaction->rollBack();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    $this->addError($e->getMessage());
                }
                return $this->refresh();
            }
        } else {
            if ($this->isGet()) {
                if ($content = Yii::$app->session->get($key)) {
                    $model->content = $content;
                }
            }
        }

        $group_labels = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($task->_curriculum, $task->_semester, $task->_subject, $task->_training_type, $task->_language, Yii::$app->user->identity->_employee, true);

        return $this->render(
            'subject-task-list-test-import',
            [
                'subject' => $subject,
                'model' => $model,
                'task' => $task,
                'group_labels' => $group_labels,
            ]
        );
    }

    public function actionSubjectTaskStatus()
    {
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        //Url::remember();
        $prev_url = "";
        $searchModel = new ESubjectTaskStudent();
        $dataProvider = $searchModel->search_by_task($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_employee' => Yii::$app->user->identity->_employee]);
        $dataProvider->query->andFilterWhere(['e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);

        if ($subject_task = $this->get('subject_task')) {
            $subject_task = ESubjectTask::findOne($subject_task);
            if ($subject_task === null) {
                $this->notFoundException();
            }
            $dataProvider->query->andFilterWhere(['e_subject_task_student._curriculum' => $subject_task->_curriculum]);
            $dataProvider->query->andFilterWhere(['_subject' => $subject_task->_subject]);
            $dataProvider->query->andFilterWhere(['_semester' => $subject_task->_semester]);
            $dataProvider->query->andFilterWhere(['_subject_task' => $subject_task->id]);

            $prev_url = $thisUrl = ['teacher/subject-task-list',
                'curriculum' => $subject_task->_curriculum,
                'semester' => $subject_task->_semester,
                'subject' => $subject_task->_subject,
                'training_type' => $subject_task->_training_type,
                'education_lang' => $subject_task->_language
            ];
            //$groups = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($subject_task->_curriculum, $subject_task->_semester, $subject_task->_subject, $subject_task->_training_type, $subject_task->_language, Yii::$app->user->identity->_employee);

            $group_labels = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($subject_task->_curriculum, $subject_task->_semester, $subject_task->_subject, $subject_task->_training_type, $subject_task->_language, Yii::$app->user->identity->_employee, true);

        }
        $groups = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($subject_task->_curriculum, $subject_task->_semester, $subject_task->_subject, $subject_task->_training_type, $subject_task->_language, Yii::$app->user->identity->_employee);

        if ($group = $this->get('group')) {
            $group = EGroup::findOne($group);
            if ($group === null) {
                $this->notFoundException();
            }
            $dataProvider->query->andFilterWhere(['_curriculum' => $group->_curriculum]);
        }
        $semester = $this->get('semester');
        if ($subject = $this->get('subject')) {
            $subject = ECurriculumSubject::getByCurriculumSemesterSubject($group->_curriculum, $semester, $subject);
            if ($subject === null) {
                $this->notFoundException();
            }
            $dataProvider->query->andFilterWhere(['_curriculum' => $group->_curriculum]);
        }

        //$dataProvider->query->orderBy(['_semester' => SORT_ASC]);
        return $this->render('subject-task-status', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'subject_task' => $subject_task,
            'groups' => $groups,
            'prev_url' => $prev_url,
            'group_labels' => $group_labels,
        ]);
    }

    public function actionAnswerList()
    {
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        //Url::remember();
        $prev_url = "";

        if ($subject_task = $this->get('subject_task')) {
            $taskModel = ESubjectTask::findOne($subject_task);
            if ($taskModel === null) {
                $this->notFoundException();
            }

            $student = $this->get('student');

            $subject_task = ESubjectTaskStudent::findOne(['_subject_task' => $taskModel->id, '_student' => $student, '_employee' => Yii::$app->user->identity->_employee, 'final_active' => 1]);
            if ($subject_task === null) {
                $this->notFoundException();
            }
            $searchModel = new EStudentTaskActivity();
            $dataProvider = $searchModel->search($this->getFilterParams());
            $dataProvider->query->andFilterWhere(['_curriculum' => $subject_task->_curriculum]);
            $dataProvider->query->andFilterWhere(['_subject' => $subject_task->_subject]);
            $dataProvider->query->andFilterWhere(['_semester' => $subject_task->_semester]);
            $dataProvider->query->andFilterWhere(['_subject_task' => $subject_task->_subject_task]);
            $dataProvider->query->andFilterWhere(['_student' => $subject_task->_student]);

            $prev_url = $thisUrl = ['teacher/subject-task-list',
                'curriculum' => $taskModel->_curriculum,
                'semester' => $taskModel->_semester,
                'subject' => $taskModel->_subject,
                'training_type' => $taskModel->_training_type,
                'education_lang' => $taskModel->_language
            ];

            $group_labels = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($taskModel->_curriculum, $taskModel->_semester, $taskModel->_subject, $taskModel->_training_type, $taskModel->_language, Yii::$app->user->identity->_employee, true);

        }
        if ($code = $this->get('code')) {
            if ($task = EStudentTaskActivity::findOne(['id' => $code])) {
                $task_meta = ESubjectTaskStudent::findOne(['_subject_task' => $task->_subject_task, '_curriculum' => $task->_curriculum, '_student' => $task->_student, 'final_active' => 1]);
                //if ($this->get('mark')) {
                $task->scenario = EStudentTaskActivity::SCENARIO_CREATE_FOR_TEACHER;
                //}
            } else {
                return $this->redirect(['teacher/answer-list', 'subject_task' => $task->_subject_task, 'student' => $task->_student, 'code' => $task->id]);
            }

            if ($task->load(Yii::$app->request->post())) {
                $task->_employee = Yii::$app->user->identity->_employee;
                $task->active = EStudentTaskActivity::STATUS_ENABLE;
                $task->marked_date = date('Y-m-d H:i:s', time());
                @$old_marked = EStudentTaskActivity::getMarkBySubjectTaskStudent($task->_subject_task, $task->_student);

                if ($task->save()) {
                    $task_meta->_task_status = ESubjectTaskStudent::TASK_STATUS_RATED;
                    $task_meta->save(false);
                    if ($old_marked != null && $old_marked->id != $task->id) {
                        $old_marked->active = EStudentTaskActivity::STATUS_DISABLE;
                        $old_marked->save(false);
                    }
                    $this->addSuccess(__('Mark [{code}] added to Task for this Student', ['code' => $task->_subject_task]));
                    return $this->redirect(['teacher/answer-list', 'subject_task' => $task->_subject_task, 'student' => $task->_student, 'code' => $task->id]);
                }

            }
        }

        if ($attribute = $this->get('attribute')) {
            if ($model = EStudentTaskActivity::findOne(['id' => $this->get('id')])) {

                $model->$attribute = !$model->$attribute;
                if ($model->mark > 0) {
                    if ($model->save()) {
                        $condition = ['and',
                            ['=', '_subject_task', $model->_subject_task],
                            ['=', '_student', $model->_student],
                            ['<>', 'id', $model->id],
                            ['>', 'mark', 0],
                            ['not', ['mark' => null]],
                        ];
                        EStudentTaskActivity::updateAll([
                            'active' => ESubjectTaskStudent::STATUS_DISABLE,
                        ], $condition);

                        if ($model->id) {
                            $this->addSuccess(__('Item [{id}] of [{answer}] is enabled', ['id' => $model->id, 'answer' => $model->_subject_task]), true, true);
                        } else {
                            $this->addSuccess(__('Item [{id}] of [{answer}] is disabled', ['id' => $model->id, 'answer' => $model->_subject_task]), true, true);
                        }
                        //Yii::$app->response->format = Response::FORMAT_JSON;
                        //return [];
                        return $this->redirect(Yii::$app->request->referrer);

                    }

                } else {
                    $this->addError(__('The answer is not marked'));
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
        }
        //$dataProvider->query->orderBy(['_semester' => SORT_ASC]);
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'subject_task' => $subject_task,
            'task' => @$task,
            'task_meta' => @$task_meta,
            'prev_url' => @$prev_url,
            'group_labels' => @$group_labels,
            'taskModel' => @$taskModel,
        ]);
    }


    public function actionSubjectTopicTestEdit($id = false)
    {
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }

        $model = $id ? $this->findCurriculumSubjectTopicTestModel($id) : new ESubjectResourceQuestion();
        $model->setScenario($id ? ESubjectResourceQuestion::SCENARIO_UPDATE : ESubjectResourceQuestion::SCENARIO_CREATE);

        /*if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }*/
        if ($model->subjectResource) {
            $prev_url = [
                'teacher/subject-topic-test',
                'education_lang' => $model->_language,
                'code' => $model->_subject_topic
            ];
        } else {
            $task = $model->subjectTask;

            $prev_url = ['teacher/subject-task-list',
                'curriculum' => $task->_curriculum,
                'semester' => $task->_semester,
                'subject' => $task->_subject,
                'training_type' => $task->_training_type,
                'education_lang' => $task->_language,
                'code' => $task->id,
                'questions' => 1
            ];
        }

        $subject = ECurriculumSubject::getByCurriculumSemesterSubject($model->_curriculum, $model->_semester, $model->_subject);
        if ($subject === null) {
            $this->notFoundException();
        }

        if ($this->get('delete')) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(__('Question `{title}` deleted successfully', ['title' => $model->getShortTitle()]));
                    return $this->redirect($prev_url);
                }
            } catch (Exception $e) {
                $this->addError($e->getMessage());
            }

            return $this->redirect(['teacher/subject-topic-test-edit', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $this->addSuccess(
                    __(
                        'Question `{title}` {action} successfully.',
                        [
                            'title' => $model->getShortTitle(),
                            'action' => __($id ? 'updated' : 'created'),
                        ]
                    )
                );
                return $this->refresh();
            } else {
                $this->addError($model->getOneError());
            }

        }

        $group_labels = ESubjectSchedule::getGroupByCurriculumSemesterSubjectTrainingLanguage($model->_curriculum, $model->_semester, $model->_subject, $model->_training_type, $model->_language, Yii::$app->user->identity->_employee, true);


        return $this->renderView(
            [
                'model' => $model,
                'subject' => $subject,
                'group_labels' => $group_labels,
                'prev_url' => $prev_url,
            ]
        );
    }

    public function actionTaskRatingInfo()
    {
        if ($id = $this->get('id')) {
            if ($model = $this->findExamScheduleModelOne($id)) {
                if ($student = $this->get('student')) {
                    $studentModel = $this->findStudentOne($student);
                }

                $searchModel = new EStudentTaskActivity();
                $dataProvider = $searchModel->searchForStudent($studentModel, $model);

                return $this->renderAjax('task-rating-info', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]);
            }
        }
    }

    public function actionSubjectTaskSend()
    {
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        if ($curriculum = $this->post('curriculum')) {
            $curriculum_model = ECurriculum::findOne($curriculum);
            if ($curriculum_model === null) {
                $this->notFoundException();
            }
        }
        $semester = $this->post('semester');
        $education_lang = $this->post('education_lang');
        $training_type = $this->post('training_type');

        if ($subject = $this->post('subject')) {
            $subject = ECurriculumSubject::getByCurriculumSemesterSubject($curriculum_model->id, $semester, $subject);
            if ($subject === null) {
                $this->notFoundException();
            }
        }
        $prev_url = null;
        if ($code = $this->post('code')) {
            if ($model = ESubjectTask::findOne(['id' => $code])) {
                $model->scenario = ESubjectTask::SCENARIO_CREATE;
                if ($this->post('send')) {
                    $prev_url = $thisUrl = [
                        'teacher/subject-task-list',
                        'curriculum' => $curriculum_model->id,
                        'semester' => $subject->_semester,
                        'subject' => $subject->_subject,
                        'training_type' => $training_type,
                        'education_lang' => $education_lang,
                        'code' => $model->id,
                        'edit' => 1,
                    ];
                    try {
                        $student = $this->post('student');
                        $student = urldecode($student);
                        $final_exam_type = $this->post('final_exam_type');
                        $final_exam_type = urldecode($final_exam_type);
                        $deadline = $this->post('deadline');
                        $deadline = urldecode($deadline);
                        parse_str($student, $get_student);
                        parse_str($final_exam_type, $get_final_exam_type);
                        parse_str($deadline, $get_deadline);

                        // print_r($get_deadline);
                        $selected_students = array();
                        $finals = array();
                        $deadlines = array();

                        foreach ($get_student as $key => $item) {
                            foreach ($item as $key2 => $item2) {
                                if ($item2 != "" && $item2 != 0) {
                                    $selected_students[$key2] = $item2;
                                }
                            }
                        }
                        foreach ($get_final_exam_type as $key => $item) {
                            foreach ($item as $key2 => $item2) {
                                $finals[$key2] = $item2;
                            }
                        }
                        foreach ($get_deadline as $key => $item) {
                            foreach ($item as $key2 => $item2) {
                                $deadlines[$key2] = $item2;
                            }
                        }

                        $model->active = ESubjectTask::STATUS_ENABLE;

                        $noError = true;
                        if ($model->active) {
                            if ($model->getActiveTestQuestions()->count() < $model->question_count) {
                                $this->addError(__('Kiritilgan test savollari soni belgilangan sondan kam'));
                                $noError = false;
                            }

                            $limit_ball = ESubjectTask::getLimitBallByCurriculumSubjectDetail(
                                $curriculum_model->id,
                                $subject->_subject,
                                $education_lang,
                                $training_type,
                                $subject->_semester,
                                Yii::$app->user->identity->_employee,
                                $model->_exam_type
                            );
                            $exam_type_limit = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject(
                                $curriculum_model->id,
                                $subject->_semester,
                                $subject->_subject,
                                $model->_exam_type
                            );
                            if ($model->curriculum->_marking_system != MarkingSystem::MARKING_SYSTEM_FIVE) {
                                //if ($model->isNewRecord) {
                                if (@$exam_type_limit->max_ball < (@$limit_ball->max_ball)) {
                                    $this->addError(
                                        __('Maksimal ballar yig\'indisi fanga ajratilgan ball limitidan oshib ketdi')
                                    );
                                    return $this->redirect(Yii::$app->request->referrer);
                                    $noError = false;
                                }
                                /*} else {
                                    if (@$exam_type_limit->max_ball < (@$limit_ball->max_ball + @$model->max_ball)) {
                                        $this->addError(__('Maksimal ballar yig\'indisi fanga ajratilgan ball limitidan oshib ketdi'));
                                        return $this->redirect(Yii::$app->request->referrer);
                                        $noError = false;
                                    }
                                }*/
                            }
                        }

                        if ($noError && $model->save()) {
                            foreach ($selected_students as $key => $item) {
                                //if ($st->_group == $group->_group) {
                                $studentMeta = EStudentMeta::getStudentByCurriculumYearSemester(
                                    $model->_curriculum,
                                    $model->_education_year,
                                    $model->_semester,
                                    $key,
                                    EStudentMeta::STATUS_ENABLE
                                );
                                //$old_student = new ESubjectTaskStudent();
                                $old_student = ESubjectTaskStudent::getStudentBySubjectTask(
                                    $key,
                                    $model->id,
                                    $model->_curriculum,
                                    $model->_subject,
                                    $finals[$key]
                                );
                                if ($old_student == null) {
                                    $student = new ESubjectTaskStudent();
                                } else {
                                    $student = $old_student;
                                }

                                $student->scenario = ESubjectTaskStudent::SCENARIO_CREATE;
                                $student->_subject_task = $model->id;
                                $student->_curriculum = $model->_curriculum;
                                $student->_subject = $model->_subject;
                                $student->_training_type = $model->_training_type;
                                $student->_education_year = $model->_education_year;
                                $student->_semester = $model->_semester;
                                $student->_employee = $model->_employee;
                                $student->_student = $key;
                                $student->_group = $studentMeta->_group;
                                $student->_final_exam_type = $finals[$key];
                                $student->deadline = $deadlines[$key];

                                //$student->_task_status = 11;
                                $student->active = ESubjectTaskStudent::STATUS_ENABLE;
                                $student->final_active = 1;
                                $student->_task_type = $model->_task_type;
                                // print_r($student->attributes);
                                try {
                                    ESubjectTaskStudent::updateAll(
                                        [
                                            'final_active' => 0,
                                        ],
                                        [
                                            'AND',
                                            [
                                                '_subject_task' => $model->id,
                                                '_student' => $key,
                                            ],
                                            ['!=', '_final_exam_type', $finals[$key]]
                                        ]
                                    );
                                    $student->save(false);
                                    $this->addSuccess(
                                        __(
                                            'Subject Task [{code}] published for students successfully',
                                            ['code' => $model->id]
                                        )
                                    );
                                } catch (\Exception $e) {
                                    if ($e->getCode() == 23505) {
                                        $this->addError(__('This students have been given this task already'));
                                    } else {
                                        $this->addError($e->getMessage());
                                    }
                                }
                            }
                            //}
                            $this->addSuccess(
                                __('Subject Task [{code}] published successfully', ['code' => $model->id])
                            );
                            return $this->redirect($prev_url);
                            //}
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    //return $this->redirect($thisUrl);
                }
            } else {
                return $this->redirect($prev_url);
            }
        }
    }

    public function actionCertificateCommitteeResult()
    {
        $this->activeMenu = 'teacher-examtable';

        $searchModel = new ECertificateCommitteeResult();

        if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
            $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            $searchModel->_faculty = $faculty;
        }

        if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
            $department = Yii::$app->user->identity->employee->headDepartments->id;
            $searchModel->_faculty = $this->_user()->employee->headDepartments->parent;
            $searchModel->_department = $department;
        }
        $dataProvider = $searchModel->search($this->getFilterParams());

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionCertificateCommitteeResultEdit($id = false)
    {
        $this->activeMenu = 'teacher-examtable';
        if ($id) {
            $model = ECertificateCommitteeResult::findOne($id);
            if ($model === null) {
                $this->notFoundException();
            }
        } else {
            $model = new ECertificateCommitteeResult();

            if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $model->_faculty = $faculty;
            }

            if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
                $department = Yii::$app->user->identity->employee->headDepartments->id;
                $model->_faculty = $this->_user()->employee->headDepartments->parent;
                $model->_department = $department;
            }
        }
        $model->scenario = ECertificateCommitteeResult::SCENARIO_INSERT;
        if (Yii::$app->request->isAjax && $model->load($this->post())) {
            $this->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($this->get('delete', false) && !$model->isNewRecord) {
            try {
                $model->delete();
                $this->addSuccess(__('Certificate committee result `{id}` deleted successfully', ['id' => $model->id]));
                return $this->redirect(['teacher/certificate-committee-result']);
            } catch (\Throwable $exception) {
                $this->addError($exception->getMessage());
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                if ($id) {
                    $this->addSuccess(
                        __(
                            'Certificate committee result `{id}` updated successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Certificate committee result `{id}` created successfully.',
                            [
                                'id' => $model->id,
                            ]
                        )
                    );
                }
                return $this->redirect(['teacher/certificate-committee-result-edit', 'id' => $model->id]);
            }
        }

        if ($this->request->isPost && $model->hasErrors()) {
            $this->addError($model->getErrorSummary(false));
        }

        return $this->renderView(
            [
                'model' => $model,
            ]
        );
    }

    public function actionCalendarPlan()
    {
        $this->activeMenu = 'subjects';
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT && $this->_user()->role->code !== AdminRole::CODE_DEAN) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }

        $searchModel = new ESubjectSchedule();
        $dataProvider = $searchModel->search_group($this->getFilterParams());
        $dataProvider->query->select('e_subject_schedule._curriculum, e_subject_schedule._employee, e_subject_schedule._group, e_subject_schedule._employee, e_subject_schedule._education_year,_subject,e_group._education_lang,_semester,_training_type');
        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT || $this->_user()->role->code == AdminRole::CODE_DEAN) {
            $dataProvider->query->andFilterWhere([
                //  'e_subject_schedule._education_year' => EducationYear::getCurrentYear()->code,
                '_employee' => Yii::$app->user->identity->_employee,
                //'_training_type' => TrainingType::TRAINING_TYPE_LECTURE,
            ]);
            if ($searchModel->_education_year == null) {
                $searchModel->_education_year = EducationYear::getCurrentYear()->code;
                $dataProvider->query->andFilterWhere(['e_subject_schedule._education_year' => EducationYear::getCurrentYear()->code]);
            }
            $dataProvider->query->andFilterWhere([
                '<>', '_training_type', TrainingType::TRAINING_TYPE_INDEPENDENT,
            ]);
        }
        if ($this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            if (Yii::$app->user->identity->employee->headDepartments) {
                $department = Yii::$app->user->identity->employee->headDepartments->id;
                $ids = EEmployeeMeta::find()
                    ->select(['_employee'])
                    ->where(['active' => EEmployeeMeta::STATUS_ENABLE, '_department' => $department])
                    ->column();
                $dataProvider->query->andFilterWhere(['e_subject_schedule._employee' => $ids]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $ids = ECurriculum::find()
                    ->select(['id'])
                    ->where(['active' => ECurriculum::STATUS_ENABLE, '_department' => $faculty])
                    ->column();
                $dataProvider->query->andFilterWhere(['e_subject_schedule._curriculum' => $ids]);
            }
            else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }



        $dataProvider->sort->defaultOrder = ['e_subject_schedule._education_year' => SORT_DESC, '_semester' => SORT_ASC, '_subject' => SORT_ASC];
        $dataProvider->query->groupBy(['e_subject_schedule._curriculum', 'e_subject_schedule._employee','e_subject_schedule._group','e_subject_schedule._employee', 'e_subject_schedule._education_year', '_subject', 'e_group._education_lang', '_semester', '_training_type']);

        if ($curriculum = $this->get('curriculum')) {
            $curriculum_model = ECurriculum::findOne($curriculum);
            if ($curriculum_model === null) {
                $this->notFoundException();
            }
        }
        $semester = $this->get('semester');
        $educationYear = $this->get('educationYear');
        $education_lang = $this->get('education_lang');
        $training_type = $this->get('training_type');
        $group = $this->get('group');
        if ($subject = $this->get('subject')) {
            $subject = ECurriculumSubject::getByCurriculumSemesterSubject($curriculum_model->id, $semester, $subject);
            if ($subject === null) {
                $this->notFoundException();
            }
        }
        if ($group = $this->get('group')) {
            $group = EGroup::findOne($group);
            if ($group === null) {
                $this->notFoundException();
            }
        }

        $dataProviderTopic = "";
        if ($curriculum && $subject) {

            $searchModelTopic = new ECurriculumSubjectTopic();
            $dataProviderTopic = $searchModelTopic->search($this->getFilterParams());
            $dataProviderTopic->query->andFilterWhere(['_curriculum' => $curriculum_model->id]);
            $dataProviderTopic->query->andFilterWhere(['_subject' => $subject->_subject]);
            $dataProviderTopic->query->andFilterWhere(['_semester' => $subject->_semester]);
            $dataProviderTopic->query->andFilterWhere(['_training_type' => $training_type]);

            if ($this->_user()->role->code == AdminRole::CODE_TEACHER)
                $schedule = $this->findSubjectScheduleByAttributesModel($educationYear, $subject->_semester, $group->id, $subject->_subject, $training_type, Yii::$app->user->identity->_employee);
            else
                $schedule = $this->findSubjectScheduleByAttributeModel($educationYear, $subject->_semester, $group->id, $subject->_subject, $training_type, false);


            $params['lesson_dates'] = ESubjectSchedule::find()
                ->where(['_education_year' => $educationYear, '_group' => $group->id,
                    '_semester' => $subject->_semester, '_subject' => $subject->_subject,
                    '_training_type' => $training_type])
                ->groupBy(['lesson_date', '_lesson_pair', '_training_type', '_subject_topic', 'id'])
                ->orderBy(['lesson_date' => SORT_ASC, '_lesson_pair' => SORT_ASC])->all();

            $absentsControl = EAttendanceControl::find()
                ->where([
                    '_education_year' => $educationYear,
                    '_subject' => $subject->_subject,
                    '_semester' => $subject->_semester,
                    '_training_type' => $training_type,
                    '_group' => $group->id,
                    '_employee' => $schedule->_employee
                ])
                ->all();

            $check = [];
            foreach ($absentsControl as $absent_control) {
                $params['check'][Yii::$app->formatter->asDate($absent_control->lesson_date, 'php:Y-m-d')][$absent_control->_lesson_pair] = $absent_control->active;
            }

            //$countQuery = clone $lesson_dates;
            //$pages = new Pagination(['totalCount' => $countQuery->count(), 'defaultPageSize' => 4, /*'pageSizeLimit'=>2*/]);
            //$models = $lesson_dates->offset($pages->offset)->limit($pages->limit)->all();
            if ($this->get('download')) {
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'tempDir' => Yii::getAlias('@runtime/mpdf'),
                ]);
                $mpdf->defaultCssFile = Yii::getAlias('@backend/assets/app/css/pdf-print.css');
                $mpdf->shrink_tables_to_fit = 1;
                $mpdf->keep_table_proportions = true;
                $univer = EUniversity::findCurrentUniversity();
                $mpdf->SetDisplayMode('fullwidth');
                $mpdf->WriteHTML($this->renderPartial('calendar-plan-pdf', [
                    'subject' => $subject,

                    'absentsControl' => $absentsControl,

                    'educationYear' => $educationYear,
                    'group' => $group,
                    'training_type' => $training_type,
                    'dataProviderTopic' => $dataProviderTopic,
                    'univer' => $univer,
                    'params' => $params,
                    'schedule' => $schedule,
                ]));

                return $mpdf->Output('Calendar_plan-' . $subject->subject->name . '.pdf', Destination::DOWNLOAD);
            }

            return $this->render('calendar-plan-view', [
                'subject' => $subject,
                'training_type' => $training_type,
                'dataProviderTopic' => $dataProviderTopic,
                'params' => $params,
            ]);


        }
        //$dataProvider->query->orderBy(['_semester' => SORT_ASC]);
        return $this->render('calendar-plans', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    protected function findSubjectScheduleModel($id, $teacher)
    {
        if (($model = ESubjectSchedule::findOne(['id' => $id, '_employee' => $teacher])) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findSubjectScheduleByAttributesModel($education_year, $semester, $group, $subject, $training_type, $teacher)
    {
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT)
            $model = ESubjectSchedule::findOne(['_education_year' => $education_year, '_semester' => $semester, '_group' => $group, '_subject' => $subject, '_training_type' => $training_type, '_employee' => $teacher]);
        else
            $model = ESubjectSchedule::findOne(['_education_year' => $education_year, '_semester' => $semester, '_group' => $group, '_subject' => $subject, '_training_type' => $training_type]);

        if ($model !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findSubjectScheduleByAttributeModel($education_year, $semester, $group, $subject, $training_type, $teacher)
    {
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER)
            $model = ESubjectSchedule::findOne(['_education_year' => $education_year, '_semester' => $semester, '_group' => $group, '_subject' => $subject, '_training_type' => $training_type, '_employee' => $teacher]);
        else
            $model = ESubjectSchedule::findOne(['_education_year' => $education_year, '_semester' => $semester, '_group' => $group, '_subject' => $subject, '_training_type' => $training_type]);

        if ($model !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    /**
     * @param $id
     * @param $teacher
     * @param string $exam_type
     * @return ESubjectExamSchedule
     * @throws NotFoundHttpException
     */
    protected function findExamScheduleModelOne($id)
    {
        $model = ESubjectExamSchedule::findOne($id);
        if ($model !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findStudentOne($id)
    {
        $model = EStudent::findOne($id);
        if ($model !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findExamScheduleModel($id, $teacher, $exam_type = "")
    {
        if ($exam_type == "")
            $model = ESubjectExamSchedule::findOne(['id' => $id, '_employee' => $teacher]);
        else
            $model = ESubjectExamSchedule::findOne(['id' => $id, '_employee' => $teacher, '_exam_type' => $exam_type]);

        if ($model !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findCurriculumSubjectModel($id, $department = "", $employee = "")
    {
        if ($department != "") {
            if (($model = ECurriculumSubject::findOne(['id' => $id, '_department' => $department])) !== null) {
                return $model;
            } else {
                $this->notFoundException();
            }
        } else if ($employee != "") {
            if (($model = ECurriculumSubject::findOne(['id' => $id, '_employee' => $employee])) !== null) {
                return $model;
            } else {
                $this->notFoundException();
            }
        }
    }

    protected function findCurriculumSubjectTopicTestModel($id)
    {
        if (($model = ESubjectResourceQuestion::findOne(['id' => $id])) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    private function exportQuestions($contents, $filename)
    {
        $filename = str_replace('/', '-', $filename . '_questions.txt');
        $dir = Yii::getAlias('@runtime/q');
        FileHelper::createDirectory($dir);
        @file_put_contents($dir . DS . $filename, implode("\n+++++\n", $contents));
        Yii::$app->response->sendFile($dir . DS . $filename);
        FileHelper::unlink($dir . DS . $filename);
        return;
    }
    /*protected function findAttendanceControl($id, $teacher)
    {
        if (($model = EAttendanceControl::findOne(['id'=>$id,'_employee'=>$teacher])) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }*/

}
