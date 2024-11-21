<?php

namespace backend\controllers;

use backend\models\FilterForm;
use backend\models\FormUploadTrans;
use backend\models\FormUploadUzasbo;
use common\components\AccessResources;
use common\components\Config;
use common\components\file\InterlacedImage;
use common\models\academic\EDecree;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\employee\EEmployeeMeta;
use common\models\finance\EContractPrice;
use common\models\finance\EContractType;
use common\models\finance\EIncreasedContractCoefficient;
use common\models\finance\EMinimumWage;
use common\models\finance\EPaidContractFee;
use common\models\finance\EStipendValue;
use common\models\finance\EStudentContract;
use common\models\finance\EStudentContractInvoice;
use common\models\finance\EStudentContractInvoiceMeta;
use common\models\finance\EStudentContractType;
use common\models\finance\EStudentScholarship;
use common\models\finance\EStudentScholarshipMonth;
use common\models\structure\EUniversity;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\AdminRole;
use common\models\system\classifier\CitizenshipType;
use common\models\system\classifier\ContractSummaType;
use common\models\system\classifier\ContractType;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\ProjectCurrency;
use common\models\system\classifier\StipendRate;
use common\models\system\classifier\StudentStatus;
use common\models\system\classifier\StudentType;
use common\models\system\job\ContractFileGenerateJob;
use common\models\system\job\ContractListFileGenerateJob;
use frontend\models\finance\StudentContractType;
use Mpdf\Mpdf;
use Da\QrCode\QrCode;
use Mpdf\Output\Destination;
use phpDocumentor\Reflection\Types\Null_;
use Yii;
use yii\base\ErrorException;
use yii\helpers\FileHelper;
use yii\queue\redis\Queue;
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
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\mpdf\Pdf;


class FinanceController extends BackendController
{
    public $activeMenu = 'finance';

    public function actionMinimumWage()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $model = new EMinimumWage();
        $model->scenario = EMinimumWage::SCENARIO_CREATE;
        $searchModel = new EMinimumWage();

        if ($code = $this->get('code')) {
            if ($model = EMinimumWage::findOne(['id' => $code])) {
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Minimum Wage [{code}] is deleted successfully', ['code' => $model->id]));

                            return $this->redirect(['finance/minimum-wage']);
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['finance/minimum-wage', 'code' => $model->id]);
                }
            } else {
                return $this->redirect(['finance/minimum-wage']);
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($code) {
                $this->addSuccess(__('Minimum Wage [{code}] updated successfully', ['code' => $model->id]));
            } else {
                $this->addSuccess(__('Minimum Wage [{code}] created successfully', ['code' => $model->id]));
            }

        }
        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionScholarshipAmount()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $model = new EStipendValue();
        $model->scenario = EStipendValue::SCENARIO_CREATE;
        $searchModel = new EStipendValue();

        if ($code = $this->get('code')) {
            if ($model = EStipendValue::findOne(['id' => $code])) {
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Amount of Scholarship [{code}] is deleted successfully', ['code' => $model->id]));

                            return $this->redirect(['finance/scholarship-amount']);
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['finance/scholarship-amount', 'code' => $model->id]);
                }
            } else {
                return $this->redirect(['finance/scholarship-amount']);
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($code) {
                $this->addSuccess(__('Amount of Scholarship [{code}] updated successfully', ['code' => $model->id]));
            } else {
                $this->addSuccess(__('Amount of Scholarship [{code}] created successfully', ['code' => $model->id]));
            }

        }
        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionContractType()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $model = new EContractType();
        $model->scenario = EContractType::SCENARIO_CREATE;
        $searchModel = new EContractType();

        if ($code = $this->get('code')) {
            if ($model = EContractType::findOne(['id' => $code])) {
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Type of Contract [{code}] is deleted successfully', ['code' => $model->id]));

                            return $this->redirect(['finance/contract-type']);
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['finance/contract-type', 'code' => $model->id]);
                }
            } else {
                return $this->redirect(['finance/contract-type']);
            }
        }
        if ($model->load(Yii::$app->request->post())) {
            try {
                if ($model->save()) {
                    if ($code) {
                        $this->addSuccess(__('Type of Contract [{code}] updated successfully', ['code' => $model->id]));
                    } else {
                        $this->addSuccess(__('Type of Contract [{code}] created successfully', ['code' => $model->id]));
                    }
                }
            } catch (\Exception $e) {
                if ($e->getCode() == 23505) {
                    $this->addError(__('Could not insert duplicated data'));
                } else {
                    $this->addError($e->getMessage());
                }
            }
        }
        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionIncreasedContractCoef()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $model = new EIncreasedContractCoefficient();
        $model->scenario = EIncreasedContractCoefficient::SCENARIO_CREATE;
        $searchModel = new EIncreasedContractCoefficient();

        if ($code = $this->get('code')) {
            if ($model = EIncreasedContractCoefficient::findOne(['id' => $code])) {
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Coefficient of Increased Contract [{code}] is deleted successfully', ['code' => $model->id]));

                            return $this->redirect(['finance/increased-contract-coef']);
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['finance/increased-contract-coef', 'code' => $model->id]);
                }
            } else {
                return $this->redirect(['finance/increased-contract-coef']);
            }
        }
        if ($model->load(Yii::$app->request->post())) {
            $model->_education_type = ESpecialty::findOne(['id' => $model->_specialty])->_education_type;
            try {
                if ($model->save()) {
                    if ($code) {
                        $this->addSuccess(__('Coefficient of Increased Contract [{code}] updated successfully', ['code' => $model->id]));
                    } else {
                        $this->addSuccess(__('Coefficient of Increased Contract [{code}] created successfully', ['code' => $model->id]));
                    }
                }
            } catch (\Exception $e) {
                if ($e->getCode() == 23505) {
                    $this->addError(__('Could not insert duplicated data'));
                } else {
                    $this->addError($e->getMessage());
                }
            }
        }
        return $this->renderView([
            'dataProvider' => $searchModel->search($this->getFilterParams()),
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    public function actionContractPrice()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $searchModel = new EContractPrice();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['contract_locality' => EContractPrice::CONTRACT_LOCALITY_LOCAL]);
        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionContractPriceEdit($id = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        if ($id) {
            $model = EContractPrice::findOne($id);
            if (!$model) {
                $this->notFoundException();
            }
        } else {
            $model = new EContractPrice();

        }
        $model->scenario = EContractPrice::SCENARIO_CREATE_LOCAL;

        $model->_citizenship_type = CitizenshipType::CITIZENSHIP_TYPE_UZB;
        $model->contract_locality = EContractPrice::CONTRACT_LOCALITY_LOCAL;

        if ($this->get('delete', false)) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(
                        __(
                            'Contract Price `{name}` deleted successfully.',
                            ['name' => $model->id]
                        )
                    );
                    return $this->redirect(['finance/contract-price']);
                }
            } catch (\Throwable $exception) {
                $this->addError($exception->getMessage());
            }
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($this->post())) {
            $model->_contract_currency = ProjectCurrency::CURRENCY_TYPE_UZB;
            $model->_education_type = ESpecialty::findOne(['id' => $model->_specialty])->_education_type;
            if ($model->isNewRecord) {
                if (EContractPrice::getCheckContractPrice($model->_department, $model->_specialty, $model->_education_form, $model->_student_type, EContractPrice::CONTRACT_LOCALITY_LOCAL) !== null) {
                    $this->addError(__('Could not insert duplicated data'));
                    return $this->redirect(Yii::$app->request->referrer);
                }
            } else {
                if (EContractPrice::getCheckContractPrice($model->_department, $model->_specialty, $model->_education_form, $model->_student_type, EContractPrice::CONTRACT_LOCALITY_LOCAL, $model->id) !== null) {
                    $this->addError(__('Could not insert duplicated data'));
                    return $this->redirect(Yii::$app->request->referrer);

                }
            }

            if ($model->save()) {
                if ($id) {
                    $this->addSuccess(
                        __(
                            'Contract Price {name} updated successfully',
                            ['name' => $model->id]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Contract Price {name} created successfully',
                            ['name' => $model->id]
                        )
                    );
                }
                return $this->redirect(['finance/contract-price-edit', 'id' => $model->id]);
            }


        }

        return $this->renderView(
            [
                'model' => $model,
            ]
        );
    }

    public function actionContractPriceForeign()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $searchModel = new EContractPrice();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['contract_locality' => EContractPrice::CONTRACT_LOCALITY_FOREIGN]);
        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionContractPriceForeignEdit($id = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        if ($id) {
            $model = EContractPrice::findOne($id);
            if (!$model) {
                $this->notFoundException();
            }
        } else {
            $model = new EContractPrice();
        }
        $model->scenario = EContractPrice::SCENARIO_CREATE_FOREIGN;
        if ($this->get('delete', false)) {
            try {
                if ($model->delete()) {
                    $this->addSuccess(
                        __(
                            'Contract Price `{name}` deleted successfully.',
                            ['name' => $model->id]
                        )
                    );
                    return $this->redirect(['finance/contract-price-foreign']);
                }
            } catch (\Throwable $exception) {
                $this->addError($exception->getMessage());
            }
        }
        $model->contract_locality = EContractPrice::CONTRACT_LOCALITY_FOREIGN;
        $model->_citizenship_type = CitizenshipType::CITIZENSHIP_TYPE_FOREIGN;
        if ($model->isNewRecord)
            $model->_contract_currency = ProjectCurrency::CURRENCY_TYPE_UZB;
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($this->post())) {

            $model->_education_type = ESpecialty::findOne(['id' => $model->_specialty])->_education_type;
            if ($model->isNewRecord) {
                if (EContractPrice::getCheckContractPrice($model->_department, $model->_specialty, $model->_education_form, false, EContractPrice::CONTRACT_LOCALITY_FOREIGN) !== null) {
                    $this->addError(__('Could not insert duplicated data'));
                    return $this->redirect(Yii::$app->request->referrer);
                }
            } else {
                if (EContractPrice::getCheckContractPrice($model->_department, $model->_specialty, $model->_education_form, false, EContractPrice::CONTRACT_LOCALITY_FOREIGN, $model->id) !== null) {
                    $this->addError(__('Could not insert duplicated data'));
                    return $this->redirect(Yii::$app->request->referrer);

                }
            }
            if ($model->save()) {
                if ($id) {
                    $this->addSuccess(
                        __(
                            'Contract Price {name} updated successfully',
                            ['name' => $model->id]
                        )
                    );
                } else {
                    $this->addSuccess(
                        __(
                            'Contract Price {name} created successfully',
                            ['name' => $model->id]
                        )
                    );
                }
                return $this->redirect(['finance/contract-price-foreign-edit', 'id' => $model->id]);
            }
        }

        return $this->renderView(
            [
                'model' => $model,
            ]
        );
    }

    public function actionStudentContract()
    {
        $univer = EUniversity::findCurrentUniversity();
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $searchModel = new EStudentContract();
        $dataProvider = $searchModel->search($this->getFilterParams());
        /*if (empty($searchModel->_group)) {
            $dataProvider->query->andWhere('1 <> 1');
        }*/
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

        if ($fileName = $this->get('file')) {
            $dir = Yii::getAlias('@backend/runtime/export/');
            $baseName = basename($dir . $fileName);

            if (file_exists($dir . $baseName)) {
                return Yii::$app->response->sendFile($dir . $baseName, $baseName);
            } else {
                $this->addError(__('File {name} not found', ['name' => $fileName]));
            }
            return $this->goBack();
        }

        if ($this->get('export')) {
            $query = $searchModel->search($this->getFilterParams(), false);
            $query->andFilterWhere(['e_student_contract.contract_status' => EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED]);
            $countQuery = clone $query;
            $limit = 1200;
            if ($countQuery->count() <= $limit) {
                $fileName = EStudentContract::generateContractDownloadFile($query);

                return Yii::$app->response->sendFile($fileName, basename($fileName));
            } else {
                /**
                 * @var $queue Queue
                 * @var $queue1 Queue
                 */
                $queue1 = Yii::$app->queue;
                $queue = Yii::$app->queueFile;
                $prefix = $queue1->channel;
                if ($queue1->redis->llen("$prefix.waiting") == 0) {
                    $queue = $queue1;
                }

                if ($queue
                    ->ttr(900)
                    ->push(
                        new ContractFileGenerateJob(
                            [
                                'filterParams' => $this->getFilterParams(),
                                'language' => Yii::$app->language,
                                'downloadUrl' => linkTo(['finance/student-contract', 'file' => '']),
                                'recipients' => [$this->_user()->contact->id],
                            ]
                        )
                    )) {
                    $this->addSuccess(
                        __(
                            'Shartnoma soni {limit} tadan oshganligi sababli, ro\'yxat generatsiya qilish navbatga qo\'yildi va tez orada Xabarlar sahifasiga jo\'natiladi.',
                            ['limit' => $limit]
                        )
                    );
                }

                return $this->redirect(['finance/student-contract']);
            }
        }

        if ($code = $this->get('code')) {
            if ($selected = EStudentContract::findOne(['id' => $code])) {

                $download = $this->get('download', -1);
                $file = $this->get('file');
                if ($download !== -1) {
                    if (is_array($selected->filename)) {
                        $files = $selected->filename;
                        if (isset($files['name'])) {
                            $file = Yii::getAlias('@root/') . $files['base_url'] . DS . $files['name'];

                            if (file_exists($file)) {
                                return Yii::$app->response->sendFile($file, $files['name']);
                            }
                        }
                    }

                    return $this->goHome();
                }


                if ($this->get('delete')) {
                    try {
                        if ($selected->contract_status == EStudentContractType::CONTRACT_REQUEST_STATUS_SEND) {
                            $type = EStudentContractType::findOne(['id' => $selected->_student_contract_type]);
                            if ($selected->delete()) {
                                if ($type->delete()) {
                                    $this->addSuccess(__('Contract [{code}] is deleted successfully', ['code' => $selected->id]));
                                    return $this->redirect(['finance/student-contract']);
                                }
                            }

                        } else {
                            $this->addError(
                                __('Contract can not delete')
                            );
                            return $this->redirect(Yii::$app->request->referrer);
                        }

                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['finance/student-contract', 'code' => $selected->id, 'edit' => 1]);
                }


                /*if ($this->get('change-type')) {
                    $selected->scenario = EStudentContract::SCENARIO_CHANGE_TYPE;
                    if ($selected->load(Yii::$app->request->post())) {
                        if ($selected->save())
                            return $this->redirect(['finance/student-contract',
                                    'EStudentContract[_department]' => $selected->_department,
                                    'EStudentContract[_specialty]' => $selected->_specialty,
                                    'EStudentContract[_education_form]' => $selected->_education_form,
                                    'EStudentContract[_education_year]' => $selected->_education_year,
                                    'EStudentContract[_group]' => $selected->_group]
                            );
                    }
                    return $this->renderAjax('change-contract-type', [
                        'selected' => $selected,
                    ]);
                }*/

                if ($this->get('set')) {
                    //автоматик генерация: отм коди+таълим тури+таълим шакли+шартнома йили+4 хонали кетма-кетлик ракам
                    $num = EStudentContract::getCountContract($selected->_education_year);
                    //$number = $univer->code.$selected->_education_type.$selected->_education_form.$selected->_education_year.$num;

                    // $year = substr($selected->_education_year, -2);
                    // echo \common\models\system\Counter::getNextSequence('contract_counter_'.$year);
                    // \common\models\system\Counter::getNextSequence('contract_counter');
                    if ($selected->_contract_type == ContractType::CONTRACT_TYPE_BASE || $selected->_contract_type == ContractType::CONTRACT_TYPE_RECOMMEND || $selected->_contract_type == ContractType::CONTRACT_TYPE_FOREIGN) {
                        if ($selected->number == "") {
                            $year = substr($selected->_education_year, -2);
//                            \common\models\system\Counter::getNextSequence('contract_counter')
//                            \common\models\system\Counter::getNextSequence('contract_counter_'.$year);

                            $number = $univer->code . $year . $num;
                            $selected->number = $number;
                            // $selected->number = EStudentContract::validateNumber($selected, $selected->number);
                            $selected->number = EStudentContract::validateNumber($selected, $selected->number);
                        }
                        $selected->date = Yii::$app->formatter->asDate(time(), 'php:Y-m-d');

                    } else {
                        if ($selected->number == "") {
                            $this->addError(
                                __('Specific number is not given for the contract')
                            );
                            return $this->redirect(Yii::$app->request->referrer);
                        }
                    }


                    //$selected->date = Yii::$app->formatter->asDate(time(), 'php:Y-m-d');
                    $selected->university_code = $univer->code;
                    $selected->rector = (EEmployeeMeta::getRectorName() != null) ? EEmployeeMeta::getRectorName()->employee->fullname : '_______________________';
                    if ($selected->mailing_address == "") {
                        $selected->mailing_address = $univer->mailing_address;
                    }
                    if ($selected->bank_details == "") {
                        $selected->bank_details = $univer->bank_details;
                    }

                    $price = EContractPrice::getContractPrice($selected->_department, $selected->_specialty, $selected->_education_form, $selected->student->_student_type, EContractPrice::CONTRACT_LOCALITY_LOCAL);
                    if ($selected->_contract_type != ContractType::CONTRACT_TYPE_BASE) {
                        $price = EContractPrice::getContractPrice($selected->_department, $selected->_specialty, $selected->_education_form, StudentType::STUDENT_TYPE_SIMPLE, EContractPrice::CONTRACT_LOCALITY_LOCAL);
                    }

                    $stipend_rate = EStipendValue::getBaseStipendRate()->stipend_value;
                    $contract_type_coef = EContractType::getContractTypeByType($selected->_contract_type);

                    if ($stipend_rate === null) {
                        $this->addError(
                            __('A Base Stipend Scholarship has not been determined')
                        );
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                    if ($contract_type_coef === null) {
                        $this->addError(
                            __('A Contract Type Coefficient has not been determined')
                        );
                        return $this->redirect(Yii::$app->request->referrer);
                    }

                    if ($selected->_contract_type == ContractType::CONTRACT_TYPE_INCREASED_56 || $selected->_contract_type == ContractType::CONTRACT_TYPE_INCREASED_1) {
                        $increased = EIncreasedContractCoefficient::getContractCoef($selected->_department, $selected->_specialty);
                        if ($increased === null) {
                            $this->addError(
                                __('An Increased Contract Coefficient has not been determined for this specialty')
                            );
                            return $this->redirect(Yii::$app->request->referrer);
                        }
                    }

                    if ($selected->_contract_type == ContractType::CONTRACT_TYPE_FOREIGN) {
                        $foreign_price = EContractPrice::getContractPrice($selected->_department, $selected->_specialty, $selected->_education_form, false, EContractPrice::CONTRACT_LOCALITY_FOREIGN);
                        if ($foreign_price === null) {
                            $this->addError(
                                __('An Foreign Contract Summa has not been determined for this specialty')
                            );
                            return $this->redirect(Yii::$app->request->referrer);
                        }
                    }

                    if (Config::get(Config::CONFIG_COMMON_CONTRACT_CALCULATION) == 11) {

                        if ($minimum = EMinimumWage::getCurrentMinimumWage() === null) {
                            $this->addError(
                                __('A Minimum Wage coefficient has not been determined')
                            );
                            return $this->redirect(Yii::$app->request->referrer);
                        }
                        $min_wage = $minimum->name;

                        if ($price === null || !$price->coefficient) {
                            $this->addError(
                                __('An appropriate coefficient has not been determined for this specialty')
                            );
                            return $this->redirect(Yii::$app->request->referrer);
                        }

                        if ($selected->_contract_type == ContractType::CONTRACT_TYPE_BASE) {
                            $contract_summa_off = ($price->coefficient * $min_wage - 12 * $stipend_rate) * $contract_type_coef->coef;
                            $discount_diff = $contract_summa_off * $selected->discount / 100;

                            if ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_ON) {
                                $selected->summa = $price->coefficient * $min_wage * $contract_type_coef->coef - $discount_diff;
                            } elseif ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_OFF) {
                                $selected->summa = $contract_summa_off - $discount_diff;
                                if ($selected->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $selected->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_EVENING) {
                                    $contract_summa_on = ($price->coefficient * $min_wage) * $contract_type_coef->coef;
                                    $discount_diff = $contract_summa_on * $selected->discount / 100;

                                    $selected->summa = $contract_summa_on - $discount_diff;
                                } else
                                    $selected->summa = $contract_summa_off - $discount_diff;
                            }
                        } elseif ($selected->_contract_type == ContractType::CONTRACT_TYPE_RECOMMEND) {
                            $contract_summa_off = ($price->coefficient * $min_wage - 12 * $stipend_rate) * $contract_type_coef->coef;
                            $discount_diff = $contract_summa_off * $selected->discount / 100;

                            if ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_OFF) {
                                $selected->summa = $contract_summa_off - $discount_diff;
                                if ($selected->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $selected->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_EVENING) {
                                    $contract_summa_on = ($price->coefficient * $min_wage) * $contract_type_coef->coef;
                                    $discount_diff = $contract_summa_on * $selected->discount / 100;

                                    $selected->summa = $contract_summa_on - $discount_diff;
                                } else
                                    $selected->summa = $contract_summa_off - $discount_diff;
                            }
                        } elseif ($selected->_contract_type == ContractType::CONTRACT_TYPE_FOREIGN) {
                            $contract_summa_off = $foreign_price->summa * $contract_type_coef->coef;
                            $discount_diff = $contract_summa_off * $selected->discount / 100;

                            if ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_OFF) {
                                $selected->summa = $contract_summa_off - $discount_diff;
                                if ($selected->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $selected->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_EVENING) {
                                    $contract_summa_on = $contract_summa_off;
                                    $discount_diff = $contract_summa_on * $selected->discount / 100;

                                    $selected->summa = $contract_summa_on - $discount_diff;
                                } else
                                    $selected->summa = $contract_summa_off - $discount_diff;
                            }
                        } elseif ($selected->_contract_type == ContractType::CONTRACT_TYPE_INCREASED_56 || $selected->_contract_type == ContractType::CONTRACT_TYPE_INCREASED_1) {
                            if ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_ON) {
                                $selected->summa = $price->coefficient * $min_wage * $contract_type_coef->coef * $increased->coefficient;
                            } elseif ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_OFF) {
                                if ($selected->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $selected->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_EVENING)
                                    $selected->summa = ($price->coefficient * $min_wage) * $contract_type_coef->coef * $increased->coefficient;
                                else
                                    $selected->summa = ($price->coefficient * $min_wage - 12 * $stipend_rate) * $contract_type_coef->coef * $increased->coefficient;
                            }
                            $discount_diff = $selected->summa * $selected->discount / 100;
                            $selected->summa = $selected->summa - $discount_diff;
                        } else {
                            if ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_ON) {
                                $selected->summa = $price->coefficient * $min_wage * $contract_type_coef->coef;
                            } elseif ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_OFF) {
                                if ($selected->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $selected->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_EVENING)
                                    $selected->summa = ($price->coefficient * $min_wage) * $contract_type_coef->coef;
                                else
                                    $selected->summa = ($price->coefficient * $min_wage - 12 * $stipend_rate) * $contract_type_coef->coef;
                            }
                            $discount_diff = $selected->summa * $selected->discount / 100;
                            $selected->summa = $selected->summa - $discount_diff;
                        }

                        $selected->summa = $selected->summa * $selected->month_count / 12;
                        if ($selected->_graduate_type == EStudentContract::GRADUATE_TYPE_YES) {
                            if ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_ON) {
                                $selected->summa = $selected->summa - 2 * $stipend_rate;
                            }
                        }
                    } else {
                        if ($selected->_contract_type != ContractType::CONTRACT_TYPE_FOREIGN) {
                            if ($price === null || !$price->summa) {
                                $this->addError(
                                    __('An appropriate summa has not been determined for this specialty')
                                );
                                return $this->redirect(Yii::$app->request->referrer);
                            }
                        }
                        if ($selected->_contract_type == ContractType::CONTRACT_TYPE_BASE) {
                            //$contract_summa_off = ($price->coefficient * $min_wage - 12 * $stipend_rate) * $contract_type_coef->coef;
                            $contract_summa_off = $price->summa * $contract_type_coef->coef;
                            $discount_diff = $contract_summa_off * $selected->discount / 100;

                            if ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_ON) {
                                //$selected->summa = $price->coefficient * $min_wage * $contract_type_coef->coef - $discount_diff;
                                $selected->summa = $contract_summa_off + 12 * $stipend_rate - $discount_diff;
                            } elseif ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_OFF) {
                                $selected->summa = $contract_summa_off - $discount_diff;
                                if ($selected->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $selected->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_EVENING) {
                                    $contract_summa_on = $contract_summa_off;
                                    $discount_diff = $contract_summa_on * $selected->discount / 100;

                                    $selected->summa = $contract_summa_on - $discount_diff;
                                } else
                                    $selected->summa = $contract_summa_off - $discount_diff;
                            }
                        } elseif ($selected->_contract_type == ContractType::CONTRACT_TYPE_RECOMMEND) {
                            //$contract_summa_off = ($price->coefficient * $min_wage - 12 * $stipend_rate) * $contract_type_coef->coef;
                            $contract_summa_off = $price->summa * $contract_type_coef->coef;
                            $discount_diff = $contract_summa_off * $selected->discount / 100;

                            if ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_OFF) {
                                $selected->summa = $contract_summa_off - $discount_diff;
                                if ($selected->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $selected->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_EVENING) {
                                    $contract_summa_on = $contract_summa_off;
                                    $discount_diff = $contract_summa_on * $selected->discount / 100;

                                    $selected->summa = $contract_summa_on - $discount_diff;
                                } else
                                    $selected->summa = $contract_summa_off - $discount_diff;
                            }
                        } elseif ($selected->_contract_type == ContractType::CONTRACT_TYPE_FOREIGN) {
                            $contract_summa_off = $foreign_price->summa * $contract_type_coef->coef;
                            $discount_diff = $contract_summa_off * $selected->discount / 100;

                            if ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_OFF) {
                                $selected->summa = $contract_summa_off - $discount_diff;
                                if ($selected->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $selected->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_EVENING) {
                                    $contract_summa_on = $contract_summa_off;
                                    $discount_diff = $contract_summa_on * $selected->discount / 100;

                                    $selected->summa = $contract_summa_on - $discount_diff;
                                } else
                                    $selected->summa = $contract_summa_off - $discount_diff;
                            }
                        } elseif ($selected->_contract_type == ContractType::CONTRACT_TYPE_INCREASED_56 || $selected->_contract_type == ContractType::CONTRACT_TYPE_INCREASED_1) {
                            if ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_ON) {
                                //$selected->summa = $price->coefficient * $min_wage * $contract_type_coef->coef * $increased->coefficient;
                                $selected->summa = $price->summa * $contract_type_coef->coef * $increased->coefficient + 12 * $stipend_rate;
                            } elseif ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_OFF) {
                                if ($selected->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $selected->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_EVENING) {
                                    //$selected->summa = ($price->coefficient * $min_wage ) * $contract_type_coef->coef * $increased->coefficient;
                                    $selected->summa = $price->summa * $contract_type_coef->coef * $increased->coefficient;
                                } else {
                                    //$selected->summa = ($price->coefficient * $min_wage - 12 * $stipend_rate) * $contract_type_coef->coef * $increased->coefficient;
                                    $selected->summa = $price->summa * $contract_type_coef->coef * $increased->coefficient;
                                }
                            }
                            $discount_diff = $selected->summa * $selected->discount / 100;
                            $selected->summa = $selected->summa - $discount_diff;
                        } else {
                            if ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_ON) {
                                //$selected->summa = $price->coefficient * $min_wage * $contract_type_coef->coef;
                                $selected->summa = $price->summa * $contract_type_coef->coef + 12 * $stipend_rate;
                            } elseif ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_OFF) {
                                if ($selected->_education_form == EducationForm::EDUCATION_FORM_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_SPECIAL || $selected->_education_form == EducationForm::EDUCATION_FORM_SECOND_HIGHER_PART_TIME || $selected->_education_form == EducationForm::EDUCATION_FORM_EVENING)
                                    //$selected->summa = ($price->coefficient * $min_wage) * $contract_type_coef->coef;
                                    $selected->summa = $price->summa * $contract_type_coef->coef;
                                else
                                    // $selected->summa = ($price->coefficient * $min_wage - 12 * $stipend_rate) * $contract_type_coef->coef;
                                    $selected->summa = $price->summa * $contract_type_coef->coef;
                            }
                            $discount_diff = $selected->summa * $selected->discount / 100;
                            $selected->summa = $selected->summa - $discount_diff;
                        }

                        $selected->summa = $selected->summa * $selected->month_count / 12;
                        if ($selected->_graduate_type == EStudentContract::GRADUATE_TYPE_YES) {
                            if ($selected->_contract_summa_type == ContractSummaType::CONTRACT_SUMMA_TYPE_ON) {
                                $selected->summa = $selected->summa - 2 * $stipend_rate;
                            }
                        }
                    }

                    if ($selected->start_date === null)
                        $selected->start_date = date('Y') . '-09-15';
                    if ($selected->end_date === null)
                        $selected->end_date = date('Y') . '-10-01';

                    $selected->contract_status = EStudentContractType::CONTRACT_REQUEST_STATUS_READY;
                    //$selected->contract_form_type = EStudentContractType::CONTRACT_FORM_TWO;
                    //EStudentContract::validateNumber($selected, $selected->number);

                    //if($selected->validate()){
                    //print_r($selected->getErrors());
                    if ($selected->save(false)) {
                        $this->addSuccess(
                            __('Parameter of contract configured for this student.')
                        );
                        //return $this->redirect(Yii::$app->request->referrer);
                        return $this->redirect(['finance/student-contract',
                                'EStudentContract[_department]' => $selected->_department,
                                'EStudentContract[_specialty]' => $selected->_specialty,
                                'EStudentContract[_education_type]' => $selected->_education_type,
                                'EStudentContract[_education_form]' => $selected->_education_form,
                                'EStudentContract[_education_year]' => $selected->_education_year,
                                'EStudentContract[_group]' => $selected->_group,
                                'EStudentContract[contract_status]' => StudentContractType::CONTRACT_REQUEST_STATUS_READY]
                        );
                    }
                    //}


                }

                if ($this->get('edit')) {
                    if ($selected->accepted === EStudentContract::STATUS_ENABLE) {
                        $this->addError(
                            __('The contract cannot be edited')
                        );
                        return $this->redirect(['finance/student-contract']);
                        //return $this->redirect(Yii::$app->request->referrer);
                    }
                    $selected->scenario = EStudentContract::SCENARIO_CHANGE_TYPE;
                    if ($selected->load(Yii::$app->request->post())) {
                        $selected->contract_status = EStudentContractType::CONTRACT_REQUEST_STATUS_PROCESS;
                        /*if($selected->_contract_type != ContractType::CONTRACT_TYPE_BASE)
                            $selected->discount = 0.0;*/


                        if ($selected->save())
                            //return $this->redirect(['finance/student-contract']);
                            //return $this->redirect(Yii::$app->request->referrer);
                            return $this->redirect(['finance/student-contract',
                                    'EStudentContract[_department]' => $selected->_department,
                                    'EStudentContract[_specialty]' => $selected->_specialty,
                                    'EStudentContract[_education_type]' => $selected->_education_type,
                                    'EStudentContract[_education_form]' => $selected->_education_form,
                                    'EStudentContract[_education_year]' => $selected->_education_year,
                                    'EStudentContract[_group]' => $selected->_group,
                                    'EStudentContract[contract_status]' => StudentContractType::CONTRACT_REQUEST_STATUS_PROCESS]
                            );
                    }
                    return $this->render('change-contract-information', [
                        'selected' => $selected,
                        'univer' => $univer,
                    ]);
                }
                if ($this->get('generate-pdf')) {
                    if ($selected->start_date === null)
                        $selected->start_date = date('Y') . '-09-15';
                    if ($selected->end_date === null)
                        $selected->end_date = date('Y') . '-10-01';
                    $univer = EUniversity::findCurrentUniversity();
                    if ($selected->_contract_type == ContractType::CONTRACT_TYPE_FOREIGN) {
                        return $this->render('_contract-view-foreign-information', [
                            'selected' => $selected,
                            'univer' => $univer,
                        ]);
                    } else {
                        if ($selected->contract_form_type === EStudentContractType::CONTRACT_FORM_TWO) {
                            return $this->render('_contract-view-biliteral-information', [
                                'selected' => $selected,
                                'univer' => $univer,
                            ]);
                        } else {
                            return $this->render('_three-sided-information', [
                                'selected' => $selected,
                                'univer' => $univer,
                            ]);
                        }
                    }

                }
                if ($this->get('ready-pdf')) {
                    $univer = EUniversity::findCurrentUniversity();

                    // get your HTML raw content without any layouts or scripts
                    if ($selected->_contract_type == ContractType::CONTRACT_TYPE_FOREIGN) {
                        $content = $this->renderPartial('_contract-view-foreign-information', [
                            'selected' => $selected,
                            'univer' => $univer,
                        ]);
                    } else {
                        if ($selected->contract_form_type === EStudentContractType::CONTRACT_FORM_TWO) {
                            $content = $this->renderPartial('_contract-view-biliteral-information', [
                                'selected' => $selected,
                                'univer' => $univer,
                            ]);
                        } else {
                            $content = $this->renderPartial('_three-sided-information', [
                                'selected' => $selected,
                                'univer' => $univer,
                            ]);
                        }
                    }
                    //$destination = Pdf::DEST_DOWNLOAD;

                    $pdf = new Pdf([
                        'mode' => Pdf::MODE_UTF8,
                        'format' => Pdf::FORMAT_A4,
                        'orientation' => Pdf::ORIENT_PORTRAIT,
                        'destination' => Pdf::DEST_BROWSER,
                        'filename' => $selected->number . '.pdf',
                        'content' => $content,
                        //'cssFile' => '@backend/assets/app/css/pdf-print.css',
                        'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.css',

                        'cssInline' => 'body {font-size:14px !important; font-family: "Times New Roman" !important} .title {text-indent:32px !important} .bank_detail{background-color: white !important;
        font-family: "Times New Roman" !important;
        font-size: 14px !important;
        padding: 0 !important;
        border: 0}',
                        'methods' => [
                            'SetHeader' => [date("d.m.Y")],
                            'SetFooter' => ['{PAGENO}'],
                        ]
                    ]);
                    //return $pdf->render();
                    $content1 = $pdf->content;
                    $filename = $pdf->filename;
                    $dir = Yii::getAlias("@root/private/contract") . DS . $selected->_education_year . DS . $selected->_department;
                    if (!file_exists($dir . DS . $filename)) {
                        if (!is_dir($dir)) {
                            FileHelper::createDirectory($dir, 0777);
                        }
                    }
                    try {
                        $path = $pdf->Output($content1, $dir . DS . $filename, \Mpdf\Output\Destination::FILE);
                        $data['name'] = $filename;
                        $data['order'] = "";
                        $data['type'] = "application/pdf";
                        $data['base_url'] = '/private/contract/' . $selected->_education_year . '/' . $selected->_department;
                        $selected->filename = $data;
                        $selected->contract_status = EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED;
                        $selected->accepted = EStudentContract::STATUS_ENABLE;
                        $selected->different = $selected->summa;

                        $selected->different = $selected->summa - EStudentContract::getTotal($selected->paidContractFee, 'summa');
                        if ($selected->different > 0)
                            $selected->different_status = EStudentContract::DIFFERENT_DEBTOR_STATUS;
                        elseif ($selected->different == 0)
                            $selected->different_status = EStudentContract::DIFFERENT_EQUAL_STATUS;
                        else
                            $selected->different_status = EStudentContract::DIFFERENT_NOT_DEBTOR_STATUS;


                        if ($selected->start_date === null)
                            $selected->start_date = date('Y') . '-09-15';
                        if ($selected->end_date === null)
                            $selected->end_date = date('Y') . '-10-01';
                        $selected->save(false);
                        $this->addSuccess(
                            __('The contract file was created successfully')
                        );

                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }


                }


            }
        }
        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }

    public function actionSetStudentContractType()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->search_for_contract($this->getFilterParams());
        $faculty = "";
        $list = array();
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
        if (empty($searchModel->_group)) {
            $dataProvider->query->andWhere('1 <> 1');
        }


        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }


    public function actionToSetStudentContractType()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        if (Yii::$app->request->post('selection') && Yii::$app->request->post('contract_summa_type') && Yii::$app->request->post('contract_type') && Yii::$app->request->post('contract_form_type') && Yii::$app->request->post('department') && Yii::$app->request->post('specialty') && Yii::$app->request->post('education_year') && Yii::$app->request->post('education_form') && Yii::$app->request->post('group')) {
            $selection = (array)Yii::$app->request->post('selection');

            $contract_summa_type = $this->post('contract_summa_type');
            $contract_summa_type = urldecode($contract_summa_type);
            parse_str($contract_summa_type, $get_contract_summa_type);

            $contract_type = $this->post('contract_type');
            $contract_type = urldecode($contract_type);
            parse_str($contract_type, $get_contract_type);

            $contract_form_type = $this->post('contract_form_type');
            $contract_form_type = urldecode($contract_form_type);
            parse_str($contract_form_type, $get_contract_form_type);

            $department = Yii::$app->request->post('department');
            $specialty = Yii::$app->request->post('specialty');
            $education_year = Yii::$app->request->post('education_year');
            $education_form = Yii::$app->request->post('education_form');
            $group = Yii::$app->request->post('group');
            $education_type = ESpecialty::findOne(['id' => $specialty])->_education_type;
            $contract_summas = array();
            $contract_types = array();
            $contract_form_types = array();
            foreach ($get_contract_summa_type as $key => $item) {
                foreach ($item as $key2 => $item2) {
                    $contract_summas[$key2] = $item2;
                }
            }

            foreach ($get_contract_type as $key => $item) {
                foreach ($item as $key2 => $item2) {
                    $contract_types[$key2] = $item2;
                }
            }

            foreach ($get_contract_form_type as $key => $item) {
                foreach ($item as $key2 => $item2) {
                    $contract_form_types[$key2] = $item2;
                }
            }

            $students = array();
            foreach ($selection as $id) {
                $student = EStudentMeta::findOne((int)$id);
                $students[$student->_student] = $student->_student;
            }

            foreach ($contract_summas as $key => $item) {
                if (in_array($key, $students)) {
                    try {
                        $model = EStudentContractType::findOne([
                            '_student' => $key,
                            '_department' => $department,
                            '_specialty' => $specialty,
                            '_education_year' => $education_year,
                            '_education_form' => $education_form,
                        ]);
                        if ($model === null)
                            $model = new EStudentContractType();
                        //$model->scenario = EStudentContractType::SCENARIO_CREATE;
                        $model->_student = $key;
                        $model->_contract_summa_type = $item;
                        $model->contract_form_type = $contract_form_types[$key];
                        $model->_department = $department;
                        $model->_specialty = $specialty;
                        $model->_education_year = $education_year;
                        $model->_education_form = $education_form;
                        $model->contract_status = EStudentContractType::CONTRACT_REQUEST_STATUS_PROCESS;

                        //  $model->position = 0;
                        //$model->_created_self = EStudentContractType::STATUS_DISABLE;
                        $model->contract_status = EStudentContractType::CONTRACT_REQUEST_STATUS_PROCESS;
                        if ($model->save(false)) {
                            $studentContract = new EStudentContract();
                            $studentContract->_student_contract_type = $model->id;
                            $studentContract->_contract_summa_type = $model->_contract_summa_type;
                            $studentContract->contract_form_type = $model->contract_form_type;
                            $studentContract->_contract_type = $contract_types[$key];
                            $studentContract->_student = $model->_student;
                            $studentContract->_specialty = $model->_specialty;
                            $studentContract->_department = $model->_department;
                            $studentContract->_education_type = $education_type;
                            $studentContract->_education_form = $model->_education_form;
                            $studentContract->_education_year = $model->_education_year;
                            $studentContract->_graduate_type = EStudentContract::GRADUATE_TYPE_NO;
                            $studentContract->contract_status = $model->contract_status;
                            $studentContract->_group = $group;
                            $studentContract->_curriculum = EGroup::findOne($group)->_curriculum;
                            $studentContract->_level = Semester::getCourseCode($studentContract->_curriculum, $model->_education_year)->_level;
                            //$studentContract->date = null;
                            $studentContract->summa = null;
                            if ($studentContract->save(false)) {
                                $this->addSuccess(
                                    __('Types of contracts were identified for students in the group.')
                                );
                            }


                            $this->addSuccess(
                                __('Types of contracts were identified for students in the group.')
                            );

                        } else {
                            $e2 = new Exception();
                            if ($e2->getCode() == 0) {
                                $this->addError(__('Student have been already setted for this education year'));
                            } else {
                                $this->addError($e2->getMessage());
                            }
                        }
                    } catch (Exception $e) {
                        if ($e->getCode() == 23505) {
                            $this->addError(__('Student have been already setted for this education year'));
                        } else {
                            $this->addError($e->getMessage());
                        }
                    }
                }
            }
            return $this->redirect(['finance/student-contract',
                'EStudentContract[_department]' => $department,
                'EStudentContract[_specialty]' => $specialty,
                'EStudentContract[_education_type]' => $education_type,
                'EStudentContract[_education_form]' => $education_form,
                'EStudentContract[_education_year]' => $education_year,
                'EStudentContract[_group]' => $group]);
        }

    }

    public function actionPaidContractFee()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        if ($code = $this->get('contract')) {
            if ($contract = EStudentContract::findOne(['id' => $code])) {

            } else {
                return $this->redirect(Yii::$app->request->referrer);
            }
        }

        $model = new EPaidContractFee();
        $model->scenario = EPaidContractFee::SCENARIO_CREATE;
        $searchModel = new EPaidContractFee();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_student_contract' => $contract->id]);

        if ($code = $this->get('code')) {
            if ($model = EPaidContractFee::findOne(['id' => $code])) {
                $model->scenario = EPaidContractFee::SCENARIO_CREATE;
                $student_contract = $model->_student_contract;
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $contract = EStudentContract::findOne($student_contract);
                            if ($contract !== null) {
                                $contract->different = $contract->summa - EStudentContract::getTotal($contract->paidContractFee, 'summa');
                                if ($contract->different > 0)
                                    $contract->different_status = EStudentContract::DIFFERENT_DEBTOR_STATUS;
                                elseif ($contract->different == 0)
                                    $contract->different_status = EStudentContract::DIFFERENT_EQUAL_STATUS;
                                else
                                    $contract->different_status = EStudentContract::DIFFERENT_NOT_DEBTOR_STATUS;
                                $contract->save(false);

                            }
                            $this->addSuccess(__('Paid Contract Fee [{code}] is deleted successfully', ['code' => $model->id]));

                            return $this->redirect(['finance/paid-contract-fee', 'contract' => $contract->id]);
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['finance/paid-contract-fee', 'contract' => $contract->id]);
                }
            } else {
                return $this->redirect(['finance/paid-contract-fee', 'contract' => $contract->id]);
            }
        }
        $model->_student_contract = $contract->id;
        $model->_education_year = $contract->_education_year;
        $model->_student = $contract->_student;
        if ($contract->accepted === EStudentContract::STATUS_DISABLE) {
            $this->addError(
                __('It is not possible to enter a fee for the contract')
            );
            return $this->redirect(Yii::$app->request->referrer);
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            /*$contract->different = $contract->summa - EStudentContract::getTotal($contract->paidContractFee, 'summa');
            if($contract->different > 0)
                $contract->different_status = EStudentContract::DIFFERENT_DEBTOR_STATUS;
            else if($contract->different == 0)
                $contract->different_status = EStudentContract::DIFFERENT_EQUAL_STATUS;
            else
                $contract->different_status = EStudentContract::DIFFERENT_NOT_DEBTOR_STATUS;
            $contract->save();*/
            if ($code) {
                $this->addSuccess(__('Paid Contract Fee [{code}] updated successfully', ['code' => $model->id]));
            } else {
                $this->addSuccess(__('Paid Contract Fee [{code}] created successfully', ['code' => $model->id]));
            }

        }
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
            'contract' => $contract,
        ]);
    }

    public function actionPaymentMonitoring()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $searchModel = new EStudentContract();
        $dataProvider = $searchModel->search_payment($this->getFilterParams(), true, $this->_user());
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
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear()->code;
            $dataProvider->query->andFilterWhere(['e_student_contract._education_year' => EducationYear::getCurrentYear()->code]);
        }
        if ($fileName = $this->get('file')) {
            $dir = Yii::getAlias('@backend/runtime/export/');
            $baseName = basename($dir . $fileName);

            if (file_exists($dir . $baseName)) {
                return Yii::$app->response->sendFile($dir . $baseName, $baseName);
            } else {
                $this->addError(__('File {name} not found', ['name' => $fileName]));
            }
            return $this->goBack();
        }

        if ($this->get('download')) {
            $education_year = $this->get('education_year');
            $query = $searchModel->search_payment($this->getFilterParams(), false, $this->_user());
            $query->andFilterWhere(['e_student_contract._education_year' => $education_year]);
            $countQuery = clone $query;
            $limit = 2000;
            if ($countQuery->count() <= $limit) {
                $fileName = EStudentContract::generateDownloadFile($query);

                return Yii::$app->response->sendFile($fileName, basename($fileName));
            } else {
                /**
                 * @var $queue Queue
                 * @var $queue1 Queue
                 */
                $queue1 = Yii::$app->queue;
                $queue = Yii::$app->queueFile;
                $prefix = $queue1->channel;
                if ($queue1->redis->llen("$prefix.waiting") == 0) {
                    $queue = $queue1;
                }

                if ($queue
                    ->ttr(900)
                    ->push(
                        new ContractListFileGenerateJob(
                            [
                                'filterParams' => $this->getFilterParams(),
                                'education_year' => $education_year,
                                'language' => Yii::$app->language,
                                'downloadUrl' => linkTo(['finance/payment-monitoring', 'file' => '']),
                                'recipients' => [$this->_user()->contact->id],
                            ]
                        )
                    )) {
                    $this->addSuccess(
                        __(
                            'Shartnoma to`lovlari soni {limit} tadan oshganligi sababli, ro\'yxat generatsiya qilish navbatga qo\'yildi va tez orada Xabarlar sahifasiga jo\'natiladi.',
                            ['limit' => $limit]
                        )
                    );
                }

                return $this->redirect(['finance/payment-monitoring']);
            }
        }
        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }

    public function actionPaymentMonitoringGroup()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING && $this->_user()->role->code !== AdminRole::CODE_DEAN) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $searchModel = new EStudentContract();
        $dataProvider = $searchModel->search_payment($this->getFilterParams(), true, $this->_user());
        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $searchModel->_department = $faculty;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        if ($searchModel->_education_year == null) {
            //$searchModel->_education_year = EducationYear::getCurrentYear()->code;
            //$dataProvider->query->andFilterWhere(['e_student_contract._education_year' => EducationYear::getCurrentYear()->code]);
        }

        if (empty($searchModel->_department) || empty($searchModel->_specialty) || empty($searchModel->_education_form)) {
            $dataProvider->query->andWhere('1 <> 1');
        }

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }

    /*public function actionScholarship($scholarship = false, $month = false, $delete = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_MARKETING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $searchModel = new EStudentScholarship();
        $dataProvider = $searchModel->search($this->getFilterParams());
        //if (empty($searchModel->_group)) {
         //   $dataProvider->query->andWhere('1 <> 1');
        //}
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


        if ($code = $this->get('code')) {
            if ($selected = EStudentScholarship::findOne(['id' => $code])) {

                if ($this->get('view')){

                    $searchModelMonths = new EStudentScholarshipMonth();
                    $dataProviderMonths = $searchModelMonths->search($this->getFilterParams());
                    $dataProviderMonths->query->andFilterWhere(['_student_scholarship' => $selected->id]);
                    $model = new EStudentScholarshipMonth();
                    return $this->render('scholarship-information', [
                        'selected' => $selected,
                        'searchModelMonths' => $searchModelMonths,
                        'dataProviderMonths' => $dataProviderMonths,
                        'model' => $model,
                    ]);
                }
            }
        }
        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }*/

    public function actionScholarship($scholarship = false, $month = false, $edit = false, $delete = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING && $this->_user()->role->code !== AdminRole::CODE_DEAN) {
            $this->addInfo(
                __('This page is for the accounting and dean only.')
            );
            return $this->goHome();
        }
        /**
         * @var $meta EStudentContract
         */
        $model = null;
        $meta = null;

        $user = $this->_user();
        $department = false;


        if ($month) {
            $model = EStudentScholarshipMonth::findOne(['id' => $month]);
            $model->scenario = EStudentScholarshipMonth::SCENARIO_CREATE;
            $meta = EStudentScholarship::findOne([
                'id' => $model->_student_scholarship
            ]);
            if ($delete) {
                try {
                    if ($model->delete()) {
                        $this->addSuccess(__('{number} month has been deleted ', ['number' => $model->id]));
                        return $this->redirect(['scholarship', 'scholarship' => $model->_student_scholarship]);
                    }
                } catch (\Exception $exception) {
                    $this->addError($exception->getMessage());
                    return $this->redirect(['scholarship', 'month' => $model->id]);
                }
                return $this->redirect(['scholarship', 'scholarship' => $model->_student_scholarship]);
            }
        }

        if ($scholarship) {
            if ($meta = EStudentScholarship::findOne([
                'id' => $scholarship,
            ])) {
                $model = new EStudentScholarshipMonth();
                $model->scenario = EStudentScholarshipMonth::SCENARIO_CREATE;
                $searchModel = new EStudentScholarshipMonth();
                $dataProvider = $searchModel->search($this->getFilterParams());
                $dataProvider->query->andFilterWhere(['_student_scholarship' => $meta->id]);

                if ($delete) {
                    try {
                        if ($meta->delete()) {
                            $this->addSuccess(__('{number} scholarship has been deleted ', ['number' => $meta->id]));
                            return $this->redirect(['scholarship']);
                        }
                    } catch (\Exception $exception) {
                        if ($exception->getCode() == 23503) {
                            $this->addError(__('Could not delete related data'));
                        } else {
                            $this->addError($exception->getMessage());
                        }
                        return $this->redirect(['scholarship', 'scholarship' => $meta->id]);
                    }
                    return $this->redirect(['scholarship', '$meta' => $model->id]);
                }

            }
        }

        if ($model) {
            if ($edit) {
                if ($model->isNewRecord) {
                    $model->_student = $meta->_student;
                    $model->_student_scholarship = $meta->id;
                    $model->_stipend_rate = $meta->_stipend_rate;
                    $model->_education_year = $meta->_education_year;
                    $model->_semester = $meta->_semester;
                    $model->summa = $meta->summa;
                }


                $model->scenario = EStudentScholarshipMonth::SCENARIO_CREATE;


                if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($model);
                }
                if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                    try {
                        if ($model->save()) {
                            $this->addSuccess(__(
                                $month ?
                                    'The scholarship month for student {name} has been updated' :
                                    'The Scholarship month for student {name} has been created',
                                [
                                    'name' => $model->student->getFullName()
                                ]));
                            return $this->redirect(['scholarship', 'scholarship' => $model->_student_scholarship]);
                        } else {
                            $this->addError($model->getOneError());
                        }
                    } catch (\Exception $exception) {
                        $this->addError($exception->getMessage());
                        return $this->refresh();
                    }
                }

                return $this->renderAjax('_scholarship_month_summa', [
                    'model' => $model,
                ]);
            }


            return $this->render('scholarship-information', [
                'model' => $model,
                'meta' => $meta,
                'department' => $department,
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
        }


        $searchModel = new EStudentScholarship();
        $dataProvider = $searchModel->search($this->getFilterParams());

        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
            $dataProvider->query->andFilterWhere(['_department' => $department]);
        }

        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            //'dataProvider' => $searchModel->searchForInvoice($this->getFilterParams(), $department),
        ]);
    }

    public function actionSetScholarship()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING && $this->_user()->role->code !== AdminRole::CODE_DEAN) {
            $this->addInfo(
                __('This page is for the accounting and dean only.')
            );
            return $this->goHome();
        }
        $type_model = null;
        /*if ($type = $this->get('type')) {
            $type_model = StipendRate::findOne($type);

        }*/

        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->search_for_scholarship($this->getFilterParams());
        $faculty = "";
        $list = array();
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $dataProvider->query->andFilterWhere(['_department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        if (empty($searchModel->_group)) {
            $dataProvider->query->andWhere('1 <> 1');
        }
        if (empty($searchModel->_semestr)) {
            $dataProvider->query->andWhere('1 <> 1');
        }

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
                'type_model' => $type_model,
            ]
        );
    }

    public function actionToSetScholarship()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING && $this->_user()->role->code !== AdminRole::CODE_DEAN) {
            $this->addInfo(
                __('This page is for the accounting and dean only.')
            );
            return $this->goHome();
        }
        if (Yii::$app->request->post('selection') && Yii::$app->request->post('stipend_rate') && Yii::$app->request->post('start_date') && Yii::$app->request->post('education_type') && Yii::$app->request->post('curriculum') && Yii::$app->request->post('group') && Yii::$app->request->post('semester') && Yii::$app->request->post('decree')) {

            $selection = (array)Yii::$app->request->post('selection');

            $stipend_rate = $this->post('stipend_rate');
            $stipend_rate = urldecode($stipend_rate);
            parse_str($stipend_rate, $get_stipend_rate);

            $start_date = $this->post('start_date');
            $start_date = urldecode($start_date);
            parse_str($start_date, $get_start_date);

            $end_date = $this->post('end_date');
            $end_date = urldecode($end_date);
            parse_str($end_date, $get_end_date);

            $education_type = Yii::$app->request->post('education_type');
            $curriculum = Yii::$app->request->post('curriculum');
            $group = Yii::$app->request->post('group');
            $semester = Yii::$app->request->post('semester');
            //$decree = Yii::$app->request->post('decree');

            $stipend_rates = array();
            $start_dates = array();
            $end_dates = array();
            foreach ($get_stipend_rate as $key => $item) {
                foreach ($item as $key2 => $item2) {
                    $stipend_rates[$key2] = $item2;
                }
            }

            foreach ($get_start_date as $key => $item) {
                foreach ($item as $key2 => $item2) {
                    $start_dates[$key2] = $item2;
                }
            }

            foreach ($get_end_date as $key => $item) {
                foreach ($item as $key2 => $item2) {
                    $end_dates[$key2] = $item2;
                }
            }

            if ($order_number = Yii::$app->request->post('decree')) {

                $success = 0;
                if ($decree = EDecree::findOne(['id' => $order_number, 'status' => EDecree::STATUS_ENABLE])) {

                    $transaction = Yii::$app->db->beginTransaction();
                    $data = [];
                    $user = $this->_user();
                    try {
                        foreach ($selection as $id) {
                            if ($student = EStudentMeta::findOne((int)$id)) {
                                $model = new EStudentScholarship();
                                $model->_student = $student->_student;
                                $model->_department = $student->_department;
                                $model->_specialty = $student->_specialty_id;
                                $model->_education_type = $education_type;
                                $model->_education_form = $student->_education_form;
                                $model->_curriculum = $curriculum;
                                $model->_group = $group;
                                $model->_payment_form = $student->_payment_form;
                                $model->_semester = $semester;
                                $model->_education_year = $student->_education_year;
                                $model->_stipend_rate = $stipend_rates[$student->_student];
                                $model->_decree = $decree->id;
                                $model->summa = EStipendValue::getStipendRateValue($stipend_rates[$student->_student])->stipend_value;
                                $model->start_date = $start_dates[$student->_student];
                                $model->end_date = $end_dates[$student->_student];

                                if ($model->save(false)) {

                                    $start_date = (new \DateTime($start_dates[$student->_student]))->modify('first day of this month');;
                                    $end_date = (new \DateTime($end_dates[$student->_student]))->modify('last day of this month');;
                                    $interval = \DateInterval::createFromDateString('1 month');
                                    $period = new \DatePeriod($start_date, $interval, $end_date);
                                    foreach ($period as $dt) {
                                        $month_data[] = [
                                            '_student' => $model->_student,
                                            '_student_scholarship' => $model->id,
                                            '_stipend_rate' => $model->_stipend_rate,
                                            '_education_year' => $model->_education_year,
                                            '_semester' => $model->_semester,
                                            'summa' => $model->summa,
                                            'month_name' => $dt->format("Y-m-d"),
                                            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                                            'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),

                                        ];
                                    }
                                    //print_r($month_data);


                                    $data[] = [
                                        '_decree' => $decree->id,
                                        '_student' => $model->_student,
                                        '_admin' => $user->id,
                                        '_student_meta' => $student->id,
                                        'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                                    ];
                                    $success++;
                                }
                            }
                        }
                        if ($success) {
                            Yii::$app->db
                                ->createCommand()
                                ->batchInsert('e_decree_student', array_keys($data[0]), $data)
                                ->execute();
                            Yii::$app->db
                                ->createCommand()
                                ->batchInsert('e_student_scholarship_month', array_keys($month_data[0]), $month_data)
                                ->execute();
                            $transaction->commit();
                            $this->addSuccess(__('Decree {number} at {date} applied to {count} students',
                                [
                                    'number' => $decree->number,
                                    'date' => $decree->date->format('Y-m-d'),
                                    'count' => $success,
                                ]
                            ));
                        }

                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                }
            }

            return $this->redirect(['finance/scholarship',
                'EStudentScholarship[_education_type]' => $education_type,
                'EStudentScholarship[_curriculum]' => $curriculum,
                'EStudentScholarship[_group]' => $group,
                'EStudentScholarship[_semester]' => $semester
            ]);
        }

    }

    public function actionUzasboData($id = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $model = new EStudent();
        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->search_uzasbo($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);


        $faculty = "";
        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $dataProvider->query->andFilterWhere(['e_student_meta._department' => $faculty]);
                $searchModel->_department = $faculty;
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }

        if ($id) {
            $student = $this->findStudentMetaModel($id);
            $model = EStudent::findOne(['id' => $student->_student]);
            $model->scenario = EStudent::SCENARIO_INSERT_UZASBO;
            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    $this->addSuccess(__('Uzasbo data [{id}] updated successfully', ['id' => $model->id]));
                    return $this->redirect(['uzasbo-data', 'id' => $model->id]);
                }
            }
        }


        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'model' => @$model,
                'student' => @$student,
                'faculty' => @$faculty,
            ]
        );
    }

    public function actionControlContract()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_FINANCE_CONTROL) {
            $this->addInfo(
                __('This page is for the finance-control only.')
            );
            return $this->goHome();
        }
        $searchModel = new EStudentContract();
        $dataProvider = $searchModel->search_control($this->getFilterParams());

        if ($code = $this->get('code')) {
            if ($selected = EStudentContract::findOne(['id' => $code])) {
                $download = $this->get('download', -1);
                $file = $this->get('file');
                if ($download !== -1) {
                    if (is_array($selected->filename)) {
                        $files = $selected->filename;
                        if (isset($files['name'])) {
                            $file = Yii::getAlias('@root/') . $files['base_url'] . DS . $files['name'];

                            if (file_exists($file)) {
                                return Yii::$app->response->sendFile($file, $files['name']);
                            }
                        }
                    }
                    return $this->goHome();
                }
            }
        }

        if ($attribute = $this->get('attribute')) {
            if ($model = EStudentContract::findOne(['id' => $this->get('id')])) {
                /*if(count($model->paidContractFee) > 0){
                    $this->addError(
                        __('The contract status cannot be changed')
                    );
                    return $this->redirect(Yii::$app->request->referrer);
                }*/

                $model->$attribute = !$model->$attribute;
                if ($model->save(false)) {
                    if ($model->accepted == EStudentContract::STATUS_ENABLE) {
                        $this->addSuccess(__('Item [{id}] of contract is enabled', ['id' => $model->id]), true, true);
                    } else {
                        $this->addSuccess(__('Item [{id}] of contract is disabled', ['id' => $model->id]), true, true);
                    }
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return $this->redirect(Yii::$app->request->referrer);
                    //
                    //                 return [];
                }
            }
        }

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }


    public function actionStudentContractManual()
    {
        $univer = EUniversity::findCurrentUniversity();
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        $searchModel = new EStudentContract();
        $dataProvider = $searchModel->search_manual($this->getFilterParams());
        /*if (empty($searchModel->_group)) {
            $dataProvider->query->andWhere('1 <> 1');
        }*/
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


        if ($code = $this->get('code')) {
            if ($selected = EStudentContract::findOne(['id' => $code])) {

                if ($this->get('delete')) {
                    try {
                        $paid = EStudentContract::getTotal($selected->paidContractFee, 'summa');
                        if ($paid == 0) {

                            if ($selected->delete()) {

                                $this->addSuccess(__('Contract [{code}] is deleted successfully', ['code' => $selected->id]));
                                return $this->redirect(['finance/student-contract-manual']);

                            }

                        } else {
                            $this->addError(
                                __('Contract can not delete')
                            );
                            return $this->redirect(Yii::$app->request->referrer);
                        }

                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    //return $this->redirect(['finance/student-contract-manual', 'code'=>$selected->id, 'edit'=>1]);
                }


            }
        }
        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }

    public function actionStudentContractManualEdit($student = false, $contract = false, $delete = false)
    {
        /**
         * @var $meta EStudentMeta
         */
        $model = null;
        $meta = null;

        $user = $this->_user();
        $faculty = false;

        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }

        if ($contract) {
            $model = EStudentContract::findOne(['id' => $contract, '_manual_type' => EStudentContract::MANUAL_STATUS_TYPE_MANUAL]);
            /*if ($delete) {
                try {
                    if ($model->delete()) {
                        $this->addSuccess(__('{number} transcript has been deleted ', ['number' => $model->academic_register_number]));
                        return $this->redirect(['transcript']);
                    }
                } catch (\Exception $exception) {
                    $this->addError($exception->getMessage());
                    return $this->redirect(['transcript-edit', 'transcript' => $model->id]);
                }
                return $this->redirect(['transcript']);
            }*/


            $contractFeeSearchModel = new EPaidContractFee();
            $contractFeeDataProvider = $contractFeeSearchModel->search($this->getFilterParams());
            $contractFeeDataProvider->query->andFilterWhere(['_student_contract' => $model->id]);


        }

        if ($payment = $this->get('payment')) {
            $contractFee = new EPaidContractFee();
            if ($code = $this->get('code')) {
                $contractFee = EPaidContractFee::findOne($code);

                if ($this->get('delete')) {
                    try {
                        $student_contract = $contractFee->_student_contract;
                        if ($contractFee->delete()) {
                            $contract = EStudentContract::findOne($student_contract);
                            if ($contract !== null) {
                                $contract->different = $contract->summa - EStudentContract::getTotal($contract->paidContractFee, 'summa');
                                if ($contract->different > 0)
                                    $contract->different_status = EStudentContract::DIFFERENT_DEBTOR_STATUS;
                                elseif ($contract->different == 0)
                                    $contract->different_status = EStudentContract::DIFFERENT_EQUAL_STATUS;
                                else
                                    $contract->different_status = EStudentContract::DIFFERENT_NOT_DEBTOR_STATUS;
                                $contract->save(false);

                            }
                            $this->addSuccess(__('Paid Contract Fee [{code}] is deleted successfully', ['code' => $model->id]));

                            return $this->redirect(['finance/student-contract-manual-edit', 'contract' => $model->id]);
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['finance/student-contract-manual-edit', 'contract' => $model->id]);
                }
            }


            $contractFee->scenario = EPaidContractFee::SCENARIO_CREATE;
            $contractFee->_student_contract = $model->id;
            $contractFee->_education_year = $model->_education_year;
            $contractFee->_student = $model->_student;
            $contractFee->_manual_type = EStudentContract::MANUAL_STATUS_TYPE_MANUAL;
            /*if($model->accepted === EStudentContract::STATUS_DISABLE){
                $this->addError(
                    __('It is not possible to enter a fee for the contract')
                );
                return $this->redirect(Yii::$app->request->referrer);
            }*/
            if ($contractFee->load(Yii::$app->request->post()) && $contractFee->save()) {
                if ($code) {
                    $this->addSuccess(__('Paid Contract Fee [{code}] updated successfully', ['code' => $contractFee->id]));
                } else {
                    $this->addSuccess(__('Paid Contract Fee [{code}] created successfully', ['code' => $contractFee->id]));
                }
                return $this->redirect(['finance/student-contract-manual-edit', 'contract' => $model->id]);

            }
            return $this->renderAjax('_student_contract_manual_payment', [
                'model' => $contractFee,
            ]);
        }


        if ($student) {
            if ($meta = EStudentMeta::findOne([
                'id' => $student,
                'active' => true,
                '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED
            ])) {
                $model = EStudentContract::findOne(['_student' => $meta->_student, '_education_year' => $meta->_education_year, '_manual_type' => EStudentContract::MANUAL_STATUS_TYPE_MANUAL]);
                if ($model === null) {
                    $model = new EStudentContract([
                        '_student' => $meta->_student,
                        '_education_year' => $meta->_education_year,
                        '_manual_type' => EStudentContract::MANUAL_STATUS_TYPE_MANUAL,
                        'contract_status' => EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED,
                    ]);

                }
                // $model->scenario = EAcademicInformation::SCENARIO_INSERT;
                $model->scenario = EStudentContract::SCENARIO_CONTRACT_MANUAL;
            }
        }

        if ($model) {
            $model->scenario = EStudentContract::SCENARIO_CONTRACT_MANUAL;
            /*if ($semester) {
                $model->_semester = $semester;
            }*/
            if ($model->isNewRecord) {
                if ($meta) {
                    $model->setAttributes([
                        '_student' => $meta->_student,
                        '_student_meta' => $meta->id,
                        '_curriculum' => $meta->_curriculum,
                        '_education_type' => $meta->_education_type,
                        '_education_form' => $meta->_education_form,
                        '_level' => $meta->_level,
                        '_specialty' => $meta->_specialty_id,
                        '_department' => $meta->_department,
                        '_group' => $meta->_group,
                        '_education_year' => $meta->_education_year,
                        'discount' => 0,
                        'month_count' => 12,
                        'accepted' => EStudentContract::STATUS_ENABLE,
                    ]);
                }
            }
            if ($model->isNewRecord) {
                $model->month_count = 12;
                $model->_contract_type = ContractType::CONTRACT_TYPE_BASE;
                $model->_contract_summa_type = ContractSummaType::CONTRACT_SUMMA_TYPE_OFF;
                $model->contract_form_type = EStudentContractType::CONTRACT_FORM_TWO;
            }

            if ($model->load($this->post())) {
                /* if ($model->isNewRecord)
                     $model->_semester = $model->semester_id;*/

                try {

                    if ($model->save()) {
                        $this->addSuccess(__(
                            $contract ?
                                'The contract for student {name} has been updated' :
                                'The contract for student {name} has been created',
                            [
                                'name' => $model->student->getFullName()
                            ]));
                        return $this->redirect(['student-contract-manual-edit', 'contract' => $model->id]);
                    } else {
                        $this->addError($model->getOneError());
                    }
                } catch (\Exception $exception) {
                    if ($exception->getCode() == 23505) {
                        $this->addError(__('You cannot order a contract for the selected academic year'));
                    } else {
                        $this->addError($exception->getMessage());
                    }
                    //return $this->refresh();
                }

            }

            return $this->render('student-contract-manual-edit-student', [
                'selected' => $model,
                'meta' => $meta,
                'contractFee' => @$contractFee,
                'contractFeeDataProvider' => @$contractFeeDataProvider,

            ]);
        }

        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->search_for_contract($this->getFilterParams());

        $list = array();
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

        if (!empty($searchModel->search)) {
            $dataProvider->query->andWhere('1 = 1');
        } else if (empty($searchModel->_department) || empty($searchModel->_specialty_id) || empty($searchModel->_education_form)) {
            $dataProvider->query->andWhere('1 <> 1');
        }

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }

    public function actionPaymentMonitoringDepartment()
    {
        if (!$this->_user()->role->isDeanOrTutorRole()) {
            $this->addInfo(
                __('This page is for the dean profile only.')
            );
            return $this->goHome();
        }
        $searchModel = new EStudentContract();
        $dataProvider = $searchModel->search_payment($this->getFilterParams(), true, $this->_user());
        $faculty = "";
        if ($this->_user()->role->isDeanOrTutorRole()) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
                $searchModel->_department = $faculty;
                $dataProvider->query->andFilterWhere(['e_student_contract._department' => $faculty]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        if ($searchModel->_education_year == null) {
            $searchModel->_education_year = EducationYear::getCurrentYear()->code;
            $dataProvider->query->andFilterWhere(['e_student_contract._education_year' => EducationYear::getCurrentYear()->code]);
        }

        if (empty($searchModel->_department) || empty($searchModel->_specialty) || empty($searchModel->_education_form)) {
            $dataProvider->query->andWhere('1 <> 1');
        }

        if ($fileName = $this->get('file')) {
            $dir = Yii::getAlias('@backend/runtime/export/');
            $baseName = basename($dir . $fileName);

            if (file_exists($dir . $baseName)) {
                return Yii::$app->response->sendFile($dir . $baseName, $baseName);
            } else {
                $this->addError(__('File {name} not found', ['name' => $fileName]));
            }
            return $this->goBack();
        }

        if ($this->get('download')) {
            $education_year = $this->get('education_year');
            $query = $searchModel->search_payment($this->getFilterParams(), false, $this->_user());
            $query->andFilterWhere(['e_student_contract._education_year' => $education_year]);
            $countQuery = clone $query;
            $limit = 2000;
            if ($countQuery->count() <= $limit) {
                $fileName = EStudentContract::generateDownloadFile($query);

                return Yii::$app->response->sendFile($fileName, basename($fileName));
            } else {
                /**
                 * @var $queue Queue
                 * @var $queue1 Queue
                 */
                $queue1 = Yii::$app->queue;
                $queue = Yii::$app->queueFile;
                $prefix = $queue1->channel;
                if ($queue1->redis->llen("$prefix.waiting") == 0) {
                    $queue = $queue1;
                }

                if ($queue
                    ->ttr(900)
                    ->push(
                        new ContractListFileGenerateJob(
                            [
                                'filterParams' => $this->getFilterParams(),
                                'education_year' => $education_year,
                                'language' => Yii::$app->language,
                                'downloadUrl' => linkTo(['finance/payment-monitoring', 'file' => '']),
                                'recipients' => [$this->_user()->contact->id],
                            ]
                        )
                    )) {
                    $this->addSuccess(
                        __(
                            'Shartnoma to`lovlari soni {limit} tadan oshganligi sababli, ro\'yxat generatsiya qilish navbatga qo\'yildi va tez orada Xabarlar sahifasiga jo\'natiladi.',
                            ['limit' => $limit]
                        )
                    );
                }

                return $this->redirect(['finance/payment-monitoring']);
            }
        }

        return $this->renderView(
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'faculty' => $faculty,
            ]
        );
    }

    public function actionImportUzasbo()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }

        if ($this->get('download')) {
            $file = Yii::getAlias('@root/templates/template.xlsx');
            if (file_exists($file)) {
                return Yii::$app->response->sendFile($file);
            }
            return $this->goHome();
        }

        $model = new FormUploadUzasbo();

        if ($model->load(Yii::$app->request->post())) {
            if ($count = $model->uploadData()) {
                $this->addSuccess(__('{count} students UzASBO IDs imported', ['count' => $count]));
            } else {
                if ($model->hasErrors()) {
                    $errors = $model->getFirstErrors();
                    $this->addError(array_pop($errors));
                } else {
                    $this->addInfo(__('No students found'));
                }
            }

            return $this->redirect(['finance/uzasbo-data']);
        }
    }

    public function actionContractInvoice($contracts = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }

        $searchModel = new EStudentContractInvoice();
        $dataProvider = $searchModel->searchContingent($this->getFilterParams());

        $department = false;


        if ($this->_user()->role->code == AdminRole::CODE_DEAN) {
            if (Yii::$app->user->identity->employee->deanFaculties) {
                $department = Yii::$app->user->identity->employee->deanFaculties->id;
                $dataProvider->query->andFilterWhere(['e_student_contract_invoice._department' => $department]);
            } else {
                $this->addInfo(
                    __('The institution department is not attached to your account. ')
                );
                return $this->goHome();
            }
        }
        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionContractInvoiceEdit($contract = false, $invoice = false, $download = false, $delete = false)
    {
        if ($this->_user()->role->code !== AdminRole::CODE_ACCOUNTING) {
            $this->addInfo(
                __('This page is for the marketing only.')
            );
            return $this->goHome();
        }
        /**
         * @var $meta EStudentContract
         */
        $model = null;
        $meta = null;

        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }
        if ($invoice) {
            $model = EStudentContractInvoice::findOne(['id' => $invoice]);
            $meta = EStudentContractInvoiceMeta::findOne([
                'id' => $model->_student_contract,
                'active' => EStudentContract::STATUS_ENABLE,
                'contract_status' => EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED
            ]);
            if ($delete) {
                try {
                    if ($model->delete()) {
                        $this->addSuccess(__('{number} invoice has been deleted ', ['number' => $model->invoice_number]));
                        return $this->redirect(['contract-invoice-edit', 'contract' => $model->_student_contract]);
                    }
                } catch (\Exception $exception) {
                    $this->addError($exception->getMessage());
                    return $this->redirect(['contract-invoice-edit', 'invoice' => $model->id]);
                }
                return $this->redirect(['contract-invoice-edit', 'contract' => $model->_student_contract]);
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
                $univer = EUniversity::findCurrentUniversity();
                $mpdf->SetDisplayMode('fullwidth');
                $mpdf->WriteHTML($this->renderPartial('contract-invoice-pdf', ['model' => $model, 'univer' => $univer]));

                return $mpdf->Output('Invoice-' . $model->student->student_id_number . '.pdf', Destination::DOWNLOAD);
            }
        }

        if ($contract) {
            if ($meta = EStudentContractInvoiceMeta::findOne([
                'id' => $contract,
                'active' => EStudentContract::STATUS_ENABLE,
                'contract_status' => EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED
            ])) {

                $model = new EStudentContractInvoice([
                    '_student' => $meta->_student,
                    '_student_contract' => $meta->id,
                    '_education_year' => $meta->_education_year,
                    '_department' => $meta->_department,
                    '_specialty' => $meta->_specialty,
                    '_education_type' => $meta->_education_type,
                    '_education_form' => $meta->_education_form,
                    '_level' => $meta->_level,
                    '_curriculum' => $meta->_curriculum,
                    '_group' => $meta->_group,
                ]);

                $model->scenario = EStudentContractInvoice::SCENARIO_CREATE;
            }
        }

        if ($model) {
            $model->scenario = EStudentContractInvoice::SCENARIO_CREATE;
            $searchModel = new EStudentContractInvoice();
            $dataProvider = $searchModel->search($this->getFilterParams());
            $dataProvider->query->andFilterWhere(['_student_contract' => $model->_student_contract]);

            if ($model->isNewRecord)
                $model->invoice_summa = $meta->summa;
            else
                $old_summa = $model->invoice_summa;
            if ($model->load($this->post())) {
                $all_summa = EStudentContractInvoice::getAllSummaByContract($model->_student_contract)->invoice_summa;
                if ($model->isNewRecord) {
                    if ($meta->summa < ($model->invoice_summa + $all_summa)) {
                        $this->addError(__('The limit summa for the contract has been exceeded'));
                        return $this->redirect(['finance/contract-invoice-edit', 'contract' => $meta->id]);
                    }
                } else {

                    if ($meta->summa < ($model->invoice_summa + $all_summa - $old_summa)) {
                        $this->addError(__('The limit summa for the contract has been exceeded'));
                        return $this->redirect(['finance/contract-invoice-edit', 'contract' => $meta->id]);
                    }
                }


                try {
                    if ($model->save()) {
                        $this->addSuccess(__(
                            $invoice ?
                                'The invoice for student {name} has been updated' :
                                'The invoice for student {name} has been created',
                            [
                                'name' => $model->student->getFullName()
                            ]));
                        return $this->redirect(['contract-invoice-edit', 'contract' => $model->_student_contract]);
                    } else {
                        $this->addError($model->getOneError());
                    }
                } catch (\Exception $exception) {
                    $this->addError($exception->getMessage());
                    return $this->refresh();
                }
            }
            return $this->render('contract-invoice-edit-student', [
                'model' => $model,
                'meta' => $meta,
                'department' => $department,
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
        }

        $searchModel = new EStudentContractInvoiceMeta();
        $user = $this->_user();
        $department = false;

        if ($user->role->isDeanRole()) {
            $department = $user->employee->deanFaculties->id;
        }

        return $this->renderView([
            'department' => $department,
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->searchForInvoice($this->getFilterParams(), $department),
        ]);
    }

    /**
     * @param $id
     * @return EStudentMeta|array|\yii\db\ActiveRecord|null
     * @throws \yii\web\NotFoundHttpException
     */
    protected function findStudentMetaModel($id)
    {
        if (($model = EStudentMeta::find()->where(
                ['_student' => $id, '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]
            )->one()) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }


}
