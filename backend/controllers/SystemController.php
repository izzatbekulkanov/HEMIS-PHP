<?php

namespace backend\controllers;

use backend\models\FormUploadTrans;
use common\components\AccessResources;
use common\components\Config;
use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiSyncModel;
use common\components\hemis\models\SyncLog;
use common\models\employee\EEmployee;
use common\models\report\BaseReport;
use common\models\system\Admin;
use common\models\system\AdminResource;
use common\models\system\AdminRole;
use common\models\system\classifier\_BaseClassifier;
use common\models\system\job\EmployeeContingentFileGenerateJob;
use common\models\system\SystemClassifier;
use common\models\system\SystemLogin;
use common\models\system\SystemLog;
use common\models\system\SystemMessage;
use common\models\system\SystemMessageTranslation;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\UnknownClassException;
use yii\console\controllers\MigrateController;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseFileHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\queue\redis\Queue;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class SystemController extends BackendController
{
    public $activeMenu = 'system';

    public function actionBackup()
    {
        $dir = Yii::getAlias('@backups') . DS;
        $files = glob($dir . '*.bak.*');
        if ($name = $this->get('id')) {
            if (file_exists($dir . $name) && in_array($dir . $name, $files)) {
                return Yii::$app->response->sendFile($dir . $name);
            } else {
                $this->notFoundException();
            }
        }

        if ($name = $this->get('rem')) {
            if (file_exists($dir . $name) && in_array($dir . $name, $files)) {
                $time = time() - intval(filemtime($dir . $name));
                if ($time < 3600 * 24 * 7) {
                    if (unlink($dir . $name)) {
                        $this->addSuccess(__('File `{file}` has removed.', ['file' => $name]));
                    }
                } else {
                    $this->addError(__('You cannot delete backups after a week'));
                }
                return $this->redirect('backup');
            } else {
                $this->notFoundException();
            }
        }

        return $this->render('backup', [
            'dataProvider' => Config::getBackupProvider()
        ]);
    }

    public function actionSystemLog($id = false)
    {
        if ($id) {
            if ($model = SystemLog::findOne($id)) {
                return $this->render('system-log-view', [
                    'model' => $model,
                ]);
            }
            return $this->redirect(['system/system-log']);
        }
        $searchModel = new SystemLog();

        return $this->render('system-log', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->getFilterParams()),
        ]);
    }

    public function actionSyncLog()
    {
        $searchModel = new SyncLog();

        return $this->render('sync-log', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->getFilterParams()),
        ]);
    }

    public function actionSyncStatus()
    {
        if ($class = $this->get('sync')) {
            $client = HemisApi::getApiClient();
            if ($count = $client->syncAllModelsToApi($class)) {
                $this->addSuccess(__('{count} {model} pushed to synchronization queue for Hemis API', ['count' => $count, 'model' => $client->getModelTitle($class)]));
            }

            return $this->redirect(['sync-status']);
        }
        if ($this->get('toggle')) {
            if ($class = $this->get('id')) {
                $path = 'disable_sync_model_' . str_replace('-', '\\', $class);
                Config::set($path, !boolval(Config::get($path)));
            } elseif ($this->get('batch')) {
                $state = boolval($this->post('state'));
                foreach ($this->post('items') as $class) {
                    $path = 'disable_sync_model_' . str_replace('-', '\\', $class);
                    Config::set($path, !$state);
                }
            }
        }
        if ($class = $this->get('run')) {
            if (is_subclass_of($class, BaseReport::class)) {
                if ($count = $class::runReport(false)) {
                    $this->addSuccess(__('{count} report of {name} updated', ['count' => $count, 'name' => (new $class)->getName()]));
                }
            }

            return $this->redirect(['sync-status']);
        }

        if ($class = $this->get('detail')) {
            /**
             * @var $searchModel HemisApiSyncModel
             */
            $client = HemisApi::getApiClient();
            $searchModel = new $class;
            if ($status = $this->get('check')) {
                if ($count = $client->checkAllModelsToApi($class, $status)) {
                    $this->addSuccess(__('{count} {model} pushed queue to check with HEMIS API', ['count' => $count, 'model' => $client->getModelTitle($class)]));
                }
                return $this->redirect(['sync-status', 'detail' => $class]);
            }

            return $this->render('sync-detail', [
                'class' => $class,
                'searchModel' => $searchModel,
                'dataProvider' => $searchModel->searchForSyncDetail($this->getFilterParams()),
            ]);
        }

        return $this->renderView([
            'class' => $class,
        ]);
    }

    /**
     * @resource system/login-delete
     */
    public function actionLogin()
    {
        $searchModel = new SystemLogin();

        if ($this->get('delete') && $this->canAccessToResource('system/login-delete')) {
            if ($login = SystemLogin::findOne($this->get('id', -1))) {
                if ($login->delete()) {
                    $this->addSuccess(__('Login record from {ip} on {date} by {login} deleted',
                        [
                            'ip' => $login->ip,
                            'login' => Html::encode($login->login),
                            'date' => Yii::$app->formatter->asDatetime($login->created_at)
                        ]
                    ));
                }
            }
        }

        return $this->render('login', [
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @resource system/translation-batch-actions
     * @resource system/translation-clear
     */
    public function actionTranslation()
    {
        /**
         * @var $message SystemMessage
         */
        if ($message = SystemMessage::findOne($this->get('id', -1))) {
            $message->loadTranslations();

            if ($this->get('delete')) {
                if ($message->delete()) {
                    $this->addSuccess(__('Message [{message}] deleted successfully', ['message' => $message->message]));
                }

                return $this->redirect(['system/translation']);

            } else if ($message->load(Yii::$app->request->post()) && $message->updateTranslation()) {
                $this->addSuccess(__('Translation updated successfully'));
            }
        } else {
            if ($this->canAccessToResource('system/translation-clear')) {
                if ($this->get('clear') && !Yii::$app->request->isAjax) {
                    SystemMessageTranslation::deleteAll();
                    if ($count = SystemMessage::deleteAll()) {
                        $this->addSuccess(
                            __('{count} messages deleted successfully', ['count' => $count])
                        );
                    }

                    return $this->redirect(['system/translation']);
                }
            }

            if ($this->canAccessToResource('system/translation-batch-actions')) {

                $uploadForm = new FormUploadTrans();
                if ($uploadForm->load(Yii::$app->request->post())) {
                    try {
                        if ($count = $uploadForm->uploadData()) {
                            $this->addSuccess(
                                __('{count} translations uploaded successfully', ['count' => $count])
                            );
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getTraceAsString(), true);
                    }

                    return $this->redirect(['system/translation']);
                }


                $messages = SystemMessage::find()
                    ->with(['systemMessageTranslation'])
                    ->orderBy(['category' => SORT_ASC, 'message' => SORT_ASC])
                    ->all();

                if ($this->get('convert') && Config::isLatinCyrill() && !Yii::$app->request->isAjax) {
                    $count = 0;

                    foreach ($messages as $message) {
                        $count += $message->transliterateUzbek();
                    }

                    if ($count) {
                        $this->addSuccess(__('{count} messages transliterated successfully', ['count' => $count]));
                    }

                    return $this->redirect(['system/translation']);
                }

                if ($this->get('download') && !Yii::$app->request->isAjax) {
                    /**
                     * @var $message SystemMessage
                     */
                    $languages = Config::getLanguageOptions();
                    $result = [
                        array_merge(['category', 'message'], array_keys($languages)),
                    ];


                    $cols = $result[0];

                    foreach ($messages as $message) {
                        $data = [
                            'category' => $message->category,
                            'message' => $message->message,
                        ];

                        foreach ($message->systemMessageTranslation as $translation) {
                            $data[$translation->language] = $translation->translation;
                        }

                        $item = [];
                        foreach ($cols as $col) {
                            $item[$col] = isset($data[$col]) ? $data[$col] : '';
                        }
                        $result[] = $item;
                    }

                    $fileName = Yii::getAlias('@runtime') . DS . 'trans_' . time() . '.csv';
                    if ($handle = fopen($fileName, 'w+')) {
                        foreach ($result as $row)
                            fputcsv($handle, $row, ",", '"');
                        fclose($handle);

                        return Yii::$app->response->sendFile($fileName);
                    }

                    return $this->redirect(['system/translation']);
                }
            }

        }

        $searchModel = new SystemMessage();

        return $this->render('translation', [
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'message' => $message,
        ]);
    }

    /**
     * @skipAccess
     */
    public function actionCache()
    {
        $this->removeCache();

        $this->addSuccess(__('System cache cleared successfully'), false);
        if ($url = Yii::$app->request->getReferrer()) {
            return $this->redirect($url);
        }

        return $this->goHome();
    }

    public function actionConfiguration()
    {
        if (Yii::$app->request->getIsPost() && Config::batchUpdate($this->post('config'))) {
            $this->addSuccess(__('Configuration updated successfully'));

            return $this->redirect(['configuration']);
        }

        return $this->render('configuration', []);
    }


    public function actionClassifier()
    {
        /**
         * @var $model SystemClassifier
         * @var $itemModel _BaseClassifier
         * @var $searchModel _BaseClassifier
         */
        $searchModel = new SystemClassifier();
        $classifierModel = null;
        $model = null;

        if ($classifier = $this->get('classifier')) {
            if ($model = SystemClassifier::findOne(['classifier' => $classifier])) {
                try {
                    $name = Inflector::camelize(substr($classifier, 1));
                    $class = $model->getClassifierClassName();
                    $file = Yii::getAlias("@root/common/models/system/classifier/{$name}.php");

                    if (!file_exists($file)) {
                        $template = Yii::getAlias("@root/common/models/system/classifier/_template.tpl");
                        file_put_contents($file, str_replace('{classifier}', $classifier, str_replace('{name}', $name, file_get_contents($template))));
                    }
                    $searchModel = Yii::createObject($class);
                    $itemModel = Yii::createObject($class);
                    $itemModel->active = true;

                } catch (InvalidConfigException $exception) {
                } catch (\Exception $exception) {
                    $this->addError(__('Classifier Model not found'));
                    return $this->redirect(['classifier']);
                }


                if ($this->get('clear')) {
                    if ($count = $class::deleteAll()) {
                        $this->addSuccess(__('{count} items deleted of [{classifier}]', ['classifier' => $model->classifier, 'count' => $count]));
                    }

                    return $this->redirect(['system/classifier', 'classifier' => $model->classifier]);
                }

                if ($this->get('download')) {
                    $class = $model->getClassifierClassName();
                    $items = [
                        'options' => [],
                        'code' => $classifier,
                    ];
                    $nameUz = $model->getTranslation('name', Config::LANGUAGE_UZBEK);
                    foreach (Config::getLanguageOptions() as $lang => $label) {
                        $name = $model->getTranslation('name', $lang);

                        if ($lang != Config::LANGUAGE_UZBEK) {
                            $name = ($name == $nameUz) ? '' : $name;
                        }
                        if ($name)
                            $items[$lang] = $name;
                    }


                    foreach ($class::find()->addOrderBy(['position' => SORT_ASC])->all() as $item) {
                        $nameUz = $item->getTranslation('name', Config::LANGUAGE_UZBEK);

                        $data = [
                            'code' => $item->code,
                        ];
                        foreach (Config::getLanguageOptions() as $lang => $label) {
                            $name = $item->getTranslation('name', $lang);

                            if ($lang != Config::LANGUAGE_UZBEK) {
                                $name = ($name == $nameUz) ? '' : $name;
                            }
                            if ($name)
                                $data[$lang] = $name;
                        }
                        $items['options'][] = $data;
                    }

                    return Yii::$app->response->sendContentAsFile(json_encode([
                        $classifier => $items,
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "$classifier.json");
                }

                if ($this->get('import')) {
                    $model->setScenario('import');

                    if ($model->load($this->post())) {
                        if ($count = SystemClassifier::importData($model->import, $class)) {
                            $this->addSuccess(__('{count} items updated of [{classifier}]', ['classifier' => $model->classifier, 'count' => $count]));
                        }
                        return $this->redirect(['system/classifier', 'classifier' => $model->classifier]);
                    }

                    return $this->render('classifier-import', [
                        'model' => $model,
                    ]);
                }

                if ($sync = $this->get('sync')) {
                    if (!$this->syncModelToApi($model)) {
                        $this->addInfo(__('Ushbu klassifikatorda o\'zgarishlar mavjud emas'));
                    }
                    return $this->redirect(['system/classifier', 'classifier' => $model->classifier]);
                }

                if ($code = $this->get('code')) {
                    if ($itemModel = $itemModel->findOne($code)) {
                        if ($this->get('delete') && HEMIS_INTEGRATION == false) {
                            try {
                                if ($itemModel->delete()) {
                                    $this->addSuccess(__('Item [{code}] of [{classifier}] is deleted successfully', ['code' => $itemModel->code, 'classifier' => $model->classifier]));
                                }
                            } catch (\Exception $e) {
                                $this->addError($e->getMessage());
                            }
                            return $this->redirect(['system/classifier', 'classifier' => $model->classifier]);
                        }
                    } else {
                        return $this->redirect(['system/classifier', 'classifier' => $model->classifier]);
                    }
                }

                if ($itemModel->load($this->post()) && $itemModel->save()) {
                    $this->addSuccess(__('Item [{code}] added to classifier [{classifier}]', ['code' => $itemModel->code, 'classifier' => $model->classifier]));
                    //$itemModel = Yii::createObject($class);

                    //return $this->redirect(['system/classifier', 'classifier' => $model->classifier, 'code' => $itemModel->code]);
                }

                return $this->render('classifier-view', [
                    'dataProvider' => $searchModel->search($this->getFilterParams()),
                    'searchModel' => $searchModel,
                    'itemModel' => $itemModel,
                    'model' => $model,
                ]);
            }
        }

        if ($sync = $this->get('sync')) {

            if ($sync == 'api' && HEMIS_INTEGRATION) {
                $api = HemisApi::getApiClient();
                if ($count = $api->syncAllModelsToApi(SystemClassifier::class, false)) {
                    $this->addSuccess(__('{count} classifiers updated', ['count' => $count]));
                }
            }
            if ($sync == 'code') {
                $migration = Yii::createObject([
                    'class' => Migration::class,
                    'db' => Yii::$app->db,
                    'compact' => false,
                ]);

                if ($count = SystemClassifier::createClassifiersTables($migration)) {
                    $this->addSuccess(__('{count} classifiers updated', ['count' => $count]));
                }
            }

            return $this->redirect(['classifier']);
        }

        if ($this->get('download')) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Options');

            $classifiersSheet = new Worksheet();
            $classifiersSheet->setTitle('Classifiers');
            $spreadsheet->addSheet($classifiersSheet);

            /**
             * @var $classifiers SystemClassifier[]
             * @var $item _BaseClassifier
             */
            $classifiers = SystemClassifier::find()
                ->orderByTranslationField('name')
                ->all();

            $row = 1;
            $col = 1;
            $colClass = 1;
            $rowClass = 1;

            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, 'classifier', DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, 'code', DataType::TYPE_STRING);

            $classifiersSheet->setCellValueExplicitByColumnAndRow($colClass++, $rowClass, 'code', DataType::TYPE_STRING);

            foreach (Config::getLanguageOptions() as $lang => $label) {
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $lang, DataType::TYPE_STRING);
                $classifiersSheet->setCellValueExplicitByColumnAndRow($colClass++, $rowClass, $lang, DataType::TYPE_STRING);
            }


            foreach ($classifiers as $classifier) {
                $colClass = 1;
                $rowClass++;
                $classifiersSheet->setCellValueExplicitByColumnAndRow($colClass++, $rowClass, $classifier->classifier, DataType::TYPE_STRING);
                $nameUz = $classifier->getTranslation('name', Config::LANGUAGE_UZBEK);

                foreach (Config::getLanguageOptions() as $lang => $label) {
                    $name = $classifier->getTranslation('name', $lang);

                    if ($lang != Config::LANGUAGE_UZBEK) {
                        $name = ($name == $nameUz) ? '' : $name;
                    }
                    $classifiersSheet->setCellValueExplicitByColumnAndRow($colClass++, $rowClass, $name, DataType::TYPE_STRING);
                }

                $class = $classifier->getClassifierClassName();
                foreach ($class::find()->addOrderBy(['position' => SORT_ASC])->all() as $item) {

                    $row++;
                    $col = 1;
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $classifier->classifier, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->code, DataType::TYPE_STRING);

                    $nameUz = $item->getTranslation('name', Config::LANGUAGE_UZBEK);

                    foreach (Config::getLanguageOptions() as $lang => $label) {
                        $name = $item->getTranslation('name', $lang);

                        if ($lang != Config::LANGUAGE_UZBEK) {
                            $name = ($name == $nameUz) ? '' : $name;
                        }

                        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $name, DataType::TYPE_STRING);
                    }
                }
            }

            $name = 'Classifiers-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $fileName = Yii::getAlias('@runtime') . DS . $name;
            $writer->save($fileName);

            return Yii::$app->response->sendFile($fileName, $name);
        }

        return $this->render('classifier', [
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }


    public function actionAdmin()
    {
        $searchModel = new Admin(['scenario' => 'search']);

        if ($this->get('download')) {
            $query = $searchModel->search($this->getFilterParams());
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle(__('Administrators'));

            $row = 1;
            $col = 1;

            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Login'), DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Employee Id Number'), DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Full Name'), DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Role'), DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Email'), DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Telephone'), DataType::TYPE_STRING);

            /**
             * @var $model Admin
             */
            foreach ($query->query->orderBy(['login' => SORT_ASC])->all() as $i => $model) {
                if ($model->isSuperAdmin()) continue;

                $col = 1;
                $row++;

                $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->login, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->employee ? $model->employee->employee_id_number : '', DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->full_name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row, count($model->roles) > 1 ? implode(', ', \yii\helpers\ArrayHelper::getColumn($model->roles, 'name')) : @$model->role->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->email, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $model->telephone, DataType::TYPE_STRING);
            }

            $name = 'Adminlar-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $dir = Yii::getAlias('@backend/runtime/export/');
            if (!is_dir($dir)) {
                FileHelper::createDirectory($dir, 0777);
            }
            $fileName = $dir . $name;
            $writer->save($fileName);

            return Yii::$app->response->sendFile($fileName, basename($fileName));
        }

        return $this->render('admin/index', [
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
        ]);
    }

    public function actionAdminEdit($id = false)
    {
        if ($id) {
            $model = $this->findAdminModel($id);
            if ($model->isTechAdmin()) {
                return $this->redirect(['system/admin']);
            }
            $model->scenario = Admin::SCENARIO_UPDATE;
        } else {
            $model = new Admin(['scenario' => Admin::SCENARIO_INSERT]);
        }

        if ($model->employee) {
            return $this->redirect(['employee/account', 'id' => $model->_employee]);
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            if ($id) {
                $this->addSuccess(
                    __('Administrator `{name}` updated successfully.', [
                        'name' => $model->login
                    ])
                );
            } else {
                $this->addSuccess(
                    __('Administrator `{name}` created successfully.', [
                        'name' => $model->login
                    ])
                );
            }

            if ($model->change_password)
                $this->addSuccess(
                    __('Password `{name}` updated successfully.', [
                        'name' => $model->login
                    ])
                );
            return $this->redirect(['admin-edit', 'id' => $model->id]);
        }

        return $this->render('admin/edit', [
            'model' => $model,
        ]);
    }

    public function actionAdminDelete($id)
    {
        $model = $this->findAdminModel($id);

        try {
            if ($model->delete()) {
                $this->addSuccess(
                    __('Administrator `{name}` deleted successfully.', [
                        'name' => $model->login
                    ])
                );
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());

            return $this->redirect(['admin-edit', 'id' => $model->id]);
        }


        return $this->redirect(['admin']);
    }


    public function actionRole()
    {
        $searchModel = new AdminRole();

        /**
         * @var $role AdminRole
         */
        if ($this->get('download')) {
            $items = [];

            foreach (AdminRole::find()->where(['!=', 'code', AdminRole::CODE_SUPER_ADMIN])->with('resources')->orderBy(['position' => SORT_ASC])->all() as $role) {
                $items[] = [
                    'code' => $role->code,
                    'position' => $role->position,
                    'name' => $role->getAllTranslations('name'),
                    'resources' => ArrayHelper::getColumn($role->resources, function (AdminResource $resource) {
                        return $resource->path;
                    })
                ];
            }

            return Yii::$app->response->sendContentAsFile(Json::encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'admin-roles.json');
        }

        return $this->render('admin/role', [
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
        ]);
    }


    public function actionRoleEdit($id)
    {
        if ($id) {
            $model = $this->findAdminRoleModel($id);

            if ($this->get('delete') && false) {
                try {
                    if ($model->delete()) {
                        $this->addSuccess(
                            __('Role `{name}` deleted successfully.', [
                                'name' => $model->code
                            ])
                        );
                        return $this->redirect(['role']);
                    }
                } catch (Exception $e) {
                    $this->addError($e->getMessage());
                }
                return $this->redirect(['role-edit', 'id' => $model->id]);
            }
        } else {
            $model = new AdminRole();
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($id) {
                $this->addSuccess(
                    __('Role `{name}` updated successfully.', [
                        'name' => $model->code
                    ]));
            } else {
                $this->addSuccess(
                    __('Role `{name}` created successfully.', [
                        'name' => $model->code
                    ]));
            }

            return $this->redirect(['role-edit', 'id' => $model->id]);
        }

        return $this->render('admin/role-edit', [
            'model' => $model,
        ]);
    }

    public function actionResource()
    {
        $searchModel = new AdminResource();

        if ($this->get('reindex')) {
            if ($updated = AccessResources::parsePermissions(true)) {
                $this->addInfo(__('{count} resources updated', ['count' => $updated]));
            }
            return $this->redirect(['resource']);
        }

        if ($this->get('clear')) {
            AdminResource::deleteAll([]);
            return $this->redirect(['resource']);
        }


        return $this->render('admin/resource', [
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Finds the Admin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Admin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findAdminModel($id)
    {
        if (($model = Admin::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findAdminRoleModel($id)
    {
        if (($model = AdminRole::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findAdminResourceModel($id)
    {
        if (($model = AdminResource::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }
}
