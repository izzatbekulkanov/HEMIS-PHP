<?php

namespace backend\controllers;

use backend\models\FormUploadTrans;
use common\components\AccessResources;
use common\components\Config;
use common\models\infrastructure\Building;
use common\models\infrastructure\EAuditorium;

use common\models\system\Admin;
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
class InfrastructureController extends BackendController
{
    public $activeMenu = 'infrastructure';

    /**
     * @resource infrastructure/building-delete
     */
    public function actionBuilding($id = false)
    {
        $model = $id ? $this->findBuildingModel($id) : new Building();
        $model->scenario = Building::SCENARIO_INSERT;

        if ($this->get('delete') && $this->canAccessToResource('infrastructure/building-delete')) {

            if ($issue = $model->anyIssueWithDelete()) {
                $this->addError($issue);
                return $this->redirect(['building', 'id' => $model->code]);
            } else {
                $this->addSuccess(__('Building [{code}] deleted successfully', ['code' => $model->name]));
                return $this->redirect(['building']);
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($id) {
                $this->addSuccess(__('Building [{code}] updated successfully', ['code' => $model->name]));
            } else {
                $this->addSuccess(__('Building [{code}] created successfully', ['code' => $model->name]));
            }
            $model = new Building();
            $model->scenario = Building::SCENARIO_INSERT;
        }

        $searchModel = new Building();

        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    /**
     * @resource infrastructure/auditorium-delete
     */
    public function actionAuditorium($id = false)
    {
        $model = $id ? $this->findAuditoriumModel($id) : new EAuditorium($this->getSession('auditorium_data'));
        $model->scenario = EAuditorium::SCENARIO_INSERT;

        if ($this->get('delete') && $this->canAccessToResource('infrastructure/auditorium-delete')) {
            if ($issue = $model->anyIssueWithDelete()) {
                $this->addError($issue);
                return $this->redirect(['auditorium', 'id' => $model->code]);
            } else {
                $this->addSuccess(__('Auditorium [{code}] deleted successfully', ['code' => $model->name]));
                return $this->redirect(['auditorium']);
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->setSession('auditorium_data', $model->getAttributes(['_building', '_auditorium_type']));

            if ($id) {
                $this->addSuccess(__('Auditorium [{code}] updated successfully', ['code' => $model->name]));
            } else {
                $this->addSuccess(__('Auditorium [{code}] created successfully', ['code' => $model->name]));
            }

            $model = new EAuditorium($this->getSession('auditorium_data'));
            $model->scenario = EAuditorium::SCENARIO_INSERT;
        }

        $searchModel = new EAuditorium();
        $provider = $searchModel->search($this->getFilterParams());

        return $this->renderView([
            'dataProvider' => $provider,
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    protected function findBuildingModel($id)
    {
        if (($model = Building::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findAuditoriumModel($id)
    {
        if (($model = EAuditorium::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }
}
