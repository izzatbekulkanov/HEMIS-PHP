<?php

namespace backend\controllers;

use common\models\academic\EDecree;
use common\models\student\EStudentDecreeMeta;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class DecreeController extends BackendController
{
    public $activeMenu = 'transfer';

    public function actionApply()
    {
        $searchModel = new EStudentDecreeMeta();
        $handlers = EStudentDecreeMeta::getDecreeTypeApplyHandlers();

        $faculty = $this->_user()->role->isDeanRole() ? @$this->_user()->employee->deanFaculties->id : null;

        if ($searchModel->load($this->post())) {
            if ($searchModel->validate()) {
                if ($items = $this->post('selection')) {
                    try {
                        if ($count = $searchModel->applyItems($this->_user(), $items)) {
                            Yii::$app->session->set('last_applied_decree_apply', $searchModel->_decree);
                            $this->addSuccess(__('Decree {name} applied to {count} students', ['count' => $count, 'name' => $searchModel->decree->getFullInformation()]));
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage(), true);
                    }
                }
                return $this->redirect(['apply']);
            }
        }

        $searchModel->selectedStudents = 0;

        if ($d = Yii::$app->session->get('last_applied_decree_apply')) {
            $searchModel->_decree = $d;
        }

        return $this->renderView([
            'dataProvider' => $searchModel->searchForDecreeApply($this->getFilterParams(), $faculty),
            'searchModel' => $searchModel,
            'handlers' => $handlers,
        ]);
    }

    public function actionIndex($students = false)
    {
        if ($students) {
            if ($model = $this->findModel($students)) {
                return $this->renderPartial('students', [
                    'model' => $model,
                    'dataProvider' => $model->getStudentsProvider()
                ]);
            }
        }
        if ($this->get('status')) {
            if ($model = $this->findModel($this->get('id'))) {
                return $model->updateAttributes(['status' => $model->status == EDecree::STATUS_ENABLE ? EDecree::STATUS_DISABLE : EDecree::STATUS_ENABLE]);
            }
        }
        $searchModel = new EDecree();

        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForEmployee($this->getFilterParams(), $this->_user()),
        ]);
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     * @skipAccess
     */
    public function actionFile($id)
    {
        $model = $this->findModel($id);

        if ($file = $model->getFilePath('file')) {
            $name = $model->file;
            return Yii::$app->response->sendFile($file, $name['name']);
        }

        return $this->redirect(['index']);
    }

    public function actionEdit($id = false)
    {
        if ($id) {
            $model = $this->findModel($id);
            if ($this->_user()->role->isDeanRole()) {
                if ($model->_department !== $this->_user()->employee->deanFaculties->id) {
                    return $this->redirect(['decree/index']);
                }
            }
        } else {
            $model = new EDecree();

            if ($this->_user()->role->isDeanRole()) {
                $model->_department = $this->_user()->employee->deanFaculties->id;
            }
        }


        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $this->addSuccess(
                    __($id ? 'Decree `{name}` updated successfully.' : 'Decree `{name}` created successfully.', [
                        'name' => $model->name
                    ])
                );
                return $this->redirect(['edit', 'id' => $model->id]);
            } else {
                $this->addError($model->getOneError());
            }
        }

        return $this->renderView([
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($this->_user()->role->isDeanRole()) {
            if ($model->_department !== $this->_user()->employee->deanFaculties->id) {
                return $this->redirect(['decree/index']);
            }
        }

        if ($message = $model->anyIssueWithDelete()) {
            $this->addError($message);
            return $this->redirect(['edit', 'id' => $model->id]);
        } else {
            $this->addSuccess(
                __('Decree `{name}` deleted successfully.', [
                    'name' => $model->name
                ])
            );
        }

        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return EDecree
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = EDecree::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }
}
