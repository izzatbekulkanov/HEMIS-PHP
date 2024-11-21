<?php

namespace backend\controllers;

use backend\components\View;
use backend\models\FilterForm;
use backend\models\FormUploadTrans;
use common\components\AccessResources;
use common\components\Config;
use common\models\academic\EDecree;
use common\models\academic\EDecreeStudent;
use common\models\curriculum\EducationYear;
use common\models\employee\EEmployeeMeta;
use common\models\performance\EStudentGpa;
use common\models\student\ESpecialty;
use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\student\EStudentExpelMeta;
use common\models\student\EStudentMeta;
use common\models\curriculum\ECurriculum;
use common\models\student\EStudentRestoreMeta;
use common\models\student\EStudentTransferGroupMeta;
use common\models\student\EStudentTransferMeta;
use common\models\system\AdminRole;
use common\models\system\classifier\BachelorSpeciality;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\Gender;
use common\models\system\classifier\MasterSpeciality;
use common\models\system\classifier\StudentStatus;
use common\models\system\classifier\TeacherPositionType;
use Yii;
use yii\db\JsonExpression;
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

class TransferController extends BackendController
{
    public $activeMenu = 'transfer';

    public function actionStudentGroup()
    {
        $faculty = $this->_user()->role->isDeanRole() ? @$this->_user()->employee->deanFaculties->id : null;
        $searchModel = new EStudentTransferGroupMeta();

        if ($searchModel->load($this->post())) {
            if ($faculty) {
                $searchModel->_department = $searchModel->nextDepartment = $faculty;
            }

            if ($searchModel->validate()) {
                if ($items = $this->post('selection')) {
                    try {
                        if ($count = $searchModel->transferItems($this->_user(), $items)) {
                            $this->addSuccess(__('{count} students transferred to {group} group', ['count' => $count, 'group' => $searchModel->nextGroupItem->name]));
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage(), true);
                    }
                }
                return $this->redirect(['student-group']);
            }
        }

        $searchModel->selectedStudents = 0;

        return $this->renderView([
            'dataProvider' => $searchModel->searchForGroupTransfer($this->getFilterParams(), $faculty),
            'searchModel' => $searchModel,
            'faculty' => $faculty,
        ]);
    }

    public function actionStudentCourseTransfer()
    {
        $faculty = $this->_user()->role->isDeanRole() ? @$this->_user()->employee->deanFaculties->id : null;
        $searchModel = new EStudentTransferMeta(['scenario' => EStudentTransferMeta::SCENARIO_TRANSFER]);

        if ($searchModel->load($this->post())) {
            if ($searchModel->validate()) {
                if ($items = $this->post('selection')) {
                    try {
                        if ($count = $searchModel->transferItems($this->_user(), $items)) {
                            $level = $searchModel->nextLevel;
                            $this->addSuccess(__('{count} students transferred to {level} level', ['count' => $count, 'level' => $level->level->name . ' / ' . $level->name]));
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage(), true);
                    }
                }
                return $this->redirect(['student-course-transfer']);
            }
        }

        $searchModel->selectedStudents = 0;

        return $this->renderView([
            'dataProvider' => $searchModel->searchForCourseTransfer($this->getFilterParams(), $faculty),
            'searchModel' => $searchModel,
            'faculty' => $faculty,
        ]);
    }


    public function actionRestore($id = false)
    {
        /**
         * @var $restoreModel EStudentRestoreMeta
         */
        $faculty = @$this->_user()->employee->deanFaculties->id;
        $searchModel = new EStudentRestoreMeta();
        $restoreModel = null;
        $restoreMetaModel = new EStudentRestoreMeta();
        $restoreMetaModel->_department = $faculty;

        if ($id) {
            if ($restoreModel = EStudentRestoreMeta::findOne($id)) {
                if ($restoreMetaModel->load($this->post())) {
                    try {
                        if ($newMeta = $restoreMetaModel->restoreStudent($this->_user(), $restoreModel)) {
                            $this->addSuccess(__('Student {name} restored to {meta} successfully', [
                                'name' => $restoreModel->student->getFullName(),
                                'meta' => sprintf("%s / %s / %s", $restoreMetaModel->educationType->name, $restoreMetaModel->semester->name, $restoreMetaModel->group->name)
                            ]));

                            $path = $this->getRoute() . '_search';
                            Yii::$app->session->offsetUnset($path);

                            return $this->redirect(['restore']);
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['restore', 'id' => $id]);
                }

                $restoreMetaModel->loadRestoreParams($this->getFilterParams(), $restoreModel);
            }
        }


        return $this->renderView([
            'dataProvider' => $searchModel->searchForRestore($this->get(), $faculty),
            'searchModel' => $searchModel,
            'restoreModel' => $restoreModel,
            'faculty' => $faculty,
            'restoreMetaModel' => $restoreMetaModel,
        ]);
    }

    public function actionStudentCourseExpel()
    {
        $faculty = $this->_user()->role->isDeanRole() ? @$this->_user()->employee->deanFaculties->id : null;
        $searchModel = new EStudentTransferMeta(['scenario' => EStudentTransferMeta::SCENARIO_EXPEL]);

        if ($searchModel->load($this->post())) {
            if ($searchModel->validate()) {
                if ($items = $this->post('selection')) {
                    try {
                        if ($count = $searchModel->expelItems($this->_user(), $items)) {
                            Yii::$app->session->set('last_applied_decree_ex', $searchModel->_decree);
                            $this->addSuccess(__('{count} students expelled from course', ['count' => $count]));
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage(), true);
                    }
                }
                return $this->redirect(['student-course-expel']);
            }
        }

        $searchModel->selectedStudents = 0;

        if ($d = Yii::$app->session->get('last_applied_decree_ex')) {
            // $searchModel->_decree = $d;
        }

        return $this->renderView([
            'dataProvider' => $searchModel->searchForCourseTransfer($this->getFilterParams(), $faculty),
            'searchModel' => $searchModel,
            'faculty' => $faculty,
        ]);
    }

    public function actionStudentTransfer()
    {
        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $faculty = "";
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
        $dataProvider->query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
        $dataProvider->query->andFilterWhere(['e_student_meta.active' => EStudentMeta::STATUS_ENABLE]);

        $searchModelFix = new EStudentMeta();
        $searchModelFix->scenario = EStudentMeta::SCENARIO_ORDER;
        $studentRegisterProvider = "";
        if (!empty($searchModel->_group)) {
            $studentRegister = new EStudentMeta();
            $studentRegisterProvider = $studentRegister->search(false);
            $studentRegisterProvider->query->andFilterWhere(['_curriculum' => $searchModel->_curriculum, '_group' => $searchModel->_group]);
            $studentRegisterProvider->query->andFilterWhere(['_student_status' => StudentStatus::STUDENT_TYPE_STUDIED]);
            $studentRegisterProvider->query->andFilterWhere(['<>', '_semestr', $searchModel->_semestr]);
            if (empty($searchModel->_group)) {
                // $dataProvider->query->andWhere('1 <> 1');
                $studentRegisterProvider->query->andWhere('1 <> 1');
            }
            $studentRegisterProvider->query->andFilterWhere(['e_student_meta.active' => EStudentMeta::STATUS_ENABLE]);
        }
        /*        $subject = new ECurriculumSubject();
                $subjectProvider = $subject->search($this->getFilterParams());
                $subjectProvider->query->andFilterWhere(['_curriculum' => $searchModel->_curriculum, '_semester' => $searchModel->_semestr]);
                $subjectProvider->query->orderBy(['in_group' => SORT_DESC]);

                $studentSubject = new EStudentSubject();
                $studentSubjectProvider = $studentSubject->search($this->getFilterParams());
                $studentSubjectProvider->query->andFilterWhere(['_curriculum' => $searchModel->_curriculum, '_semester' => $searchModel->_semestr, '_group' => $searchModel->_group]);

        */


        /*if ($this->get('delete')) {
            if ($id = $this->get('id')) {
                if ($model = EStudentMeta::findOne($id)) {
                    if ($issue = $model->anyIssueWithDelete()) {
                        $this->addError($issue);
                    } else {
                        $this->addSuccess(__('Student [{code}] deleted successfully', ['code' => $model->id]));
                    }
                }
            }
            return $this->redirect(Yii::$app->request->referrer);
        }*/

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'searchModelFix' => $searchModelFix,
            'studentRegister' => @$studentRegister,
            'studentRegisterProvider' => @$studentRegisterProvider,
            'faculty' => $faculty,
        ]);

    }

    public function actionStatus()
    {
        $searchModel = new EStudentMeta();
        $faculty = null;
        $user = $this->_user();

        if ($user->role->isDeanOrTutorRole()) {
            $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
        }

        return $this->renderView([
            'dataProvider' => $searchModel->searchForStudentStatus($this->getFilterParams(), $user, $faculty),
            'searchModel' => $searchModel,
            'faculty' => $faculty,
            'user' => $user,
        ]);
    }

    public function actionStudentExpel()
    {
        $faculty = $this->_user()->role->isDeanRole() ? @$this->_user()->employee->deanFaculties->id : null;
        $searchModel = new EStudentExpelMeta();

        if ($searchModel->load($this->post())) {
            if ($searchModel->validate()) {
                if ($items = $this->post('selection')) {
                    try {
                        if ($count = $searchModel->expelItems($this->_user(), $items)) {
                            $this->addSuccess(__('{count} students transferred to expel', ['count' => $count]));
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage(), true);
                    }
                }
                return $this->redirect(['student-expel']);
            }
        }

        $searchModel->selectedStudents = 0;

        return $this->renderView([
            'dataProvider' => $searchModel->searchForExpel($this->getFilterParams(), $faculty),
            'searchModel' => $searchModel,
            'faculty' => $faculty,
        ]);
    }

    public function actionAcademicLeave()
    {
        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->searchForTransfer($this->getFilterParams());
        $faculty = false;

        if ($this->_user()->role->isDeanRole()) {
            $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            $dataProvider->query->andFilterWhere(['_department' => $faculty]);
        }

        $searchModelFix = new EStudentMeta();
        $searchModelFix->scenario = EStudentMeta::SCENARIO_ORDER;

        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'searchModelFix' => $searchModelFix,
            'faculty' => $faculty,
        ]);
    }

    public function actionGraduate()
    {
        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->searchForTransfer($this->getFilterParams());
        $faculty = false;

        if ($this->_user()->role->isDeanRole()) {
            $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            $dataProvider->query->andFilterWhere(['_department' => $faculty]);
        }

        $searchModelFix = new EStudentMeta();
        $searchModelFix->scenario = EStudentMeta::SCENARIO_ORDER;
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'searchModelFix' => $searchModelFix,
            'faculty' => $faculty,
        ]);
    }

    public function actionToOperation()
    {
        /**
         * @var $model EStudentMeta
         * @var $decree EDecree
         */
        if (Yii::$app->request->post('selection') && Yii::$app->request->post('decree')) {
            $selection = (array)Yii::$app->request->post('selection');
            if ($order_number = Yii::$app->request->post('decree')) {
                $success = 0;
                if ($decree = EDecree::findOne(['id' => $order_number, 'status' => EDecree::STATUS_ENABLE])) {

                    $operand = Yii::$app->request->post('operand');
                    $grad_simple = Yii::$app->request->post('grad_simple');

                    $transaction = Yii::$app->db->beginTransaction();
                    $order_reason = Yii::$app->request->post('order_reason');
                    $data = [];
                    $user = $this->_user();
                    try {
                        foreach ($selection as $id) {
                            if ($model = EStudentMeta::findOne((int)$id)) {
                                $canProcess = false;
                                switch ($operand) {
                                    case StudentStatus::STUDENT_TYPE_GRADUATED:
                                        $canProcess = $model->canOperateGraduation();
                                        break;
                                    case StudentStatus::STUDENT_TYPE_ACADEMIC:
                                        $canProcess = $model->canOperateAcademicLeave();
                                        break;
                                    case StudentStatus::STUDENT_TYPE_EXPEL:
                                        $canProcess = $model->canOperateExpel();
                                        break;
                                    /*case StudentStatus::STUDENT_TYPE_GRADUATED_SIMPLE:
                                        $canProcess = true;
                                        //$canProcess = $model->canOperateGraduationSimple();
                                        break;*/
                                }
                                if($grad_simple){
                                    $canProcess = $model->canOperateGraduationSimple();
                                    $operand = StudentStatus::STUDENT_TYPE_GRADUATED;
                                }

                                if ($canProcess) {
                                    $model->order_number = $decree->number;
                                    $model->order_date = $decree->date;
                                    $model->_status_change_reason = $order_reason;

                                    $model->_student_status = $operand;

                                    if ($model->save(false)) {
                                        if (EDecreeStudent::findOne([
                                                '_decree' => $decree->id,
                                                '_student' => $model->_student,
                                            ]) == null)
                                            $data[] = [
                                                '_decree' => $decree->id,
                                                '_student' => $model->_student,
                                                '_admin' => $user->id,
                                                '_student_meta' => $model->id,
                                                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                                            ];

                                        $model->student->setAsShouldBeSynced();
                                        $success++;
                                    }
                                }
                            }
                        }
                        if ($success) {
                            Yii::$app->db
                                ->createCommand()
                                ->batchInsert('e_decree_student', array_keys($data[0]), $data)
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
            return $this->redirect(Yii::$app->request->referrer);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionToTransfer()
    {
        if (Yii::$app->request->post('selection') && Yii::$app->request->post('_curriculum') && Yii::$app->request->post('_education_year') && Yii::$app->request->post('_semester')) {
            $selection = (array)Yii::$app->request->post('selection');
            $_curriculum = ECurriculum::findOne((int)Yii::$app->request->post('_curriculum'));
            $_education_year = EducationYear::findOne((int)Yii::$app->request->post('_education_year'));
            $_semester = Yii::$app->request->post('_semester');
            if (is_array($selection) && $_curriculum && $_education_year && $_semester) {
                $success = 0;
                foreach ($selection as $id) {
                    try {
                        $model = EStudentMeta::findOne((int)$id);
                        if ($new_model = EStudentMeta::getStudentByCurriculumYearSemesterActive($_curriculum->id, $_education_year->code, $_semester, $model->_student, EStudentMeta::STATUS_DISABLE)) {
                            $new_model->active = EStudentMeta::STATUS_ENABLE;
                        } else {
                            $new_model = new EStudentMeta();
                            $new_model->student_id_number = $model->student_id_number;
                            $new_model->_student = $model->_student;
                            $new_model->_department = $_curriculum->_department;
                            $new_model->_education_type = $_curriculum->_education_type;
                            $new_model->_education_form = $_curriculum->_education_form;
                            $new_model->_curriculum = $_curriculum->id;
                            $new_model->_semestr = $_semester;
                            $new_model->_level = $model->_level;
                            $new_model->_group = $model->_group;
                            $new_model->_education_year = $_education_year->code;
                            $new_model->_payment_form = $model->_payment_form;
                            $new_model->_student_status = StudentStatus::STUDENT_TYPE_STUDIED;
                            $new_model->_specialty_id = $_curriculum->_specialty_id;
                        }


                        if ($new_model->save()) {
                            $success++;
                            $model->active = EStudentMeta::STATUS_DISABLE;
                            $model->save(false);
                        } /*else {
                            $e2 = new Exception();
                            if ($e2->getCode() == 0 || $e2->getCode() == 23505) {
                                $this->addError(__('Student have been already registered for this semester'));
                            } else {
                                $this->addError($e2->getMessage());
                            }
                        }*/
                    } catch (Exception $e) {
                        if ($e->getCode() == 23505) {
                            $this->addError(__('Student have been already registered for this semester'));
                        } else {
                            $this->addError($e->getMessage());
                        }
                    }
                }
                if ($success)
                    $this->addSuccess(__('{count} student assigned to semester', ['count' => $success]));
            }
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [];
    }

    protected function findSpecialModel($id)
    {
        if (($model = ESpecialty::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findGroupModel($id)
    {
        if (($model = EGroup::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findStudentModel($id)
    {
        if (($model = EStudent::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findStudentMetaModel($id)
    {
        if (($model = EStudentMeta::find()->where(['_student' => $id])->one()) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    protected function findStudentMetaModel2($id)
    {
        if (($model = EStudentMeta::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }

    public function actionReturn()
    {
        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->searchForReturn($this->getFilterParams());
        $faculty = false;

        if ($this->_user()->role->isDeanRole()) {
            $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            $dataProvider->query->andFilterWhere(['_department' => $faculty]);
        }

        $searchModelFix = new EStudentMeta();
        $searchModelFix->scenario = EStudentMeta::SCENARIO_ORDER;
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'searchModelFix' => $searchModelFix,
            'faculty' => $faculty,
        ]);
    }

    public function actionToReturn()
    {
        /**
         * @var $model EStudentMeta
         * @var $decree EDecree
         */
        if (Yii::$app->request->post('selection')) {
            $selection = (array)Yii::$app->request->post('selection');
            $success = 0;
            $operand = Yii::$app->request->post('operand');
            $transaction = Yii::$app->db->beginTransaction();
            $data = [];
            $user = $this->_user();
            try {
                foreach ($selection as $id) {
                    if ($model = EStudentMeta::findOne((int)$id)) {
                        $canProcess = false;
                        switch ($operand) {
                            case StudentStatus::STUDENT_TYPE_STUDIED:
                                $canProcess = $model->canOperateReturn();
                                break;
                        }

                        if ($canProcess) {
                            $model->order_number = null;
                            $model->order_date = null;
                            $model->_status_change_reason = null;
                            $model->_student_status = $operand;

                            if ($model->save(false)) {
                                $data[] = [
                                    '_student_meta' => $model->id,
                                ];

                                $model->student->setAsShouldBeSynced();
                                $success++;
                            }


                        }
                    }
                }
                if ($success) {
                    Yii::$app->db
                        ->createCommand()
                        //->delete('e_decree_student', '_student_meta' => $data)
                        //->delete('e_decree_student', '_student_meta = :_student_meta', [':_student_meta' => $data])
                        ->delete('e_decree_student', ['_student_meta' => $data])
                        ->execute();
                    $transaction->commit();
                    $this->addSuccess(__('Decree deleted to {count} students',
                        [
                            'count' => $success,
                        ]
                    ));
                }

            } catch (\Exception $e) {
                $transaction->rollBack();
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionGraduateSimple()
    {
        if ($this->_user()->role->code !== AdminRole::CODE_MIN_ADMIN) {
            $this->addInfo(
                __('This page is for the miniadmin only.')
            );
            return $this->goHome();
        }

        $searchModel = new EStudentMeta();
        $dataProvider = $searchModel->searchForTransfer($this->getFilterParams());
        $faculty = false;

        if ($this->_user()->role->isDeanRole()) {
            $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
            $dataProvider->query->andFilterWhere(['_department' => $faculty]);
        }

        $searchModelFix = new EStudentMeta();
        $searchModelFix->scenario = EStudentMeta::SCENARIO_ORDER;
        return $this->renderView([
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'searchModelFix' => $searchModelFix,
            'faculty' => $faculty,
        ]);
    }

}
