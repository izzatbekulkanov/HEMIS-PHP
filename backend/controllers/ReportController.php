<?php

namespace backend\controllers;

use backend\assets\VendorAsset;
use backend\models\FilterForm;
use backend\models\FormUploadTrans;
use common\components\AccessResources;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\ESubjectScheduleTeacherMap;
use common\models\employee\EEmployeeMeta;
use common\models\infrastructure\EAuditorium;
use common\models\structure\EDepartment;
use common\models\student\EStudentMeta;
use common\models\system\AdminRole;
use common\models\system\classifier\TeacherPositionType;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\base\Exception;
use yii\helpers\BaseFileHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class ReportController extends BackendController
{
    public $activeMenu = 'report';

    public function actionByRooms()
    {
        $searchModel = new EAuditorium();
        if ($date = $this->get('date')) {
            if ($room = $this->get('room')) {
                return $this->renderPartial('_schedule', [
                    'models' => ESubjectSchedule::find()
                        ->andWhere([
                            '_auditorium' => $room,
                            'lesson_date' => $date,
                        ])
                        ->orderBy(['_lesson_pair' => SORT_ASC])
                        ->all(),
                ]);
            }
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'data' => $searchModel->searchForAuditorium($this->getFilterParams())
        ]);
    }

    public function actionTeacherMap()
    {
        $searchModel = new ESubjectScheduleTeacherMap();
        $department = null;

        if ($date = $this->get('date')) {
            if ($employee = $this->get('employee')) {
                return $this->renderPartial('_schedule', [
                    'models' => ESubjectSchedule::find()
                        ->andWhere([
                            '_employee' => $employee,
                            'lesson_date' => $date,
                        ])
                        ->orderBy(['_lesson_pair' => SORT_ASC])
                        ->all(),
                ]);
            }
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'data' => $searchModel->searchForTeachers($this->getFilterParams(), $this->_user())
        ]);
    }

    public function actionByTeachers()
    {
        $searchModel = new EEmployeeMeta();

        $dataProvider = $searchModel->searchForLog($this->getFilterParams());
        //$dataProvider->query->andFilterWhere(['in', '_position', TeacherPositionType::TEACHER_POSITIONS]);
        $department = null;
        if ($this->_user()->role->isDeanOrTutorRole()) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $department = Yii::$app->user->identity->employee->deanFaculties->id;
                $searchModel->_faculty = $department;
                $ids = EDepartment::find()->select('id, parent')->where(['parent' => $department])->column();
                $dataProvider->query->andFilterWhere(['_department' => !empty($ids) ? $ids : $department]);


            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }


        return $this->renderView(
            [
                'dataProvider' => $dataProvider,

                'searchModel' => $searchModel,
                'faculty' => $department,
            ]
        );
    }

    public function actionByStudents()
    {
        $searchModel = new EStudentMeta();
        $department = null;
        if ($this->_user()->role->isDeanOrTutorRole()) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $department = Yii::$app->user->identity->employee->deanFaculties->id;
                $searchModel->_department = $department;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        $dataProvider = $searchModel->searchForLog($this->getFilterParams(), $department);


        return $this->renderView(
            [
                'dataProvider' => $dataProvider,

                'searchModel' => $searchModel,
                'faculty' => $department,
            ]
        );
    }

    public function actionByResources()
    {
        $searchModel = new EEmployeeMeta();

        $dataProvider = $searchModel->searchForResources($this->getFilterParams());

        //$dataProvider->query->andFilterWhere(['in', '_position', TeacherPositionType::TEACHER_POSITIONS]);
        $department = null;
        if ($this->_user()->role->isDeanOrTutorRole()) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $department = Yii::$app->user->identity->employee->deanFaculties->id;
                $searchModel->_faculty = $department;
                $ids = EDepartment::find()->select('id, parent')->where(['parent' => $department])->column();
                $dataProvider->query->andFilterWhere(['e_employee_meta._department' => !empty($ids) ? $ids : $department]);


            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        if (empty($searchModel->_faculty) || empty($searchModel->_department) || empty($searchModel->_education_year) || empty($searchModel->_semester)) {
            $dataProvider->query->andWhere('1 <> 1');
        }
        return $this->renderView(
            [
                'dataProvider' => $dataProvider,

                'searchModel' => $searchModel,
                'faculty' => $department,
            ]
        );
    }

}
