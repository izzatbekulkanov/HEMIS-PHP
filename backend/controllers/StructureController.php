<?php

namespace backend\controllers;

use backend\models\FormUploadTrans;
use common\components\AccessResources;
use common\components\Config;
use common\models\structure\EUniversity;
use common\models\structure\EDepartment;

use common\models\system\Admin;
use common\models\system\classifier\StructureType;
use Yii;
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


/**
 * HEducationFormController implements the CRUD actions for HEducationForm model.
 */
class StructureController extends BackendController
{
    public $activeMenu = 'structure';

    protected function getUniversity()
    {
        return EUniversity::findCurrentUniversity();
    }

    public function actionUniversity()
    {
        $model = $this->getUniversity();

        if ($model === null) {
            $model = new EUniversity();
        }

        return $this->renderView([
            'model' => $model,
        ]);
    }

    public function actionUniversityUpdate()
    {
        $model = $this->getUniversity();

        if ($model === null) {
            $model = new EUniversity();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->syncModelToApi($model);

            $this->addSuccess(__('University data updated successfully'));

            return $this->redirect(['university-update']);
        }

        return $this->renderView([
            'model' => $model,
        ]);
    }

    /**
     * @resource structure/faculty-delete
     */
    public function actionFaculty($id = false)
    {
        $university = $this->getUniversity();

        $model = $id ? $this->findDepartmentModel($id) : new EDepartment();

        $model->scenario = EDepartment::SCENARIO_FACULTY;
        $model->_university = $university->id;
        $model->_structure_type = StructureType::STRUCTURE_TYPE_FACULTY;

        if ($this->get('delete') && $this->canAccessToResource('structure/faculty-delete')) {

            $message = false;

            if ($model->tryToDelete(function () use ($model) {
                return $this->syncModelToApi($model, true);
            }, $message)) {
                $this->addSuccess(__('Faculty [{code}] deleted successfully', ['code' => $model->code]));
                return $this->redirect(['faculty']);
            } else {
                if ($message)
                    $this->addError($message);
            }

            return $this->redirect(['faculty', 'id' => $model->id]);
        }

        if ($this->checkModelToApi($model)) {
            return $this->redirect(['faculty', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->syncModelToApi($model);

            $this->addSuccess(__('Faculty [{code}] updated successfully', ['code' => $model->code]));

            return $this->redirect(['faculty', 'id' => $model->id]);
        }

        $searchModel = new EDepartment();
        return $this->renderView([
            'dataProvider' => $searchModel->searchByType(StructureType::STRUCTURE_TYPE_FACULTY, $this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
            'university' => $university,
        ]);
    }


    /**
     * @resource structure/department-delete
     */
    public function actionDepartment($id = false)
    {
        $university = $this->getUniversity();

        $model = $id ? $this->findDepartmentModel($id) : new EDepartment();
        $model->scenario = EDepartment::SCENARIO_DEPARTMENT;
        $model->_university = $university->id;
        $model->_structure_type = StructureType::STRUCTURE_TYPE_DEPARTMENT;

        if ($this->get('delete') && $this->canAccessToResource('structure/department-delete')) {
            $message = false;

            if ($model->tryToDelete(function () use ($model) {
                return $this->syncModelToApi($model, true);
            }, $message)) {
                $this->addSuccess(__('Department [{code}] deleted successfully', ['code' => $model->code]));
                return $this->redirect(['department']);
            } else {
                if ($message)
                    $this->addError($message);
            }

            return $this->redirect(['department', 'id' => $model->id]);
        }

        if ($this->checkModelToApi($model)) {
            return $this->redirect(['department', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->syncModelToApi($model);

            $this->addSuccess(__('Department [{code}] updated successfully', ['code' => $model->code]));

            return $this->redirect(['department']);
        }

        $searchModel = new EDepartment();
        return $this->renderView([
            'dataProvider' => $searchModel->searchByType(StructureType::STRUCTURE_TYPE_DEPARTMENT, $this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
            'university' => $university,
        ]);
    }


    /**
     * @resource structure/section-delete
     */
    public function actionSection($id = false)
    {
        $university = $this->getUniversity();

        $model = $id ? $this->findDepartmentModel($id) : new EDepartment();
        $model->scenario = EDepartment::SCENARIO_SECTION;
        $model->_university = $university->id;
        if ($this->get('delete') && $this->canAccessToResource('structure/section-delete')) {
            $message = false;

            if ($model->tryToDelete(function () use ($model) {
                return $this->syncModelToApi($model, true);
            }, $message)) {
                $this->addSuccess(__('{section} [{code}] deleted successfully', ['code' => $model->code, 'section' => $model->structureType->name]));
                return $this->redirect(['section']);
            } else {
                if ($message)
                    $this->addError($message);
            }

            return $this->redirect(['section', 'id' => $model->id]);
        }

        if ($this->checkModelToApi($model)) {
            return $this->redirect(['section', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->syncModelToApi($model);

            $this->addSuccess(__('{section} [{code}] updated successfully', ['code' => $model->code, 'section' => $model->structureType->name]));

            return $this->redirect(['section', 'id' => $model->id]);
        }

        $searchModel = new EDepartment();
        return $this->renderView([
            'dataProvider' => $searchModel->searchByType(false, $this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
            'university' => $university,
        ]);
    }

    protected function findDepartmentModel($id)
    {
        if (($model = EDepartment::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }
}
