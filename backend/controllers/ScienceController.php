<?php

namespace backend\controllers;

use backend\models\FilterForm;
use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiError;
use common\models\curriculum\EducationYear;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\science\ECriteriaTemplate;
use common\models\science\EDissertationDefense;
use common\models\science\EDoctorateStudent;
use common\models\science\EProject;
use common\models\science\EProjectExecutor;
use common\models\science\EProjectMeta;
use common\models\science\EPublicationAuthorMeta;
use common\models\science\EPublicationCriteria;
use common\models\science\EPublicationMethodical;
use common\models\science\EPublicationProperty;
use common\models\science\EPublicationScientific;
use common\models\science\EScientificPlatformCriteria;
use common\models\science\EScientificPlatformProfile;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\system\AdminRole;
use common\models\system\classifier\Gender;
use common\models\system\classifier\PublicationDatabase;
use Yii;
use yii\base\Exception;
use yii\data\ArrayDataProvider;
use yii\db\IntegrityException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;


class ScienceController extends BackendController
{
    public $activeMenu = 'science';

    public function actionProject($id = false)
    {
        if ($id) {
            if ($model = $this->findProjectModel($id)) {

                if ($this->checkModelToApi($model)) {
                    return $this->redirect(['project', 'id' => $model->id]);
                }

                return $this->render('project-view', ['model' => $model]);
            }
        }

        $searchModel = new EProject();

        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
        ]);
    }

    public function actionProjectEdit($id = false)
    {
        if ($id) {
            $model = $this->findProjectModel($id);
            $model->scenario = EProject::SCENARIO_CREATE;
        } else {
            $model = new EProject();
            $model->scenario = EProject::SCENARIO_CREATE;
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->get('delete')) {
            $message = false;
            if ($model->tryToDelete(function () use ($model) {
                return $this->syncModelToApi($model, true);
            }, $message)) {
                $this->addSuccess(
                    __('Project `{name}` deleted successfully.', ['name' => $model->name])
                );
                return $this->redirect(['project']);
            } else {
                if ($message)
                    $this->addError($message);
            }

            return $this->redirect(['project-edit', 'id' => $model->id]);
        }


        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {

                $this->syncModelToApi($model);

                if ($id) {
                    $this->addSuccess(
                        __('Project `{name}` updated successfully.', [
                            'name' => $model->name
                        ]));
                } else {
                    $this->addSuccess(
                        __('Project `{name}` created successfully.', [
                            'name' => $model->name
                        ]));
                }

                return $this->redirect(['project-edit', 'id' => $model->id]);
            }
        }

        return $this->renderView([
            'model' => $model,
        ]);
    }

    public function actionProjectMeta()
    {
        $project = false;
        if ($p = $this->get('project')) {
            $project = $this->findProjectModel($p);
        }

        if ($id = $this->get('id')) {
            $model = $this->findProjectMetaModel($id);
            $model->scenario = EProjectMeta::SCENARIO_CREATE;
        } else {
            $model = new EProjectMeta();
            $model->scenario = EProjectMeta::SCENARIO_CREATE;
        }
        $model->_project = $project->id;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->get('delete')) {
            if ($this->deleteModelToApi($model)) {
                $this->addSuccess(
                    __('Project Finance Information `{name}` deleted successfully.', ['name' => $project->name])
                );
                return $this->redirect(['project', 'id' => $project->id]);
            }
            return $this->redirect(['project-meta', 'project' => $project->id, 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $this->syncModelToApi($model);

                if ($id) {
                    $this->addSuccess(
                        __('Project Finance Information `{name}` updated successfully.', [
                            'name' => $model->project->name
                        ]));
                } else {
                    $this->addSuccess(
                        __('Project Finance Information `{name}` created successfully.', [
                            'name' => $model->project->name
                        ]));
                }
            }
            return $this->redirect(['project-meta', 'project' => $project->id, 'id' => $model->id]);
        }

        return $this->render('project-meta-edit', [
            'model' => $model,
            'project' => $project,
        ]);
    }

    public function actionProjectMember()
    {
        $project = false;
        if ($p = $this->get('project')) {
            $project = $this->findProjectModel($p);
        }

        if ($id = $this->get('id')) {
            $model = $this->findProjectExecutorModel($id);
            $model->scenario = EProjectExecutor::SCENARIO_CREATE;
        } else {
            $model = new EProjectExecutor();
            $model->scenario = EProjectExecutor::SCENARIO_CREATE;
        }
        $model->_project = $project->id;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->get('delete')) {
            if ($this->deleteModelToApi($model)) {
                $this->addSuccess(
                    __('Project Member Information `{name}` deleted successfully.', ['name' => $project->name])
                );
                return $this->redirect(['project', 'id' => $project->id]);
            }
            return $this->redirect(['project-member', 'project' => $project->id, 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $this->syncModelToApi($model);

                if ($id) {
                    $this->addSuccess(
                        __('Project Member Information `{name}` updated successfully.', [
                            'name' => $model->project->name
                        ]));
                } else {
                    $this->addSuccess(
                        __('Project Member Information `{name}` created successfully.', [
                            'name' => $model->project->name
                        ]));
                }
            }
            return $this->redirect(['project-member', 'project' => $project->id, 'id' => $model->id]);
        }

        return $this->render('project-member-edit', [
            'model' => $model,
            'project' => $project,
        ]);
    }

    public function actionPublicationMethodical()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $searchModel = new EPublicationAuthorMeta();
        $dataProvider = $searchModel->search_methodical($this->getFilterParams());
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            $dataProvider->query->andFilterWhere([
                'e_publication_author_meta._employee' => Yii::$app->user->identity->_employee,
            ]);
        }

        if ($attribute = $this->get('id')) {
            if ($model = EPublicationMethodical::findOne(['id' => $attribute])) {
                return $this->renderAjax('publication-methodical-view', [
                    'model' => $model,
                ]);
            }
        }

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionPublicationMethodicalList($id = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }

        $searchModel = new EPublicationMethodical();
        //$searchModel->search = Yii::$app->user->identity->employee->second_name;
        //if($searchModel->search !=="") {
        $dataProvider = $searchModel->search($this->getFilterParams());
        //  }
        // $searchModel->search = Yii::$app->user->identity->employee->second_name;
        /* $dataProvider->query->andWhere([
              'is_main_author' => 1
               //'authors', Yii::$app->user->identity->employee->second_name
         ]);*/
        //}
        $selectedPublication = EPublicationAuthorMeta::getSelectedPublication(EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL, Yii::$app->user->identity->_employee);
        $list = array();
        foreach ($selectedPublication as $item) {
            $list [$item->_publication_methodical] = $item->_publication_methodical;
        }

        if ($this->get('delete')) {
            if ($id) {
                /**
                 * @var $model EPublicationAuthorMeta
                 */
                if ($model = EPublicationAuthorMeta::findOne($id)) {
                    $publication = $model->publicationMethodical;
                    if ($model->is_main_author == 0) {
                        try {
                            if ($model->delete()) {
                                $publication->setAsShouldBeSynced();

                                $this->addSuccess(
                                    __('Methodical Publication Author Request `{name}` deleted successfully.', ['name' => $model->id])
                                );
                                return $this->redirect(['science/publication-methodical-edit', 'id' => $publication->id]);
                            }
                        } catch (\Exception $e) {
                            $this->addError($e->getMessage());
                        }
                    } else {
                        $this->addInfo(
                            __('The main author cannot be deleted.')
                        );
                    }
                }
            }

            return $this->redirect(['science/publication-methodical-edit', 'id' => $publication]);
        }
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'list' => $list,

        ]);
    }

    public function actionPublicationMethodicalEdit($id = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }

        if ($id) {
            $model = $this->findPublicationMethodicalModel($id);
            $model->scenario = EPublicationMethodical::SCENARIO_CREATE;
            $publication_author = $this->findPublicationAuthorModel($model->id, EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL);
        } else {
            $model = new EPublicationMethodical();
            $model->scenario = EPublicationMethodical::SCENARIO_CREATE;
            $publication_author = new EPublicationAuthorMeta();
        }
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            $model->scenario = EPublicationMethodical::SCENARIO_CREATE_AUTHOR;
            $model->_employee = Yii::$app->user->identity->_employee;
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->get('delete')) {
            if ($model->canBeDeleted()) {
                $message = false;
                if ($model->tryToDelete(function () use ($model) {
                    return $this->syncModelToApi($model, true);
                }, $message, function () use ($model) {
                    $publication_author_delete = $this->findPublicationAuthorModelOne($model->id, EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL, Yii::$app->user->identity->_employee);
                    if ($publication_author_delete->is_main_author == 0) {
                        $publication_author_delete->delete();
                    } else {
                        EPublicationAuthorMeta::deleteAll(
                            ['AND',
                                [
                                    '_publication_type_table' => EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL,
                                    '_publication_methodical' => $model->id,
                                ]
                            ]
                        );
                    }
                    return true;
                })) {
                    $this->addSuccess(
                        __('Methodical Publication `{name}` deleted successfully.', ['name' => $model->name])
                    );
                    return $this->redirect(['publication-methodical']);
                } else {
                    if ($message)
                        $this->addError($message);

                    return $this->redirect(['publication-methodical-edit', 'id' => $model->id]);
                }
            }
        }

        if ($this->checkModelToApi($model)) {
            return $this->redirect(['publication-methodical-edit', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->canBeUpdated()) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if ($model->save()) {
                            if ($publication_author->isNewRecord) {
                                $publication_author->_employee = $model->_employee;
                                $publication_author->_publication_type_table = EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL;
                                $publication_author->_publication_methodical = $model->id;
                                $publication_author->is_checked_by_author = EPublicationAuthorMeta::STATUS_ENABLE;
                                $publication_author->is_main_author = 1;
                                if ($publication_author->save(false)) {
                                    $transaction->commit();

                                    $this->syncModelToApi($model);

                                    if ($id) {
                                        $this->addSuccess(
                                            __('Methodical Publication `{name}` updated successfully.', [
                                                'name' => $model->name
                                            ]));
                                    } else {
                                        $this->addSuccess(
                                            __('Methodical Publication `{name}` created successfully.', [
                                                'name' => $model->name
                                            ]));
                                    }
                                    return $this->redirect(['publication-methodical-edit', 'id' => $model->id]);
                                }
                            } else {
                                $transaction->commit();

                                $this->syncModelToApi($model);
                                if ($id) {
                                    $this->addSuccess(
                                        __('Methodical Publication `{name}` updated successfully.', [
                                            'name' => $model->name
                                        ]));
                                } else {
                                    $this->addSuccess(
                                        __('Methodical Publication `{name}` created successfully.', [
                                            'name' => $model->name
                                        ]));
                                }
                                return $this->redirect(['publication-methodical-edit', 'id' => $model->id]);
                            }

                        }
                    } catch (IntegrityException $exception) {
                        $transaction->rollBack();
                        $this->addError($exception->getMessage(), true, false);
                        $this->addError(__('Error with Methodical Publication'), false);
                    } catch (\Exception $exception) {
                        $transaction->rollBack();
                        $this->addError($exception->getMessage(), true);
                    }
                } else {
                    $this->addInfo(
                        __('The Methodical Publication cannot be edited')
                    );
                }
            }

        }
        return $this->renderView([
            'model' => $model,
        ]);
    }

    public function actionToPublication()
    {
        if (Yii::$app->request->post('selection')) {
            $selection = (array)Yii::$app->request->post('selection');
            $publication_type = Yii::$app->request->post('publication_type');
            foreach ($selection as $id) {
                try {
                    $model = new EPublicationAuthorMeta();
                    $model->_employee = Yii::$app->user->identity->_employee;
                    $model->is_checked_by_author = EPublicationAuthorMeta::STATUS_DISABLE;

                    if ($publication_type == EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL) {
                        $model->_publication_type_table = EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL;
                        $model->_publication_methodical = (int)$id;
                    } elseif ($publication_type == EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC) {
                        $model->_publication_type_table = EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC;
                        $model->_publication_scientific = (int)$id;
                    } elseif ($publication_type == EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY) {
                        $model->_publication_type_table = EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY;
                        $model->_publication_property = (int)$id;
                    }

                    $model->is_main_author = 0;
                    if ($model->save(false)) {
                        $this->addSuccess(__('Request sended to Author'));

                    } else {
                        $e2 = new Exception();
                        if ($e2->getCode() == 0) {
                            $this->addError(__('Publication already in your profile'));
                        } else {
                            $this->addError($e2->getMessage());
                        }
                    }
                } catch (Exception $e) {
                    $this->addError($e->getMessage());
                }
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionPublicationScientifical()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }

        $searchModel = new EPublicationAuthorMeta();
        $dataProvider = $searchModel->search_scientifical($this->getFilterParams());
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            $dataProvider->query->andFilterWhere([
                'e_publication_author_meta._employee' => Yii::$app->user->identity->_employee,
            ]);
        }

        if ($attribute = $this->get('id')) {
            if ($model = EPublicationScientific::findOne(['id' => $attribute])) {
                return $this->renderAjax('publication-scientifical-view', [
                    'model' => $model,
                ]);
            }
        }

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionPublicationScientificalEdit($id = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        if ($id) {
            $model = $this->findPublicationScientificalModel($id);
            $model->scenario = EPublicationScientific::SCENARIO_CREATE;
            $publication_author = $this->findPublicationAuthorModel($model->id, EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC);
        } else {
            $model = new EPublicationScientific();
            $model->scenario = EPublicationScientific::SCENARIO_CREATE;
            $publication_author = new EPublicationAuthorMeta();
        }
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            $model->scenario = EPublicationScientific::SCENARIO_CREATE_AUTHOR;
            $model->_employee = Yii::$app->user->identity->_employee;
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }


        if ($this->checkModelToApi($model)) {
            return $this->redirect(['publication-scientifical-edit', 'id' => $model->id]);
        }

        if ($this->get('delete')) {
            if ($model->canBeDeleted()) {
                $message = false;
                if ($model->tryToDelete(function () use ($model) {
                    return $this->syncModelToApi($model, true);
                }, $message, function () use ($model) {
                    $publication_author_delete = $this->findPublicationAuthorModelOne($model->id, EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL, Yii::$app->user->identity->_employee);
                    if ($publication_author_delete->is_main_author == 0) {
                        $publication_author_delete->delete();
                    } else {
                        EPublicationAuthorMeta::deleteAll(
                            ['AND',
                                [
                                    '_publication_type_table' => EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC,
                                    '_publication_scientific' => $model->id,
                                ]
                            ]
                        );
                    }
                    return true;
                })) {
                    $this->addSuccess(
                        __('Scientifical Publication `{name}` deleted successfully.', ['name' => $model->name])
                    );
                    return $this->redirect(['publication-scientifical']);
                } else {
                    if ($message)
                        $this->addError($message);

                    return $this->redirect(['publication-scientifical-edit', 'id' => $model->id]);
                }
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if (!$model->is_checked) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if ($model->save()) {
                            if ($publication_author->isNewRecord) {
                                $publication_author->_employee = $model->_employee;
                                $publication_author->_publication_type_table = EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC;
                                $publication_author->_publication_scientific = $model->id;
                                $publication_author->is_checked_by_author = EPublicationAuthorMeta::STATUS_ENABLE;
                                $publication_author->is_main_author = 1;
                                if ($publication_author->save(false)) {
                                    $transaction->commit();
                                    $this->syncModelToApi($model);
                                    if ($id) {
                                        $this->addSuccess(
                                            __('Scientifical Publication `{name}` updated successfully.', [
                                                'name' => $model->name
                                            ]));
                                    } else {
                                        $this->addSuccess(
                                            __('Scientifical Publication `{name}` created successfully.', [
                                                'name' => $model->name
                                            ]));
                                    }
                                    return $this->redirect(['publication-scientifical-edit', 'id' => $model->id]);
                                }
                            } else {
                                $transaction->commit();
                                $this->syncModelToApi($model);
                                if ($id) {
                                    $this->addSuccess(
                                        __('Scientifical Publication `{name}` updated successfully.', [
                                            'name' => $model->name
                                        ]));
                                } else {
                                    $this->addSuccess(
                                        __('Scientifical Publication `{name}` created successfully.', [
                                            'name' => $model->name
                                        ]));
                                }
                                return $this->redirect(['publication-scientifical-edit', 'id' => $model->id]);
                            }
                        }
                    } catch (IntegrityException $exception) {
                        $transaction->rollBack();
                        $this->addError($exception->getMessage(), true, false);
                        $this->addError(__('Error with Scientifical Publication'), false);
                    } catch (\Exception $exception) {
                        $transaction->rollBack();
                        $this->addError($exception->getMessage(), true);
                    }
                } else {
                    $this->addInfo(
                        __('The Scientifical Publication cannot be edited')
                    );
                }
            }
        }
        return $this->renderView([
            'model' => $model,
        ]);
    }

    public function actionPublicationScientificalList($id = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $searchModel = new EPublicationScientific();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $selectedPublication = EPublicationAuthorMeta::getSelectedPublication(EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC, Yii::$app->user->identity->_employee);
        $list = array();
        foreach ($selectedPublication as $item) {
            $list [$item->_publication_scientific] = $item->_publication_scientific;
        }

        if ($this->get('delete')) {
            if ($id) {
                /**
                 * @var $model EPublicationAuthorMeta
                 */
                if ($model = EPublicationAuthorMeta::findOne($id)) {
                    $publication = $model->_publication_scientific;
                    if ($model->is_main_author == 0) {
                        try {
                            if ($model->delete()) {
                                $model->publicationScientific->setAsShouldBeSynced();

                                $this->addSuccess(
                                    __('Scientifical Publication Author Request `{name}` deleted successfully.', ['name' => $model->id])
                                );
                                return $this->redirect(['science/publication-scientifical-edit', 'id' => $publication]);
                            }
                        } catch (\Exception $e) {
                            $this->addError($e->getMessage());
                        }
                    } else {
                        $this->addInfo(
                            __('The main author cannot be deleted.')
                        );
                    }
                }
            }
            return $this->redirect(['science/publication-scientifical-edit', 'id' => $publication]);
        }
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'list' => $list,

        ]);
    }

    public function actionPublicationProperty()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $searchModel = new EPublicationAuthorMeta();
        $dataProvider = $searchModel->search_property($this->getFilterParams());
        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            $dataProvider->query->andFilterWhere([
                'e_publication_author_meta._employee' => Yii::$app->user->identity->_employee,
            ]);
        }

        if ($attribute = $this->get('id')) {
            if ($model = EPublicationProperty::findOne(['id' => $attribute])) {
                return $this->renderAjax('publication-property-view', [
                    'model' => $model,
                ]);
            }
        }

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionPublicationPropertyEdit($id = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        if ($id) {
            $model = $this->findPublicationPropertyModel($id);
            $model->scenario = EPublicationProperty::SCENARIO_CREATE;
            $publication_author = $this->findPublicationAuthorModel($model->id, EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY);
        } else {
            $model = new EPublicationProperty();
            $model->scenario = EPublicationProperty::SCENARIO_CREATE;
            $publication_author = new EPublicationAuthorMeta();
        }

        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            $model->scenario = EPublicationProperty::SCENARIO_CREATE_AUTHOR;
            $model->_employee = Yii::$app->user->identity->_employee;
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }


        if ($this->checkModelToApi($model)) {
            return $this->redirect(['publication-property-edit', 'id' => $model->id]);
        }

        if ($this->get('delete')) {
            if ($model->canBeDeleted()) {
                $message = false;
                if ($model->tryToDelete(function () use ($model) {
                    return $this->syncModelToApi($model, true);
                }, $message, function () use ($model) {
                    $publication_author_delete = $this->findPublicationAuthorModelOne($model->id, EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL, Yii::$app->user->identity->_employee);
                    if ($publication_author_delete->is_main_author == 0) {
                        $publication_author_delete->delete();
                    } else {
                        EPublicationAuthorMeta::deleteAll(
                            ['AND',
                                [
                                    '_publication_type_table' => EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY,
                                    '_publication_property' => $model->id,
                                ]
                            ]
                        );
                    }
                    return true;
                })) {
                    $this->addSuccess(
                        __('Property Publication `{name}` deleted successfully.', ['name' => $model->name])
                    );
                    return $this->redirect(['publication-property']);
                } else {
                    if ($message)
                        $this->addError($message);

                    return $this->redirect(['publication-property-edit', 'id' => $model->id]);
                }
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if (!$model->is_checked) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if ($model->save()) {
                            if ($publication_author->isNewRecord) {
                                $publication_author->_employee = $model->_employee;
                                $publication_author->_publication_type_table = EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY;
                                $publication_author->_publication_property = $model->id;
                                $publication_author->is_checked_by_author = EPublicationAuthorMeta::STATUS_ENABLE;
                                $publication_author->is_main_author = 1;
                                if ($publication_author->save(false)) {
                                    $transaction->commit();
                                    $this->syncModelToApi($model);
                                    if ($id) {
                                        $this->addSuccess(
                                            __('Property Publication `{name}` updated successfully.', [
                                                'name' => $model->name
                                            ]));
                                    } else {
                                        $this->addSuccess(
                                            __('Property Publication `{name}` created successfully.', [
                                                'name' => $model->name
                                            ]));
                                    }
                                    return $this->redirect(['publication-property-edit', 'id' => $model->id]);
                                }
                            } else {
                                $transaction->commit();
                                $this->syncModelToApi($model);
                                if ($id) {
                                    $this->addSuccess(
                                        __('Property Publication `{name}` updated successfully.', [
                                            'name' => $model->name
                                        ]));
                                } else {
                                    $this->addSuccess(
                                        __('Property Publication `{name}` created successfully.', [
                                            'name' => $model->name
                                        ]));
                                }
                                return $this->redirect(['publication-property-edit', 'id' => $model->id]);
                            }
                        }
                    } catch (IntegrityException $exception) {
                        $transaction->rollBack();
                        $this->addError($exception->getMessage(), true, false);
                        $this->addError(__('Error with Property Publication'), false);
                    } catch (\Exception $exception) {
                        $transaction->rollBack();
                        $this->addError($exception->getMessage(), true);
                    }
                } else {
                    $this->addInfo(
                        __('The Property Publication cannot be edited')
                    );
                }
            }
        }
        return $this->renderView([
            'model' => $model,
        ]);
    }

    public function actionPublicationPropertyList($id = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $searchModel = new EPublicationProperty();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $selectedPublication = EPublicationAuthorMeta::getSelectedPublication(EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY, Yii::$app->user->identity->_employee);
        $list = array();
        foreach ($selectedPublication as $item) {
            $list [$item->_publication_property] = $item->_publication_property;
        }

        if ($this->get('delete')) {
            if ($id) {
                /**
                 * @var $model EPublicationAuthorMeta
                 */
                if ($model = EPublicationAuthorMeta::findOne($id)) {
                    if ($model->is_main_author == 0) {
                        try {
                            if ($model->delete()) {
                                $model->publicationProperty->setAsShouldBeSynced();

                                $this->addSuccess(
                                    __('Property PublicationProperty Publication Author Request `{name}` deleted successfully.', ['name' => $model->id])
                                );
                                return $this->redirect(['science/publication-property-edit', 'id' => $model->_publication_property]);
                            }
                        } catch (\Exception $e) {
                            $this->addError($e->getMessage());
                        }
                    } else {
                        $this->addInfo(
                            __('The main author cannot be deleted.')
                        );
                    }
                }
            }
            return $this->redirect(['science/publication-property-edit', 'id' => $publication]);
        }
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'list' => $list,

        ]);
    }

    public function actionPublicationMethodicalCheck($id = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the department only.')
            );
            return $this->goHome();
        }
        $searchModel = new EPublicationAuthorMeta();
        if ($this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            if (Yii::$app->user->identity->employee->headDepartments) {
                $department = Yii::$app->user->identity->employee->headDepartments->id;
                //$dataProvider->query->andFilterWhere(['_department' => $department]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        $dataProvider = $searchModel->search_methodical_department($this->getFilterParams(), $department);

        if ($attribute = $this->get('attribute')) {
            if ($model = EPublicationMethodical::findOne(['id' => $this->get('publication')])) {
                $model->$attribute = !$model->$attribute;
                if ($model->save(false)) {
                    if ($model->$attribute == EPublicationMethodical::STATUS_ENABLE) {
                        $this->addSuccess(__('Item [{id}] of Methodical Publication is enabled', ['id' => $model->id]), true, true);
                    } else {
                        $this->addSuccess(__('Item [{id}] of Methodical Publication is disabled', ['id' => $model->id]), true, true);
                    }

                    $this->syncModelToApi($model);

                    //Yii::$app->response->format = Response::FORMAT_JSON;
                    //return [];
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
        }
        if ($attribute = $this->get('id')) {
            if ($model = EPublicationMethodical::findOne(['id' => $attribute])) {
                return $this->renderAjax('publication-methodical-view', [
                    'model' => $model,
                ]);
            }
        }
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionPublicationScientificalCheck()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the department only.')
            );
            return $this->goHome();
        }

        $searchModel = new EPublicationAuthorMeta();
        if ($this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            if (Yii::$app->user->identity->employee->headDepartments) {
                $department = Yii::$app->user->identity->employee->headDepartments->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        $dataProvider = $searchModel->search_scientifical_department($this->getFilterParams(), $department);

        if ($attribute = $this->get('attribute')) {
            if ($model = EPublicationScientific::findOne(['id' => $this->get('publication')])) {
                $model->$attribute = !$model->$attribute;
                if ($model->save(false)) {
                    if ($model->$attribute == EPublicationScientific::STATUS_ENABLE) {
                        $this->addSuccess(__('Item [{id}] of Scientifical Publication is enabled', ['id' => $model->id]), true, true);
                    } else {
                        $this->addSuccess(__('Item [{id}] of Scientifical Publication is disabled', ['id' => $model->id]), true, true);
                    }
                    $this->syncModelToApi($model);

                    //Yii::$app->response->format = Response::FORMAT_JSON;
                    //return [];
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
        }

        if ($attribute = $this->get('id')) {
            if ($model = EPublicationScientific::findOne(['id' => $attribute])) {
                return $this->renderAjax('publication-scientifical-view', [
                    'model' => $model,
                ]);
            }
        }

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionPublicationPropertyCheck()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the department only.')
            );
            return $this->goHome();
        }
        $searchModel = new EPublicationAuthorMeta();
        if ($this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            if (Yii::$app->user->identity->employee->headDepartments) {
                $department = Yii::$app->user->identity->employee->headDepartments->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        $dataProvider = $searchModel->search_property_department($this->getFilterParams(), $department);

        if ($attribute = $this->get('attribute')) {
            if ($model = EPublicationProperty::findOne(['id' => $this->get('publication')])) {
                $model->$attribute = !$model->$attribute;
                if ($model->save(false)) {
                    if ($model->$attribute == EPublicationProperty::STATUS_ENABLE) {
                        $this->addSuccess(__('Item [{id}] of Property Publication is enabled', ['id' => $model->id]), true, true);
                    } else {
                        $this->addSuccess(__('Item [{id}] of Property Publication is disabled', ['id' => $model->id]), true, true);
                    }
                    $this->syncModelToApi($model);
                    //Yii::$app->response->format = Response::FORMAT_JSON;
                    //return [];
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
        }
        if ($attribute = $this->get('id')) {
            if ($model = EPublicationProperty::findOne(['id' => $attribute])) {
                return $this->renderAjax('publication-property-view', [
                    'model' => $model,
                ]);
            }
        }

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionSpecialty()
    {
        $this->activeMenu = 'doctorate';
        $searchModel = new ESpecialty();
        $dataProvider = $searchModel->search_doctorate($this->getFilterParams());

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }


    public function actionSpecialtyEdit($id = false)
    {
        $this->activeMenu = 'doctorate';
        if ($id) {
            $model = $this->findSpecialModel($id);
        } else {
            $model = new ESpecialty();
        }

        $model->scenario = ESpecialty::SCENARIO_HIGHER_DOCTORATE;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->get('delete')) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(
                        __('Specialty `{name}` deleted successfully.', [
                            'name' => $model->code
                        ])
                    );
                    return $this->redirect(['specialty']);
                }
            } catch (Exception $e) {
                if ($e->getCode() == 23503) {
                    $this->addError(__('Could not delete related data'));
                } else {
                    $this->addError($e->getMessage());
                }
            }
            return $this->redirect(['special']);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                if ($id) {
                    $this->addSuccess(
                        __('Specialty `{name}` updated successfully.', [
                            'name' => $model->code
                        ]));
                } else {
                    $this->addSuccess(
                        __('Specialty `{name}` created successfully.', [
                            'name' => $model->code
                        ]));
                }
                return $this->redirect(['specialty']);
            }
        }

        return $this->renderView([
            'model' => $model,
        ]);
    }

    protected function getDefense($id = false)
    {
        return EDissertationDefense::findCurrentDissertationDefense($id);
    }

    public function actionDoctorateStudent($id = false)
    {
        $this->activeMenu = 'doctorate';
        if ($id) {
            if ($model = $this->findDoctorateStudentModel($id)) {
                $defense = $this->getDefense($model->id);
                /*if ($defense === null) {
                    $defense = new EDissertationDefense();
                }*/
            }

            if ($this->checkModelToApi($model)) {
                return $this->redirect(['doctorate-student', 'id' => $model->id]);
            }

            return $this->render('doctorate-student-view', [
                'model' => $model,
                'defense' => @$defense,
            ]);
        }

        $searchModel = new EDoctorateStudent();
        $dataProvider = $searchModel->search($this->getFilterParams());

        return $this->render('doctorate-student', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionDoctorateStudentEdit($id = false)
    {
        $this->activeMenu = 'doctorate';
        $passport = $this->get('passport');
        $pin = $this->get('pin');
        if ($this->get('pin_hint')) {
            return $this->renderFile('@backend/views/layouts/pin.php');
        }

        if ($pin && $passport) {
            $result = [];
            if (HEMIS_INTEGRATION) {
                try {
                    if ($data = HemisApi::getApiClient()->getPassportData($passport, $pin)) {
                        $result['success'] = true;
                        $result['first_name'] = $data->name_latin;
                        $result['second_name'] = $data->surname_latin;
                        $result['third_name'] = $data->patronym_latin;
                        $result['birth_date'] = $data->birth_date;
                        $result['gender'] = $data->sex == 1 ? Gender::GENDER_MALE : Gender::GENDER_FEMALE;
                    }
                } catch (HemisApiError $e) {
                    $result['success'] = false;
                    $result['manual'] = false;
                    $result['error'] = __($e->getMessage());
                } catch (\Exception $e) {
                    $result['success'] = false;
                    $result['manual'] = true;
                    $result['error'] = __('Server bilan ulanishda xatolik vujudga keldi, ma\'lumotlarni kiritishda davom eting');
                }
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        }

        if ($id) {
            $model = $this->findDoctorateStudentModel($id);
            $model->scenario = EDoctorateStudent::SCENARIO_INSERT;
        } else {
            $model = new EDoctorateStudent();
            $model->scenario = EDoctorateStudent::SCENARIO_INSERT;
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }


        if ($this->get('delete')) {
            $message = false;
            if ($model->tryToDelete(function () use ($model) {
                return $this->syncModelToApi($model, true);
            }, $message)) {
                $this->addSuccess(
                    __('Doctorate Student Information `{name}` deleted successfully.', ['name' => $model->fullName])
                );
                return $this->redirect(['doctorate-student']);
            } else {
                if ($message)
                    $this->addError($message);
            }

            return $this->redirect(['doctorate-student-edit', 'id' => $model->id]);
        }

        $oldId = $model->student_id_number;
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $this->syncModelToApi($model);

                if ($id) {
                    $this->addSuccess(
                        __('Doctorate Student `{name}` updated successfully.', [
                            'name' => $model->getFullName()
                        ]));
                } else {
                    $this->addSuccess(
                        __('Doctorate Student `{name}` created successfully.', [
                            'name' => $model->getFullName()
                        ]));
                }
                if ($model->student_id_number && $oldId == null) {
                    $this->addSuccess(__('Student synced to HEMIS API and generated id {b}{student_id_number}{/b}', ['student_id_number' => $model->student_id_number]));
                }

                return $this->redirect(['doctorate-student-edit', 'id' => $model->id]);
            }
        } else {
            if ($model->isNewRecord) {
                $model->_citizenship = '11';
                $model->_gender = '11';
            }
        }

        return $this->renderView([
            'model' => $model,
        ]);
    }

    public function actionDoctorateStudentPassportEdit($id)
    {
        $this->activeMenu = 'doctorate';
        $passport = $this->get('passport');
        $pin = $this->get('pin');
        if ($this->get('pin_hint')) {
            return $this->renderFile('@backend/views/layouts/pin.php');
        }

        if ($pin && $passport) {
            $result = [];
            if (HEMIS_INTEGRATION) {
                try {
                    if ($data = HemisApi::getApiClient()->getPassportData($passport, $pin)) {
                        $result['success'] = true;
                        $result['first_name'] = $data->name_latin;
                        $result['second_name'] = $data->surname_latin;
                        $result['third_name'] = $data->patronym_latin;
                        $result['birth_date'] = $data->birth_date;
                        $result['gender'] = $data->sex == 1 ? Gender::GENDER_MALE : Gender::GENDER_FEMALE;
                    }
                } catch (HemisApiError $e) {
                    $result['success'] = false;
                    $result['manual'] = false;
                    $result['error'] = __($e->getMessage());
                } catch (\Exception $e) {
                    $result['success'] = false;
                    $result['manual'] = true;
                    $result['error'] = __('Server bilan ulanishda xatolik vujudga keldi, ma\'lumotlarni kiritishda davom eting');
                }
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        }

        $model = $this->findDoctorateStudentModel($id);
        $model->scenario = EDoctorateStudent::SCENARIO_INSERT;
        $model->scenario_edit_passport = true;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        $oldId = $model->student_id_number;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->syncModelToApi($model);

            $this->addSuccess(
                __(
                    'Passport information of `{name}` doctorate student updated successfully.',
                    [
                        'name' => $model->getFullName(),
                    ]
                )
            );

            if ($model->student_id_number && $oldId === null) {
                $this->addSuccess(__('Student synced to HEMIS API and generated id {b}{student_id_number}{/b}', ['student_id_number' => $model->student_id_number]));
            }

            return $this->redirect(['doctorate-student-passport-edit', 'id' => $model->id]);
        }

        return $this->renderView([
            'model' => $model,
        ]);
    }

    public function actionDissertationDefenseEdit($id = false)
    {
        $this->activeMenu = 'doctorate';
        $doctorate = EDoctorateStudent::findOne($id);
        $model = EDissertationDefense::findOne(['_doctorate_student' => $id]);
        if ($model === null) {
            $model = new EDissertationDefense();
        }

        $model->scenario = EDissertationDefense::SCENARIO_CREATE;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->checkModelToApi($model)) {
            return $this->redirect(['dissertation-defense-edit', 'id' => $doctorate->id]);
        }


        if ($this->get('delete')) {
            $message = false;
            if ($model->tryToDelete(function () use ($model) {
                return $this->syncModelToApi($model, true);
            }, $message)) {
                $this->addSuccess(
                    __('Dissertation Defense `{name}` deleted successfully.', ['name' => $model->diploma_number])
                );
                return $this->redirect(['doctorate-student', 'id' => $doctorate->id]);
            } else {
                if ($message)
                    $this->addError($message);
            }

            return $this->redirect(['doctorate-student', 'id' => $doctorate->id]);
        }


        if ($model->load(Yii::$app->request->post())) {
            $model->_doctorate_student = $doctorate->id;
            $model->_science_branch_id = $doctorate->_science_branch_id;
            $model->_specialty_id = $doctorate->_specialty_id;
            if ($model->save()) {

                $this->syncModelToApi($model);

                if ($id) {
                    $this->addSuccess(
                        __('Dissertation Defense `{name}` updated successfully.', [
                            'name' => $model->diploma_number
                        ]));
                } else {
                    $this->addSuccess(
                        __('Dissertation Defense `{name}` created successfully.', [
                            'name' => $model->diploma_number
                        ]));
                }

                return $this->redirect(['dissertation-defense-edit', 'id' => $doctorate->id]);
            }
        }

        return $this->renderView([
            'model' => $model,
            'doctorate' => $doctorate,
        ]);
    }

    public function actionPublicationCriteria()
    {
        $this->activeMenu = 'rating';
        $model = new EPublicationCriteria();
        //$model->scenario = EPublicationCriteria::SCENARIO_CREATE;
        $searchModel = new EPublicationCriteria();
        $dataProvider = $searchModel->search($this->getFilterParams());

        if ($code = $this->get('code')) {
            if ($model = EPublicationCriteria::findOne(['id' => $code])) {
                if ($model->_publication_type_table == EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL) {
                    $model->_publication_methodical_type = $model->_publication_methodical_type;
                } elseif ($model->_publication_type_table == EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC) {
                    $model->_publication_methodical_type = $model->_publication_scientific_type;
                } elseif ($model->_publication_type_table == EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY) {
                    $model->_publication_methodical_type = $model->_publication_property_type;
                    //$searchModel->_publication_type_table = EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY;
                    //$dataProvider->query->andFilterWhere(['_publication_type_table' => EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY]);
                }
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Publication Criteria [{code}] is deleted successfully', ['code' => $model->id]));

                            return $this->redirect(['science/publication-criteria']);
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['science/publication-criteria', 'code' => $model->id]);
                }
            } else {
                return $this->redirect(['science/publication-criteria']);
            }
        }

        if ($code2 = $this->get('import')) {
            $searchModel = new ECriteriaTemplate();
            $dataProvider = $searchModel->search($this->getFilterParams());
            $dataProvider->query->andFilterWhere([
                'in', '_publication_type_table', [ECriteriaTemplate::PUBLICATION_TYPE_METHODICAL, ECriteriaTemplate::PUBLICATION_TYPE_SCIENTIFIC, ECriteriaTemplate::PUBLICATION_TYPE_PROPERTY]
            ]);
            $dataProvider->query->andFilterWhere([
                'active' => ECriteriaTemplate::STATUS_ENABLE,
            ]);
            $searchModelFix = new EPublicationCriteria();
            return $this->renderAjax('publication-criteria-import', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'searchModelFix' => $searchModelFix,
            ]);
        }

        $model->scenario = EPublicationCriteria::SCENARIO_CREATE;
        if ($model->load(Yii::$app->request->post())) {
            $publication_type = $model->_publication_methodical_type;
            if ($model->_publication_type_table == EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL) {
                $model->_publication_methodical_type = $publication_type;
                $model->_publication_scientific_type = null;
                $model->_publication_property_type = null;
            } elseif ($model->_publication_type_table == EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC) {
                $model->_publication_scientific_type = $publication_type;
                $model->_publication_methodical_type = null;
                $model->_publication_property_type = null;
            } elseif ($model->_publication_type_table == EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY) {
                $model->_publication_property_type = $publication_type;
                $model->_publication_methodical_type = null;
                $model->_publication_scientific_type = null;
            }

            if ($model->save()) {
                if ($code) {
                    $this->addSuccess(__('Publication Criteria [{code}] updated successfully', ['code' => $model->id]));
                } else {
                    $this->addSuccess(__('Publication Criteria [{code}] created successfully', ['code' => $model->id]));
                }
            }
            // $model = new EducationYear();
        }
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionToImportPublication()
    {
        $selection = (array)Yii::$app->request->post('selection');
        $_education_year = EducationYear::findOne((int)Yii::$app->request->post('education_year'));
        if (is_array($selection) && $_education_year) {
            $success = 0;
            foreach ($selection as $id) {
                $new_model = new EPublicationCriteria();
                $model = ECriteriaTemplate::findOne((int)$id);

                $new_model->_education_year = $_education_year->code;
                $new_model->_publication_type_table = $model->_publication_type_table;
                $new_model->_publication_methodical_type = $model->_publication_methodical_type;
                $new_model->_publication_scientific_type = $model->_publication_scientific_type;
                $new_model->_publication_property_type = $model->_publication_property_type;
                $new_model->_in_publication_database = $model->_in_publication_database;
                $new_model->exist_certificate = $model->exist_certificate;
                $new_model->mark_value = $model->mark_value;
                if ($new_model->save()) {
                    $success++;
                }
            }

            if ($success)
                $this->addSuccess(__('{count} criteria assigned to publication', ['count' => $success]));

            return $this->redirect(Yii::$app->request->referrer);
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionToImportActivity()
    {
        $selection = (array)Yii::$app->request->post('selection');
        $_education_year = EducationYear::findOne((int)Yii::$app->request->post('education_year'));
        if (is_array($selection) && $_education_year) {
            $success = 0;
            foreach ($selection as $id) {
                $new_model = new EScientificPlatformCriteria();
                $model = ECriteriaTemplate::findOne((int)$id);
                $new_model->_education_year = $_education_year->code;
                $new_model->_publication_type_table = $model->_publication_type_table;
                $new_model->_scientific_platform = $model->_scientific_platform;
                $new_model->_criteria_type = $model->_criteria_type;
                $new_model->mark_value = $model->mark_value;
                $new_model->coefficient = $model->coefficient;
                if ($new_model->save()) {
                    $success++;
                }
            }

            if ($success)
                $this->addSuccess(__('{count} criteria assigned to publication', ['count' => $success]));

            return $this->redirect(Yii::$app->request->referrer);
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionTeacherRating()
    {
        $this->activeMenu = 'rating';
        $searchModel = new FilterForm();
        $faculty = "";
        $department = "";
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

        if ($this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            if (Yii::$app->user->identity->employee->headDepartments) {
                $department = Yii::$app->user->identity->employee->headDepartments->id;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        $result = array();
        $result_m = array();
        $result_s = array();
        $result_p = array();
        if ($searchModel->load(Yii::$app->request->get())) {
            if ($searchModel->_faculty)
                $faculty = $searchModel->_faculty;
            if ($searchModel->_department)
                $department = $searchModel->_department;
            // $department_list = EDepartment::find()->select('id')->where(['_structure_type' => StructureType::STRUCTURE_TYPE_FACULTY, 'active'=>EDepartment::STATUS_ENABLE])->orderBy(['name'=>SORT_ASC])->all();

            $methodical_criterias = EPublicationCriteria::getPublicationCriteria(EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL, $searchModel->_education_year);
            $scientifical_criterias = EPublicationCriteria::getPublicationCriteria(EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC, $searchModel->_education_year);
            $property_criterias = EPublicationCriteria::getPublicationCriteria(EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY, $searchModel->_education_year);
            $activity_criterias = EScientificPlatformCriteria::getPublicationCriteria(ECriteriaTemplate::PUBLICATION_TYPE_ACTIVITY, $searchModel->_education_year);

            if ($code = $this->get('_employee')) {
                $result_methodical = array();
                $result_scientifical = array();
                $result_property = array();
                $result_activity = array();
                $gen_res = array();

                if ($searchModel->_faculty)
                    $faculty = $searchModel->_faculty;
                if ($searchModel->_department)
                    $department = $searchModel->_department;

                $employee = EEmployee::findOne(['id' => $code]);
                $methodical_one_employee = EPublicationAuthorMeta::getEmployeeCheckedPublicationList(EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL, $searchModel->_education_year, $employee->id);
                $scientifical_one_employee = EPublicationAuthorMeta::getEmployeeCheckedPublicationList(EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC, $searchModel->_education_year, $employee->id);
                $property_one_employee = EPublicationAuthorMeta::getEmployeeCheckedPublicationList(EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY, $searchModel->_education_year, $employee->id);
                $activity_one_employee = EScientificPlatformProfile::getEmployeeCheckedProfileList($searchModel->_education_year, $employee->id);


                foreach ($methodical_one_employee as $item) {
                    foreach ($methodical_criterias as $criteria) {
                        if ($item->_methodical_publication_type == $criteria->_publication_methodical_type) {
                            if (isset($criteria->mark_value)) {
                                @$result_methodical[$item->id . '_m']['name'] = $criteria->publicationMethodicalType->name;
                                @$result_methodical[$item->id . '_m']['work_name'] = $item->work_name;
                                @$result_methodical[$item->id . '_m']['work_type'] = EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL;

                                if ($criteria->exist_certificate == 1) {
                                    if (!empty($item->certificate_number)) {
                                        @$result_methodical[$item->id . '_m']['mark'] = $criteria->mark_value;
                                    }
                                } else {
                                    if (empty($item->certificate_number)) {
                                        @$result_methodical[$item->id . '_m']['mark'] = $criteria->mark_value;
                                    } else {
                                        @$result_methodical[$item->id . '_m']['mark'] = 0;
                                    }
                                }


                            }
                        }
                    }
                }

                foreach ($scientifical_one_employee as $item) {
                    foreach ($scientifical_criterias as $criteria) {
                        if ($item->_scientific_publication_type == $criteria->_publication_scientific_type) {
                            if (isset($criteria->mark_value)) {
                                @$result_scientifical[$item->id . '_s']['name'] = $criteria->publicationScientificType->name;
                                @$result_scientifical[$item->id . '_s']['work_name'] = $item->work_name;
                                @$result_scientifical[$item->id . '_s']['work_type'] = EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC;
                                if ($criteria->_in_publication_database == 1) {
                                    if ($item->_publication_database !== PublicationDatabase::PUBLICATION_DATABASE_OTHER) {
                                        @$result_scientifical[$item->id . '_s']['mark'] = $criteria->mark_value;
                                    }
                                } else {
                                    if ($item->_publication_database === PublicationDatabase::PUBLICATION_DATABASE_OTHER) {
                                        @$result_scientifical[$item->id . '_s']['mark'] = $criteria->mark_value;
                                    } else {
                                        @$result_scientifical[$item->id . '_s']['mark'] = 0;
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($property_one_employee as $item) {
                    foreach ($property_criterias as $criteria) {
                        if ($item->_patient_type == $criteria->_publication_property_type) {
                            if (isset($criteria->mark_value)) {
                                @$result_property[$item->id . '_p']['name'] = $criteria->publicationPropertyType->name;
                                @$result_property[$item->id . '_p']['work_name'] = $item->work_name;
                                @$result_property[$item->id . '_p']['work_type'] = EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY;
                                /*if($criteria->_in_publication_database == 1) {
                                    if($item->_publication_database !== PublicationDatabase::PUBLICATION_DATABASE_OTHER) {
                                        @$result_property[$item->id.'_p']['mark'] = $criteria->mark_value;
                                    }
                                }
                                else {
                                    if($item->_publication_database === PublicationDatabase::PUBLICATION_DATABASE_OTHER) {
                                        @$result_property[$item->id.'_p']['mark'] = $criteria->mark_value;
                                    }
                                }*/
                                @$result_property[$item->id . '_p']['mark'] = $criteria->mark_value;
                                /*if($criteria->_in_publication_database == 1) {
                                    if($item->_publication_database !== PublicationDatabase::PUBLICATION_DATABASE_OTHER) {
                                        @$result_property[$item->_patient_type.'_p']['mark'] = $item->patient_types * $criteria->mark_value;
                                    }
                                }
                                else {
                                    if($item->_publication_database == PublicationDatabase::PUBLICATION_DATABASE_OTHER) {
                                        @$result_property[$item->_patient_type.'_p']['mark'] = $item->patient_types * $criteria->mark_value;
                                    }
                                    else
                                        @$result_property[$item->_patient_type.'_p']['mark'] = $item->patient_types * $criteria->mark_value;
                                }*/
                            }
                        }
                    }
                }

                foreach ($activity_one_employee as $item) {
                    foreach ($activity_criterias as $criteria) {
                        if ($item->_scientific_platform == $criteria->_scientific_platform) {
                            if (isset($criteria->mark_value)) {
                                @$result_activity[$item->_scientific_platform . '_a']['name'] = __('Scientific Activity');
                                @$result_activity[$item->_scientific_platform . '_a']['work_name'] = $item->scientificPlatform->name;
                                @$result_activity[$item->_scientific_platform . '_a']['work_type'] = ECriteriaTemplate::PUBLICATION_TYPE_ACTIVITY;
                                if ($criteria->_criteria_type == EScientificPlatformCriteria::PLATFORM_CRITERIA_HINDEX) {
                                    if (!empty($item->h_index)) {
                                        @$result_activity[$item->_scientific_platform . '_a']['mark'] += $item->h_index * $criteria->mark_value;
                                    }
                                }
                                if ($criteria->_criteria_type == EScientificPlatformCriteria::PLATFORM_CRITERIA_CITATION) {
                                    if ((!empty($item->publication_work_count) && $item->publication_work_count > 0) && !empty($item->citation_count)) {
                                        @$result_activity[$item->_scientific_platform . '_a']['mark'] += round($item->citation_count / $item->publication_work_count * $criteria->mark_value, 0);
                                        //@$result_activity[$item->_scientific_platform.'_a']['mark'] += $item->citation_count;
                                    }
                                }
                                //@$result_activity[$item->_scientific_platform.'_a']['mark'] = @$res1 + @$res2;
                            }
                        }
                    }
                }

                $gen_res = $result_methodical + $result_scientifical + $result_property + $result_activity;
                // $gen_res =  $result_property;
                //      $gen_res = array_combine(array_combine($result_methodical, $result_scientifical),$result_property);
                /*    $gen_res = array_map(function($item) {
                        return array_combine(['name', 'count', 'mark'], $item);
                    }, array_map(null, $result_methodical, $result_scientifical, $result_property));
    */
                /*$gen_res = array_map(function ($name, $count, $mark) {
                     return array_combine(
                         ['name', 'count', 'mark'],
                         [$name, $count, $mark]
                     );
                 }, $result_methodical, $result_scientifical, $result_property);

 */
                $dataProvider = new ArrayDataProvider([
                    'allModels' => @$gen_res,
                    'sort' => [
                        'attributes' => [

                            'work_type' => [
                                'header' => __('Publication'),
                                //'default' => SORT_DESC,
                            ],
                            'name' => [
                                'header' => __('Publication Type'),
                                //'default' => SORT_DESC,
                            ],
                            'work_name' => [
                                'header' => __('Work Name'),
                            ],
                            'mark' => [
                                'header' => __('Mark'),
                                // 'asc' => ['mark' => SORT_ASC],
                                //  'desc' => ['mark' => SORT_DESC],
                                // 'default' => SORT_ASC,
                            ],
                        ],
                        'defaultOrder' => [
                            'work_type' => SORT_ASC
                        ]
                    ],
                    'pagination' => [
                        'pageSize' => 20,
                    ],
                ]);


                return $this->renderAjax('teacher-rating-info', [
                    'employee' => $employee,
                    'dataProvider' => @$dataProvider,
                ]);

            }


            $methodical = EPublicationAuthorMeta::getCheckedPublication(EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL, $searchModel->_education_year, $faculty, $department, "");
            $scientifical = EPublicationAuthorMeta::getCheckedPublication(EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC, $searchModel->_education_year, $faculty, $department, "");
            $property = EPublicationAuthorMeta::getCheckedPublication(EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY, $searchModel->_education_year, $faculty, $department, "");
            $activity = EScientificPlatformProfile::getCheckedPlatform($searchModel->_education_year, $faculty, $department, "");
            foreach ($methodical as $item) {
                foreach ($methodical_criterias as $criteria) {
                    if ($item->_methodical_publication_type == $criteria->_publication_methodical_type) {
                        if (isset($criteria->mark_value)) {
                            @$result[$item->_employee]['employee'] = $item->employee->fullName;
                            @$result[$item->_employee]['_employee'] = $item->_employee;
                            if ($criteria->exist_certificate == 1) {
                                if (!empty($item->certificate_number)) {
                                    @$result[$item->_employee]['mark'] += $item->methodical_publication_types * $criteria->mark_value;
                                }
                            } else {
                                if (empty($item->certificate_number)) {
                                    @$result[$item->_employee]['mark'] += $item->methodical_publication_types * $criteria->mark_value;
                                } else {
                                    @$result[$item->_employee]['mark'] += 0;
                                }
                            }
                        }
                    }
                }
            }

            foreach ($scientifical as $item) {
                foreach ($scientifical_criterias as $criteria) {
                    if ($item->_scientific_publication_type == $criteria->_publication_scientific_type) {
                        if (isset($criteria->mark_value)) {
                            @$result[$item->_employee]['employee'] = $item->employee->fullName;
                            @$result[$item->_employee]['_employee'] = $item->_employee;
                            if ($criteria->_in_publication_database == 1) {
                                if ($item->_publication_database !== PublicationDatabase::PUBLICATION_DATABASE_OTHER) {
                                    @$result[$item->_employee]['mark'] += $item->scientific_publication_types * $criteria->mark_value;
                                }
                            } else {
                                if ($item->_publication_database === PublicationDatabase::PUBLICATION_DATABASE_OTHER) {
                                    @$result[$item->_employee]['mark'] += $item->scientific_publication_types * $criteria->mark_value;
                                } else {
                                    @$result[$item->_employee]['mark'] += 0;
                                }
                            }
                        }
                    }
                }
            }

            foreach ($property as $item) {
                foreach ($property_criterias as $criteria) {
                    if ($item->_patient_type == $criteria->_publication_property_type) {
                        if (isset($criteria->mark_value)) {
                            @$result[$item->_employee]['employee'] = $item->employee->fullName;
                            @$result[$item->_employee]['_employee'] = $item->_employee;
                            /*if($criteria->_in_publication_database == 1) {
                                if($item->_publication_database !== PublicationDatabase::PUBLICATION_DATABASE_OTHER) {
                                    @$result[$item->_employee]['mark'] += $item->patient_types * $criteria->mark_value;
                                }
                            }
                            else {
                                if($item->_publication_database === PublicationDatabase::PUBLICATION_DATABASE_OTHER) {
                                    @$result[$item->_employee]['mark'] += $item->patient_types * $criteria->mark_value;
                                }
                            }*/
                            @$result[$item->_employee]['mark'] += $item->patient_types * $criteria->mark_value;
                        }
                    }
                }
            }

            foreach ($activity as $item) {
                foreach ($activity_criterias as $criteria) {
                    if ($item->_scientific_platform == $criteria->_scientific_platform) {
                        if (isset($criteria->mark_value)) {
                            @$result[$item->_employee]['employee'] = $item->employee->fullName;
                            @$result[$item->_employee]['_employee'] = $item->_employee;

                            if ($criteria->_criteria_type == EScientificPlatformCriteria::PLATFORM_CRITERIA_HINDEX) {
                                if (!empty($item->h_index)) {
                                    @$result[$item->_employee]['mark'] += $item->h_index * $criteria->mark_value;
                                }
                            }
                            if ($criteria->_criteria_type == EScientificPlatformCriteria::PLATFORM_CRITERIA_CITATION) {
                                if ((!empty($item->publication_work_count) && $item->publication_work_count > 0) && !empty($item->citation_count)) {
                                    @$result[$item->_employee]['mark'] += round($item->citation_count / $item->publication_work_count * $criteria->mark_value, 0);
                                    //@$result_activity[$item->_scientific_platform.'_a']['mark'] += $item->citation_count;
                                }
                            }


                            //@$result[$item->_employee]['mark'] += $item->patient_types * $criteria->mark_value;
                        }
                    }
                }
            }

            $data = $result;

            $dataProvider = new ArrayDataProvider([
                'allModels' => $data,
                'sort' => [
                    //     'defaultOrder' => ['mark' => SORT_ASC],
                    'attributes' => [
                        // 'mark',
                        'employee' => [

                        ],
                        'mark' => [
                            'asc' => ['mark' => SORT_ASC],
                            'desc' => ['mark' => SORT_DESC],
                            'default' => SORT_ASC,
                        ],
                    ],
                    'defaultOrder' => [
                        'mark' => SORT_DESC
                    ]
                ],
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'department_list' => @$department_list,
            'faculty' => $faculty,
            'dataProvider' => @$dataProvider,


        ]);
    }

    public function actionDepartmentRating()
    {
        $this->activeMenu = 'rating';
        $searchModel = new FilterForm();
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

        $result = array();
        $result2 = array();
        if ($searchModel->load(Yii::$app->request->get())) {
            if ($searchModel->_faculty)
                $faculty = $searchModel->_faculty;
            $department_list = EDepartment::getDepartmentList($faculty);
            $departments = array();
            foreach ($department_list as $item) {
                $departments[$item->id] = $item->id;
            }
            $teacher_list = EEmployeeMeta::getTeacherList($departments);
            $methodical_criterias = EPublicationCriteria::getPublicationCriteria(EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL, $searchModel->_education_year);
            $scientifical_criterias = EPublicationCriteria::getPublicationCriteria(EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC, $searchModel->_education_year);
            $property_criterias = EPublicationCriteria::getPublicationCriteria(EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY, $searchModel->_education_year);
            $activity_criterias = EScientificPlatformCriteria::getPublicationCriteria(ECriteriaTemplate::PUBLICATION_TYPE_ACTIVITY, $searchModel->_education_year);

            $methodical = EPublicationAuthorMeta::getCheckedPublication(EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL, $searchModel->_education_year, $faculty, "", "");
            $scientifical = EPublicationAuthorMeta::getCheckedPublication(EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC, $searchModel->_education_year, $faculty, "", "");
            $property = EPublicationAuthorMeta::getCheckedPublication(EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY, $searchModel->_education_year, $faculty, "", "");
            $activity = EScientificPlatformProfile::getCheckedPlatform($searchModel->_education_year, $faculty, "", "");

            foreach ($methodical as $item) {
                foreach ($methodical_criterias as $criteria) {
                    if ($item->_methodical_publication_type == $criteria->_publication_methodical_type) {
                        if (isset($criteria->mark_value)) {
                            //$result[$item->_employee]['employee'] =  $item->employee->fullName;
                            if ($criteria->exist_certificate == 1) {
                                if (!empty($item->certificate_number)) {
                                    @$result[$item->_employee]['mark'] += $item->methodical_publication_types * $criteria->mark_value;
                                }
                            } else {
                                if (empty($item->certificate_number)) {
                                    @$result[$item->_employee]['mark'] += $item->methodical_publication_types * $criteria->mark_value;
                                } else
                                    @$result[$item->_employee]['mark'] += 0;
                            }
                        }
                    }
                }
            }

            foreach ($scientifical as $item) {
                foreach ($scientifical_criterias as $criteria) {
                    if ($item->_scientific_publication_type == $criteria->_publication_scientific_type) {
                        if (isset($criteria->mark_value)) {
                            if ($criteria->_in_publication_database == 1) {
                                if ($item->_publication_database != '10') {
                                    @$result[$item->_employee]['mark'] += $item->scientific_publication_types * $criteria->mark_value;
                                }
                            } else {
                                if ($item->_publication_database == '10') {
                                    @$result[$item->_employee]['mark'] += $item->scientific_publication_types * $criteria->mark_value;
                                }
                            }
                            // $result[$item->_employee]['employee'] =  $item->employee->fullName;
                        }
                    }
                }
            }

            foreach ($property as $item) {
                foreach ($property_criterias as $criteria) {
                    if ($item->_patient_type == $criteria->_publication_property_type) {
                        if (isset($criteria->mark_value)) {
                            //$result[$item->_employee]['employee'] =  $item->employee->fullName;
                            /*if($criteria->_in_publication_database == 1) {
                                if($item->_publication_database != '10') {
                                    @$result[$item->_employee]['mark'] += $item->patient_types * $criteria->mark_value;
                                }
                            }
                            else {
                                if($item->_publication_database == '10') {
                                    @$result[$item->_employee]['mark'] += $item->patient_types * $criteria->mark_value;
                                }
                            }*/
                            @$result[$item->_employee]['mark'] += $item->patient_types * $criteria->mark_value;
                        }
                    }
                }
            }

            foreach ($activity as $item) {
                foreach ($activity_criterias as $criteria) {
                    if ($item->_scientific_platform == $criteria->_scientific_platform) {
                        if (isset($criteria->mark_value)) {

                            if ($criteria->_criteria_type == EScientificPlatformCriteria::PLATFORM_CRITERIA_HINDEX) {
                                if (!empty($item->h_index)) {
                                    @$result[$item->_employee]['mark'] += $item->h_index * $criteria->mark_value;
                                }
                            }
                            if ($criteria->_criteria_type == EScientificPlatformCriteria::PLATFORM_CRITERIA_CITATION) {
                                if ((!empty($item->publication_work_count) && $item->publication_work_count > 0) && !empty($item->citation_count)) {
                                    @$result[$item->_employee]['mark'] += round($item->citation_count / $item->publication_work_count * $criteria->mark_value, 0);
                                    //@$result_activity[$item->_scientific_platform.'_a']['mark'] += $item->citation_count;
                                }
                            }

                        }
                    }
                }
            }


            foreach ($result as $key => $item) {
                foreach ($teacher_list as $teacher) {
                    if ($key === $teacher->_employee) {
                        @$result2[$teacher->_department]['mark'] += $result[$teacher->_employee]['mark'];
                        @$result2[$teacher->_department]['department'] = $teacher->department->name;
                    }
                }
            }
            //  print_r($result2);


            $data = $result2;
            $dataProvider = new ArrayDataProvider([
                'allModels' => $data,
                'sort' => [
                    //     'defaultOrder' => ['mark' => SORT_ASC],
                    'attributes' => [
                        // 'mark',
                        'department' => [
                            'header' => __('Structure Department'),
                        ],
                        'mark' => [
                            'header' => __('Mark'),
                            'asc' => ['mark' => SORT_ASC],
                            'desc' => ['mark' => SORT_DESC],
                            'default' => SORT_ASC,
                        ],
                    ],
                    'defaultOrder' => [
                        'mark' => SORT_DESC
                    ]
                ],
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'department_list' => @$department_list,
            'faculty' => $faculty,
            'dataProvider' => @$dataProvider,


        ]);
    }

    public function actionFacultyRating()
    {
        $this->activeMenu = 'rating';
        $searchModel = new FilterForm();


        $result = array();
        $result2 = array();
        $result3 = array();
        if ($searchModel->load(Yii::$app->request->get())) {
            $department_list = EDepartment::getDepartmentList();
            $departments = array();
            foreach ($department_list as $item) {
                $departments[$item->id] = $item->id;
            }
            $teacher_list = EEmployeeMeta::getTeacherList($departments);

            $methodical_criterias = EPublicationCriteria::getPublicationCriteria(EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL, $searchModel->_education_year);
            $scientifical_criterias = EPublicationCriteria::getPublicationCriteria(EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC, $searchModel->_education_year);
            $property_criterias = EPublicationCriteria::getPublicationCriteria(EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY, $searchModel->_education_year);
            $activity_criterias = EScientificPlatformCriteria::getPublicationCriteria(ECriteriaTemplate::PUBLICATION_TYPE_ACTIVITY, $searchModel->_education_year);

            $methodical = EPublicationAuthorMeta::getCheckedPublication(EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL, $searchModel->_education_year, "", "", "");
            $scientifical = EPublicationAuthorMeta::getCheckedPublication(EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC, $searchModel->_education_year, "", "", "");
            $property = EPublicationAuthorMeta::getCheckedPublication(EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY, $searchModel->_education_year, "", "", "");
            $activity = EScientificPlatformProfile::getCheckedPlatform($searchModel->_education_year, "", "", "");


            foreach ($methodical as $item) {
                foreach ($methodical_criterias as $criteria) {
                    if ($item->_methodical_publication_type == $criteria->_publication_methodical_type) {
                        if (isset($criteria->mark_value)) {
                            //$result[$item->_employee]['employee'] =  $item->employee->fullName;
                            if ($criteria->exist_certificate == 1) {
                                if (!empty($item->certificate_number)) {
                                    @$result[$item->_employee]['mark'] += $item->methodical_publication_types * $criteria->mark_value;
                                }
                            } else {
                                if (empty($item->certificate_number)) {
                                    @$result[$item->_employee]['mark'] += $item->methodical_publication_types * $criteria->mark_value;
                                } else {
                                    @$result[$item->_employee]['mark'] += 0;
                                }
                            }

                        }
                    }
                }
            }

            foreach ($scientifical as $item) {
                foreach ($scientifical_criterias as $criteria) {
                    if ($item->_scientific_publication_type == $criteria->_publication_scientific_type) {
                        if (isset($criteria->mark_value)) {
                            if ($criteria->_in_publication_database == 1) {
                                if ($item->_publication_database != '10') {
                                    @$result[$item->_employee]['mark'] += $item->scientific_publication_types * $criteria->mark_value;
                                }
                            } else {
                                if ($item->_publication_database == '10') {
                                    @$result[$item->_employee]['mark'] += $item->scientific_publication_types * $criteria->mark_value;
                                }
                            }
                            // $result[$item->_employee]['employee'] =  $item->employee->fullName;
                        }
                    }
                }
            }

            foreach ($property as $item) {
                foreach ($property_criterias as $criteria) {
                    if ($item->_patient_type == $criteria->_publication_property_type) {
                        if (isset($criteria->mark_value)) {
                            //$result[$item->_employee]['employee'] =  $item->employee->fullName;
                            /*if($criteria->_in_publication_database == 1) {
                                if($item->_publication_database != '10') {
                                    @$result[$item->_employee]['mark'] += $item->patient_types * $criteria->mark_value;
                                }
                            }
                            else {
                                if($item->_publication_database == '10') {
                                    @$result[$item->_employee]['mark'] += $item->patient_types * $criteria->mark_value;
                                }
                            }*/
                            @$result[$item->_employee]['mark'] += $item->patient_types * $criteria->mark_value;
                        }
                    }
                }
            }

            foreach ($activity as $item) {
                foreach ($activity_criterias as $criteria) {
                    if ($item->_scientific_platform == $criteria->_scientific_platform) {
                        if (isset($criteria->mark_value)) {
                            if ($criteria->_criteria_type == EScientificPlatformCriteria::PLATFORM_CRITERIA_HINDEX) {
                                if (!empty($item->h_index)) {
                                    @$result[$item->_employee]['mark'] += $item->h_index * $criteria->mark_value;
                                }
                            }
                            if ($criteria->_criteria_type == EScientificPlatformCriteria::PLATFORM_CRITERIA_CITATION) {
                                if ((!empty($item->publication_work_count) && $item->publication_work_count > 0) && !empty($item->citation_count)) {
                                    @$result[$item->_employee]['mark'] += round($item->citation_count / $item->publication_work_count * $criteria->mark_value, 0);
                                    //@$result_activity[$item->_scientific_platform.'_a']['mark'] += $item->citation_count;
                                }
                            }
                        }
                    }
                }
            }

            foreach ($result as $key => $item) {
                foreach ($teacher_list as $teacher) {
                    if ($key === $teacher->_employee) {
                        @$result2[$teacher->_department]['mark'] += $result[$teacher->_employee]['mark'];
                        @$result2[$teacher->_department]['department'] = $teacher->department->name;
                    }
                }
            }

            foreach ($result2 as $key => $item) {
                foreach ($department_list as $department) {
                    if ($key === $department->id) {
                        @$result3[$department->parent]['mark'] += $result2[$department->id]['mark'];
                        @$result3[$department->parent]['faculty'] = $department->parentDepartment->name;
                    }
                }
            }

            //  print_r($result2);


            $data = $result3;
            $dataProvider = new ArrayDataProvider([
                'allModels' => $data,
                'sort' => [
                    //     'defaultOrder' => ['mark' => SORT_ASC],
                    'attributes' => [
                        // 'mark',
                        'faculty' => [
                            'header' => __('Structure Faculty'),
                        ],
                        'mark' => [
                            'header' => __('Mark'),
                            'asc' => ['mark' => SORT_ASC],
                            'desc' => ['mark' => SORT_DESC],
                            'default' => SORT_ASC,
                        ],
                    ],
                    'defaultOrder' => [
                        'mark' => SORT_DESC
                    ]
                ],
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'department_list' => @$department_list,
            'dataProvider' => @$dataProvider,


        ]);
    }

    public function actionScientificActivity()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_TEACHER && $this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the teacher only.')
            );
            return $this->goHome();
        }
        $model = new EScientificPlatformProfile();
        //$model->scenario = EPublicationCriteria::SCENARIO_CREATE;
        $searchModel = new EScientificPlatformProfile();
        $dataProvider = $searchModel->search($this->getFilterParams());
        if ($code = $this->get('code')) {
            if ($model = EScientificPlatformProfile::findOne(['id' => $code])) {
                if ($this->deleteModelToApi($model)) {
                    $this->addSuccess(__('Scientific Activity [{code}] is deleted successfully', ['code' => $model->id]));
                    return $this->redirect(['science/scientific-activity']);
                }
            }
        }

        $model->scenario = EScientificPlatformProfile::SCENARIO_CREATE_AUTHOR;

        if ($this->_user()->role->code == AdminRole::CODE_TEACHER || $this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            $model->scenario = EPublicationProperty::SCENARIO_CREATE_AUTHOR;
            $model->_employee = Yii::$app->user->identity->_employee;
            $dataProvider->query->andFilterWhere([
                '_employee' => Yii::$app->user->identity->_employee,
            ]);
        }


        if ($model->load(Yii::$app->request->post())) {
            if (!$model->is_checked) {
                if ($model->save()) {
                    $this->syncModelToApi($model);
                    if ($code) {
                        $this->addSuccess(__('Scientific Activity [{code}] updated successfully', ['code' => $model->id]));
                    } else {
                        $this->addSuccess(__('Scientific Activity [{code}] created successfully', ['code' => $model->id]));
                    }
                    return $this->redirect(['science/scientific-activity', 'code' => $model->id]);
                }
            } else {
                $this->addInfo(
                    __('The Scientific Activity cannot be edited')
                );
            }
        }
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionScientificActivityCheck($id = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_DEPARTMENT) {
            $this->addInfo(
                __('This page is for the department only.')
            );
            return $this->goHome();
        }
        $searchModel = new EScientificPlatformProfile();
        if ($this->_user()->role->code == AdminRole::CODE_DEPARTMENT) {
            if (Yii::$app->user->identity->employee->headDepartments) {
                $department = Yii::$app->user->identity->employee->headDepartments->id;
                //$dataProvider->query->andFilterWhere(['_department' => $department]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        $dataProvider = $searchModel->search_department($this->getFilterParams(), $department);

        if ($attribute = $this->get('attribute')) {
            if ($model = EScientificPlatformProfile::findOne(['id' => $this->get('publication')])) {
                $model->$attribute = !$model->$attribute;
                if ($model->save(false)) {
                    $this->syncModelToApi($model);
                    if ($model->$attribute == EScientificPlatformProfile::STATUS_ENABLE) {
                        $this->addSuccess(__('Item [{id}] of Scientific Activity is enabled', ['id' => $model->id]), true, true);
                    } else {
                        $this->addSuccess(__('Item [{id}] of Scientific Activity is disabled', ['id' => $model->id]), true, true);
                    }
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [];
                }
            }
        }

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionScientificActivityCriteria()
    {
        $this->activeMenu = 'rating';
        $model = new EScientificPlatformCriteria();
        $searchModel = new EScientificPlatformCriteria();

        if ($code = $this->get('code')) {
            if ($model = EScientificPlatformCriteria::findOne(['id' => $code])) {
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Scientific Activity Criteria [{code}] is deleted successfully', ['code' => $model->id]));

                            return $this->redirect(['science/scientific-activity-criteria']);
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['science/scientific-activity-criteria', 'code' => $model->id]);
                }
            } else {
                return $this->redirect(['science/scientific-activity-criteria']);
            }
        }

        if ($code = $this->get('import')) {
            $searchModel = new ECriteriaTemplate();
            $dataProvider = $searchModel->search($this->getFilterParams());
            $dataProvider->query->andFilterWhere([
                'in', '_publication_type_table', [ECriteriaTemplate::PUBLICATION_TYPE_ACTIVITY]
            ]);
            $dataProvider->query->andFilterWhere([
                'active' => ECriteriaTemplate::STATUS_ENABLE,
            ]);
            $searchModelFix = new EPublicationCriteria();
            return $this->renderAjax('scientific-activity-criteria-import', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'searchModelFix' => $searchModelFix,
            ]);
        }

        $model->scenario = EScientificPlatformCriteria::SCENARIO_CREATE;
        if ($model->load(Yii::$app->request->post())) {
            $model->_publication_type_table = '14';
            if ($model->save()) {
                if ($code) {
                    $this->addSuccess(__('Scientific Activity Criteria [{code}] updated successfully', ['code' => $model->id]));
                } else {
                    $this->addSuccess(__('Scientific Activity Criteria [{code}] created successfully', ['code' => $model->id]));
                }
                return $this->redirect(['science/scientific-activity-criteria']);
            }
            // $model = new EducationYear();
        }
        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionCriteriaTemplate()
    {
        $this->activeMenu = 'rating';
        $searchModel = new ECriteriaTemplate();
        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            //'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return EProject
     * @throws NotFoundHttpException
     */
    protected function findProjectModel($id)
    {
        if (($model = EProject::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findProjectMetaModel($id)
    {
        if (($model = EProjectMeta::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findOneProjectMetaModel($project, $id)
    {
        if (($model = EProjectMeta::findOne(['id' => $id, '_project' => $project])) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findProjectExecutorModel($id)
    {
        if (($model = EProjectExecutor::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findOneProjectExecutorModel($project, $id)
    {
        if (($model = EProjectExecutor::findOne(['id' => $id, '_project' => $project])) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findPublicationMethodicalModel($id)
    {
        if (($model = EPublicationMethodical::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findPublicationScientificalModel($id)
    {
        if (($model = EPublicationScientific::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findPublicationPropertyModel($id)
    {
        if (($model = EPublicationProperty::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findSpecialModel($id)
    {
        if (($model = ESpecialty::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    /**
     * @param $id
     * @return EDoctorateStudent
     * @throws NotFoundHttpException
     */

    protected function findDoctorateStudentModel($id)
    {
        if (($model = EDoctorateStudent::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findDissertationDefenceModel($id)
    {
        if (($model = EDissertationDefense::findOne(['_doctorate_student' => $id])) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findPublicationAuthorModel($id, $publication_type = false)
    {
        if ($publication_type == EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL) {
            if (($model = EPublicationAuthorMeta::findOne(['_publication_methodical' => $id, 'is_main_author' => 1])) !== null) {
                return $model;
            } else {
                $this->notFoundException();
            }
        } elseif ($publication_type == EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC) {
            if (($model = EPublicationAuthorMeta::findOne(['_publication_scientific' => $id, 'is_main_author' => 1])) !== null) {
                return $model;
            } else {
                $this->notFoundException();
            }
        } elseif ($publication_type == EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY) {
            if (($model = EPublicationAuthorMeta::findOne(['_publication_property' => $id, 'is_main_author' => 1])) !== null) {
                return $model;
            } else {
                $this->notFoundException();
            }
        }
    }

    protected function findPublicationAuthorModelOne($id, $publication_type = false, $employee = false)
    {
        if ($publication_type == EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL) {
            if (($model = EPublicationAuthorMeta::findOne(['_publication_methodical' => $id, '_employee' => $employee])) !== null) {
                return $model;
            } else {
                $this->notFoundException();
            }
        } elseif ($publication_type == EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC) {
            if (($model = EPublicationAuthorMeta::findOne(['_publication_scientific' => $id, '_employee' => $employee])) !== null) {
                return $model;
            } else {
                $this->notFoundException();
            }
        } elseif ($publication_type == EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY) {
            if (($model = EPublicationAuthorMeta::findOne(['_publication_property' => $id, '_employee' => $employee])) !== null) {
                return $model;
            } else {
                $this->notFoundException();
            }
        }
    }
}
