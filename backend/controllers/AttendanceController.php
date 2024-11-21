<?php

namespace backend\controllers;

use backend\models\FilterForm;
use backend\models\FormUploadTrans;
use common\components\AccessResources;
use common\components\Config;
use common\models\attendance\EAttendance;
use common\models\attendance\EAttendanceActivity;
use common\models\attendance\EAttendanceControl;
use common\models\attendance\EAttendanceReport;
use common\models\attendance\EAttendanceSettingBorder;
use common\models\attendance\ELessonsStat;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\MarkingSystem;

use common\models\student\EStudentMeta;
use common\models\system\AdminRole;
use common\models\system\classifier\SemestrType;
use common\models\system\classifier\StudentStatus;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
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

class AttendanceController extends BackendController
{
    public $activeMenu = 'attendance';

    public function actionReport()
    {
        $user = $this->_user();
        $faculty = $user->role->isDeanOrTutorRole() ? $this->_user()->employee->deanFaculties->id : null;
        $searchModel = new EAttendanceReport();

        return $this->renderView([
            'dataProvider' => $searchModel->searchForReport($this->getFilterParams(), $user, $faculty),
            'searchModel' => $searchModel,
            'faculty' => $faculty,
            'user' => $user,
        ]);
    }

    public function actionActivity()
    {
        if (!$this->_user()->role->isDeanRole()) {
            $this->addInfo(__('This page is for the deans only.'));
            return $this->goBack();
        }

        $faculty = $this->_user()->employee->deanFaculties->id;
        $searchModel = new EAttendance();
        $activity = new EAttendanceActivity(['scenario' => EAttendanceActivity::SCENARIO_CHANGE_DEAN]);

        if ($activity->load($this->post())) {
            if ($activity->validate()) {
                try {
                    if ($count = $activity->processAttendanceActivity($this->_user())) {
                        $this->addSuccess(__('Attendance activity updated for {count} students', ['count' => $count]));
                        return $this->redirect(['attendance/activity']);
                    }
                } catch (\Exception $exception) {
                    $this->addError($exception->getMessage());
                }
            }
        }

        if ($d = $this->get('download')) {
            if ($attendance = EAttendanceActivity::findOne($d)) {
                if ($filePath = $attendance->getFilePath('file', true)) {
                    return Yii::$app->response->sendFile($filePath, $attendance->file['name']);
                }
            }
            return $this->redirect(currentTo(['download' => null]));
        }

        return $this->renderView([
            'dataProvider' => $searchModel->searchForActivity($this->getFilterParams(), $faculty),
            'searchModel' => $searchModel,
            'faculty' => $faculty,
            'activity' => $activity,
        ]);
    }

    public function actionAttendanceJournal($education_year = "", $semester = "", $group = "", $subject = "", $training_type = "")
    {
        if (!$this->_user()->role->isDeanRole()) {
            $this->addInfo(__('This page is for the deans only.'));
            return $this->goBack();
        }

        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        if ($education_year != "" && $semester != "" && $group != "" && $subject != "" && $training_type != "") {
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
                    }
                }
            }
            Url::remember();
            return $this->render('/teacher/attendance-journal', [
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

            if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
                if (Yii::$app->user->identity->employee->deanFaculties) {
                    $ids = ECurriculum::find()
                        ->select(['id'])
                        ->where(['active' => ECurriculum::STATUS_ENABLE, '_department' => $faculty])
                        ->column();
                    $dataProvider->query->andFilterWhere(['e_subject_schedule._curriculum' => $ids]);
                }
            }
            $current_year = EducationYear::getCurrentYear();
            if ($current_year != null) {
                $searchModel->_education_year = $current_year->code;
                $dataProvider->query->andFilterWhere(['e_subject_schedule._education_year' => $current_year->code]);
            }

            $dataProvider->sort->defaultOrder = ['_group' => SORT_ASC, '_subject' => SORT_ASC];
            $dataProvider->query->groupBy(['e_subject_schedule._education_year', '_subject', '_group', '_semester', '_training_type']);

            return $this->render('/teacher/journal-list', [
                'dataProvider' => @$dataProvider,
                'searchModel' => @$searchModel,
            ]);

        }
    }

    public function actionOverall()
    {
        $searchModel = new FilterForm();

        $autumn = array();
        $spring = array();
        $semesters = array();
        $faculty = "";
        $current_year = EducationYear::getCurrentYear();
        if ($current_year != null) {
            $searchModel->_education_year = $current_year->code;
            $searchModel->_semester_type = $current_year->_semestr_type;
        }
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
        $current_year = EducationYear::getCurrentYear();
        if ($current_year != null) {
            $searchModel->_education_year = $current_year->code;

        }
        if ($searchModel->load(Yii::$app->request->get())) {

            $semestr_list = EAttendance::find()->select('_semester')->where(['_education_year' => $searchModel->_education_year])->groupBy(['_semester'])->all();
            foreach ($semestr_list as $semestr) {
                if ($semestr->_semester % 2 == 1) {
                    $autumn[$semestr->_semester] = $semestr->_semester;
                } elseif ($semestr->_semester % 2 == 0) {
                    $spring[$semestr->_semester] = $semestr->_semester;
                }
            }
            if ($searchModel->_semester_type == SemestrType::EDUCATION_TYPE_AUTUMN) {
                $semesters = $autumn;
            } elseif ($searchModel->_semester_type == SemestrType::EDUCATION_TYPE_SPRING) {
                $semesters = $spring;
            }

            $query = EAttendance::find()
                ->joinWith(['studentMeta'])
                ->select('e_attendance._student, e_attendance._semester, SUM(absent_on+absent_off) as summary, SUM(absent_on) as absent_on, SUM(absent_off) as absent_off')
                ->where([
                    'e_attendance._education_year' => $searchModel->_education_year,
                    'e_student_meta._education_year' => $searchModel->_education_year,
                    'e_student_meta._department' => $searchModel->_faculty,
                    'e_student_meta._education_type' => $searchModel->_education_type,
                    'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                    'e_student_meta.active' => EStudentMeta::STATUS_ENABLE,
                ])
                ->andWhere(['in', '_semester', $semesters])
                ->orderBy(['absent_off' => SORT_DESC])
                ->groupBy(['e_attendance._student', 'e_attendance._semester']);
//                ->all();
            //           print_r($query->all());
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 200,
                ],
                // 'pagination' => ['pageSize' => $pagination],
                //   'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]]
            ]);
        }


        return $this->renderView([
            'dataProvider' => @$dataProvider,
            'searchModel' => $searchModel,
            'faculty' => $faculty,
            //'attendance' => $attendance,
        ]);
    }

    public function actionBySubjects()
    {
        $searchModel = new FilterForm();
        $absent_on = array();
        $absent_off = array();
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
        $current_year = EducationYear::getCurrentYear();
        if ($current_year != null) {
            $searchModel->_education_year = $current_year->code;
        }

        if ($searchModel->load(Yii::$app->request->post())) {
            $students = EStudentMeta::getStudiedContingentByYearSemesterGroup($searchModel->_education_year, $searchModel->_semester, $searchModel->_group);
            $list_student = array();
            foreach ($students as $student) {
                $list_student [$student->_student] = $student->_student;
            }

            $curriculum_subjects = ECurriculumSubject::getSubjectByCurriculumSemester($searchModel->_curriculum, $searchModel->_semester);
            $attendances = EAttendance::find()
                ->joinWith(['studentMeta'])
                ->select('e_attendance._student, _subject, SUM(absent_on) as absent_on, SUM(absent_off) as absent_off')
                ->where([
                    'e_attendance._education_year' => $searchModel->_education_year,
                    'e_attendance._semester' => $searchModel->_semester,
                    'e_student_meta._group' => $searchModel->_group,
                    //  'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED
                ])
                //->andWhere(['in', '_student', $list_student])
                ->groupBy(['e_attendance._student', '_subject'])
                ->all();

            foreach ($curriculum_subjects as $subject) {
                foreach ($attendances as $attendance) {
                    if ($subject->_subject == $attendance->_subject) {
                        $absent_on[$subject->_subject][$attendance->_student] = $attendance->absent_on;
                        $absent_off[$subject->_subject][$attendance->_student] = $attendance->absent_off;
                    }
                }
            }

        }
        return $this->renderView([
            'searchModel' => $searchModel,
            'absent_on' => $absent_on,
            'absent_off' => $absent_off,
            'students' => @$students,
            'faculty' => $faculty,
            'curriculum_subjects' => @$curriculum_subjects,
        ]);
    }

    public function actionAttendanceSetting($id = false)
    {
        //$model = new EAttendanceSettingBorder();
        $model = $id ? $this->findAttendanceSettingBorder($id) : new EAttendanceSettingBorder();
        $model->scenario = EAttendanceSettingBorder::SCENARIO_CREATE;
        if ($this->get('delete')) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(__('Item [{code}] of attendance setting is deleted successfully', ['id' => $model->id]));
                }
            } catch (\Exception $e) {
                $this->addError($e->getMessage());
            }
            return $this->redirect(['attendance/attendance-setting']);
        }

        if ($model->load(Yii::$app->request->post())) {
            try {
                if ($model->save()) {
                    $this->addSuccess(__('Item [{code}] added to grade type', ['code' => $model->id]));
                }
            } catch (\Exception $e) {
                if ($e->getCode() == 23505) {
                    $this->addError(__('This attendance setting is given'));
                } else {
                    $this->addError($e->getMessage());
                }
            }
            return $this->redirect(['attendance/attendance-setting']);
        }
        $searchModel = new EAttendanceSettingBorder();
        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionChangeAttendance()
    {
        /*if ($model = EPublicationMethodical::findOne(['id' => $attribute])) {
            return $this->renderAjax('publication-methodical-view', [
                'model' => $model,
            ]);
        }*/


        if ($id = $this->get('id')) {
            if ($model = $this->findAttendance($id)) {

                if ($d = $this->get('download')) {
                    if ($attendance = EAttendanceActivity::findOne($d)) {
                        if ($filePath = $attendance->getFilePath('file', true)) {
                            return Yii::$app->response->sendFile($filePath, $attendance->file['name']);
                        }
                    }
                    return $this->redirect(currentTo(['download' => null]));
                }

                $activity = new EAttendanceActivity();
                $activity->scenario = EAttendanceActivity::SCENARIO_CHANGE_DEAN;
                //$absent = $model->absent_on ==2 ? $model->absent_on : 0;
                $activity->_attendance = $model->id;
                $activity->_employee = Yii::$app->user->identity->getId();
                $activity->status_for_activity = EAttendanceActivity::STATUS_FOR_CHANGE_CAUSE;

                if ($activity->load(Yii::$app->request->post())) {
                    if ($activity->save()) {
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                }
                return $this->renderAjax('change-attendance', [
                    'model' => $model,
                    'activity' => $activity,
                ]);
            }
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

    protected function findAttendanceSettingBorder($id)
    {
        if (($model = EAttendanceSettingBorder::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findAttendance($id)
    {
        if (($model = EAttendance::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    /*protected function findCurriculumModel($id)
    {
        if (($model = ECurriculum::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }*/

    public function actionLessons()
    {
        $user = $this->_user();
        $searchModel = new ELessonsStat();
        $faculty = $user->role->isDeanOrTutorRole() ? @$this->_user()->employee->deanFaculties->id : null;

        return $this->renderView([
            'dataProvider' => $searchModel->searchAttendance($this->getFilterParams(), $user, $faculty),
            'searchModel' => $searchModel,
            'user' => $user,
            'faculty' => $faculty,
        ]);
    }
}
