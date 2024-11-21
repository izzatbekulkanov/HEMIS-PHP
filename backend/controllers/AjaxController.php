<?php

namespace backend\controllers;

use backend\models\HPosition;
use common\models\archive\ECertificateCommittee;
use common\models\archive\EDiplomaBlank;
use common\models\archive\EGraduateQualifyingWork;
use common\models\archive\EStudentEmployment;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\curriculum\ECurriculumSubjectExamType;
use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\RatingGrade;
use common\models\curriculum\Semester;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\science\EDoctorateStudent;
use common\models\science\EPublicationAuthorMeta;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\EQualification;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\AdminRole;
use common\models\system\classifier\BachelorSpeciality;
use common\models\system\classifier\Course;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\ExamType;
use common\models\system\classifier\FinalExamType;
use common\models\system\classifier\Gender;
use common\models\system\classifier\GraduateFieldsType;
use common\models\system\classifier\MasterSpeciality;
use common\models\system\classifier\MethodicalPublicationType;
use common\models\system\classifier\PatientType;
use common\models\system\classifier\ProjectExecutorType;
use common\models\system\classifier\ScientificPublicationType;
use common\models\system\classifier\Soato;
use common\models\system\classifier\StudentSuccess;
use common\models\system\classifier\SubjectBlock;
use common\models\User;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Ajax controller
 */
class AjaxController extends BackendController
{

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'maxLength' => 5,
                'offset' => 3,
//                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * @skipAccess
     */
    public function actionGetRegion()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $catList = Soato::getChildrenOption($cat_id);
                $out = ArrayHelper::map($catList, 'code', 'name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                usort($result, function ($a, $b) {
                    return $a['name'] > $b['name'];
                });
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetAward()
    {
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $catList = StudentSuccess::getChildrenOption($cat_id);
                $out = ArrayHelper::map($catList, 'code', 'name');
                $result = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                usort($result, function ($a, $b) {
                    return $a['name'] > $b['name'];
                });
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetGroupStudents()
    {
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $groupId = $parents[0];
                if (!empty($groupId)) {
                    $out = EGroup::getGroupStudentsOptions($groupId);
                    $result = [];
                    foreach ($out as $key => $value) {
                        $tmp_arr = ['id' => $key, 'name' => $value];
                        $result[] = $tmp_arr;
                    }
                    usort(
                        $result,
                        function ($a, $b) {
                            return $a['name'] > $b['name'];
                        }
                    );
                    return Json::encode(['output' => $result, 'selected' => '']);
                }
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGet_specialty()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $catList = ESpecialty::find()
                    ->where('_department = :id AND active=:status', [':id' => $cat_id, ':status' => ESpecialty::STATUS_ENABLE])
                    ->andWhere(['in', '_education_type', [EducationType::EDUCATION_TYPE_BACHELOR, EducationType::EDUCATION_TYPE_MASTER]])
                    ->orderByTranslationField('name')
                    ->all();
                $out = ArrayHelper::map($catList, 'id', 'fullName');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGet_curriculum()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $catList = ECurriculum::find()->where('_education_year = :id AND active=:status', [':id' => $cat_id, ':status' => true])->all();
                $out = ArrayHelper::map($catList, 'id', 'name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetCurriculums()
    {
        if ($this->_user()->role->isDeanRole() && Yii::$app->user->identity->employee->deanFaculties) {
            $faculty = Yii::$app->user->identity->employee->deanFaculties->id;
        } else {
            $faculty = "";
        }
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $eduType = $parents[0];
                $eduForm = $parents[1];
                $catList = ECurriculum::find()
                    ->where('_education_type = :type AND _education_form=:form', [':type' => $eduType, ':form' => $eduForm])
                    ->andFilterWhere(['_department' => $faculty])
                    ->all();
                $out = ArrayHelper::map($catList, 'id', 'name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetExamCurriculums()
    {
        if (isset($_POST['depdrop_all_params'])) {
            $parents = $_POST['depdrop_all_params'];
            if ($parents != null) {
                $educationYear = $parents['_education_year'];

                $ids = ESubjectExamSchedule::find()
                    ->with(['curriculum'])
                    ->select(['_curriculum'])
                    ->where([
                        '_education_year' => $educationYear
                    ]);

                if (Yii::$app->user->identity->role->isTeacherRole()) {
                    $ids->andFilterWhere(['_employee' => Yii::$app->user->identity->_employee]);
                }

                $ids->groupBy(['_curriculum']);
                $items = $ids->all();
                if (count($items)) {
                    $result = [];
                    foreach ($items as $value) {
                        $result[] = ['id' => $value->curriculum->id, 'name' => $value->curriculum->name];
                    }
                    return Json::encode(['output' => $result, 'selected' => $result[0]['id']]);
                }
            }
        }

        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGet_position()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $model = EDepartment::findOne($cat_id);
                if ($model === null) {
                    throw new NotFoundHttpException('The requested page does not exist.');
                }
                $catList = HPosition::find()->where('_structure_type=:_structure_type AND status=:status', [':_structure_type' => $model->_structure_type, ':status' => true])->all();
                $out = ArrayHelper::map($catList, 'code', 'name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionEduStudentInfo($passport = null, $pin = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->post('passport') && Yii::$app->request->post('pin')) {
            $passport = Yii::$app->request->post('passport');
            $pin = Yii::$app->request->post('pin');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://talaba.edu.uz/api/my_edu_uz/student_mvd_hemis.php?TOKEN=12345&pinfl=" . $pin . "&p_seriya=" . $passport);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        $citizen = \yii\helpers\Json::decode($output);
        $result = [
            'error' => 0,
        ];
        if (is_array($citizen)) {
            if ($citizen['document'] != "") {
                $result['first_name'] = isset($citizen['name_latin']) ? $citizen['name_latin'] : __('No data');
                $result['second_name'] = isset($citizen['surname_latin']) ? $citizen['surname_latin'] : __('No data');
                $result['third_name'] = isset($citizen['patronym_latin']) ? $citizen['patronym_latin'] : __('No data');
                $result['birth_date'] = isset($citizen['birth_date']) ? $citizen['birth_date'] : __('No data');
                $result['home_address'] = isset($citizen['birth_place']) ? $citizen['birth_place'] : __('No data');
                $_gender = isset($citizen['sex']) ? ($citizen['sex'] == 1 ? 11 : 10) : __('No data');
                $genders = "";
                $sgenders = Gender::find()->where(['active' => true])->all();
                foreach ($sgenders as $data) {
                    if ($data->code == $_gender) {
                        $genders .= "<option value='" . $data->code . "' selected>" . $data->name . "</option>";
                    } else {
                        $genders .= "<option value='" . $data->code . "'>" . $data->name . "</option>";
                    }
                }
                $result['gender'] = $genders;
                $result['hidden_gender'] = $_gender;
                $result['hidden_first_name'] = $result['first_name'];
                $result['hidden_second_name'] = $result['second_name'];
                $result['hidden_third_name'] = $result['third_name'];
                $result['hidden_birth_date'] = $result['birth_date'];
                $result['hidden_home_address'] = $result['home_address'];
            } else {
                $result['error'] = 1;
                $result['message'] = __('Information not found');
            }
        } else {
            $result['error'] = 1;
            $result['message'] = __('Information not found');
        }

        // vd($result);
        return $result;
    }

    /**
     * @skipAccess
     */
    public function actionGet_specialty_from_classifier($q = null, $id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = new \yii\db\Query;
            $query->select(" `code`, CONCAT_WS(' ', `name`, `code`) as `info`")
                ->from('h_bachelor_speciality')
                ->where(['like', 'name', $q])
                ->orWhere(['like', 'code', $q])
                //->orWhere(['like', '_translations', $q])
                ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        } elseif ($id > 0) {
            //$out['results'] = ['id' => $id, 'text' => Students::find($id)->name];
        }
        return $out;
    }

    /**
     * @skipAccess
     */
    public function actionGetSubjectBlock()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $model = ECurriculum::findOne($cat_id);
                if ($model === null) {
                    throw new NotFoundHttpException('The requested page does not exist.');
                }
                $catList = SubjectBlock::find()->where('_parent=:code AND code!=:id AND active=:active', [':code' => $model->_education_type, ':id' => $model->_education_type, ':active' => true])->all();

                $out = ArrayHelper::map($catList, 'code', 'name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionSemesterSubjectEdit($curriculum = null, $semester = null, $id = null)
    {
        $result = [
            'error' => 0,
        ];
        if (Yii::$app->request->post('curriculum') && Yii::$app->request->post('semester') && Yii::$app->request->post('id')) {
            $curriculum = Yii::$app->request->post('curriculum');
            $semester = Yii::$app->request->post('semester');
            $id = Yii::$app->request->post('id');
            $curriculum_subjects = ECurriculumSubject::find()->where(['_curriculum' => $curriculum, '_semester' => $semester, 'active' => true])->all();
            $exist_subjects = array();
            foreach ($curriculum_subjects as $subject) {
                $exist_subjects [] = $subject->_subject;
            }
            $arr_items = array();
            $arr_items = explode(',', $id);

            /*$result = [
                'error' => 0,
            ];*/

            try {
                /* $x = array_values($exist_subjects);
                 $y = array_values($arr_items);
                 sort($x);
                 sort($y);*/
                //if($x == $y || count($exist_subjects) < count($arr_items)) {
                //$sql = 'INSERT into ' . ECurriculumSubject::tableName() . ' ("_curriculum", "_subject", "_semester", "position", "updated_at", "created_at") VALUES ';
                $sql = 'INSERT into ' . ECurriculumSubject::tableName() . ' ("_curriculum", "_subject", "_curriculum_subject_block", "_semester", "_subject_type", "_rating_grade", "_exam_finish", "total_acload", "credit", "position", "updated_at", "created_at") VALUES ';
                $time = date('Y-m-d H:i:s', time());
                foreach ($arr_items as $key => $item) {
                    $curriculum_subject_block = "null";
                    $subject_type = "null";
                    $rating_grade = "null";
                    $total_acload = "null";
                    $credit = "null";
                    $exam_finish = "null";
                    //                  $department = "null";
//                    $in_group = "null";
                    //$at_semester = "false";
                    //$reorder = "false";
                    $exist = ECurriculumSubject::find()
                        ->where(['_curriculum' => $curriculum, '_subject' => $item, 'active' => true])
                        // ->andWhere(['<>', '_semester', $semester])
                        ->andWhere(['>', 'total_acload', 0])
                        ->one();
                    if ($exist !== null) {
                        //$curriculum_subject_block = $exist->_curriculum_subject_block;
                        //$subject_type = $exist->_subject_type;
                        //$rating_grade = $exist->_rating_grade;
                        //$total_acload = $exist->total_acload;
                        //$credit = $exist->credit;
                        //$exam_finish = $exist->_exam_finish;
                        //                    $department = $exist->_department;
                        //                  $in_group = $exist->in_group;
                        //                $at_semester = $exist->at_semester;
                        //$reorder = $exist->reorder;
                        /*$curriculum_subjects_details = ECurriculumSubjectDetail::find()->where([
                            '_curriculum' => $curriculum,
                            '_subject' => $item,
                            'active' => true])
                            ->andWhere(['<>', '_semester', $semester])
                            ->all();
                        foreach ($curriculum_subjects_details as $detail) {
                            $model = new ECurriculumSubjectDetail();
                            $model->_curriculum = $curriculum;
                            $model->_subject = $item;
                            $model->_semester = $semester;
                            $model->_training_type = $detail->_training_type;
                            $model->academic_load = $detail->academic_load;
                            $model->save();
                        }*/
                    } else {
                        $curriculum_subject_block = "null";
                        $subject_type = "null";
                        $rating_grade = "null";
                        $total_acload = "null";
                        $credit = "null";
                        //          $department = "null";
                        //            $in_group = "null";
                        $exam_finish = "null";
                        //              $at_semester = "false";
                        ///$reorder = "false";
                    }

                    //$sql .= '(' . $curriculum . ',' . $item .',\'' . $curriculum_subject_block . '\',\'' . $semester .'\',\'' . $subject_type .'\',\'' . $rating_grade.'\',' . $total_acload. ',' . $credit.',' . $key . ',\'' . $time . '\',\'' . $time . '\'),';
                    $sql .= '(' . $curriculum . ',' . $item . ',' . $curriculum_subject_block . ',' . $semester . ',' . $subject_type . ',' . $rating_grade . ',' . $exam_finish . ',' . $total_acload . ',' . $credit . ',' . $key . ',\'' . $time . '\',\'' . $time . '\'),';
                }
                $connection = Yii::$app->db;
                $sql = substr($sql, 0, -1);
                $sql .= ' ON CONFLICT ("_curriculum", "_subject", "_semester")  DO UPDATE SET position=EXCLUDED.position, _curriculum=EXCLUDED._curriculum, _subject=EXCLUDED._subject, _semester=EXCLUDED._semester;';
                $command = $connection->createCommand($sql);
                $command->execute();

                // }
                /* else{
                     $diff = array_diff($exist_subjects, $arr_items);
                     print_r($diff);
                 }*/

                $result['error'] = 0;
                $result['message'] = "Ok";
                return $this->redirect(Yii::$app->request->referrer);
            } catch (Exception $e) {
                $result['error'] = 1;
                $result['message'] = __('Request failed');
                $this->addError($e->getMessage());
            }
        } else {
            $curriculum = null;
            $semester = null;
            $id = null;
            $result['error'] = 1;
            $result['message'] = __('Request failed');
        }
        return json_encode($result);
    }

    /**
     * @skipAccess
     */
    public function actionGetSemesterSubject()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $sub_cat_id = $parents[1];
                $catList = ECurriculumSubject::find()->where('_curriculum=:curriculum AND _semester=:semester AND active=:active', [':curriculum' => $cat_id, ':semester' => $sub_cat_id, ':active' => true])->all();
                $out = ArrayHelper::map($catList, '_subject', 'subject.name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetSubjectTraining()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $sub_cat_id = $parents[1];
                $sub_cat_id2 = $parents[2];
                $catList = ECurriculumSubjectDetail::find()->where('_curriculum=:curriculum AND _semester=:semester AND _subject=:subject AND active=:active', [':curriculum' => $cat_id, ':semester' => $sub_cat_id, ':subject' => $sub_cat_id2, ':active' => true])->all();
                $out = ArrayHelper::map($catList, '_training_type', 'fullName');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetSpecialClassifiers()
    {
        //  const EDUCATION_TYPE_BACHELOR = '11';
        // const EDUCATION_TYPE_MASTER = '12';

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                if ($cat_id == EducationType::EDUCATION_TYPE_BACHELOR) {
                    $out = BachelorSpeciality::getChildClassifierOptions();
                } elseif ($cat_id == EducationType::EDUCATION_TYPE_MASTER) {
                    $out = MasterSpeciality::getChildClassifierOptions();
                }

                // $out = ArrayHelper::map($catList, 'code', 'fullName');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetSpecialties()
    {
        //  const EDUCATION_TYPE_BACHELOR = '11';
        // const EDUCATION_TYPE_MASTER = '12';

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $faculty = "";
                if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
                    $faculty = $this->_user()->employee->deanFaculties->id;
                }
                if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
                    $faculty = $this->_user()->employee->headDepartments->parent;
                }
                $out = ESpecialty::getHigherSpecialtyByType($cat_id, $faculty);

                // $out = ArrayHelper::map($catList, 'code', 'fullName');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetSemesterYears()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $sub_cat_id = $parents[1];
                $catList = Semester::getByCurriculumYear($cat_id, $sub_cat_id);
                $out = ArrayHelper::map($catList, 'code', 'name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetGroupSemesters()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $sub_cat_id = $parents[1];
                $sub_cat_id2 = $parents[2];
                $catList = EStudentMeta::getRealContingentByCurriculumSemester($cat_id, $sub_cat_id, $sub_cat_id2);
                //$catList = EStudentMeta::find()->where('_curriculum=:curriculum AND _education_year=:_education_year AND _semestr=:_semester AND active=:active', [':curriculum' => $cat_id, ':_education_year' => $sub_cat_id, ':_semester' => $sub_cat_id2, ':active'=>EStudentMeta::STATUS_ENABLE])->all();
                $out = ArrayHelper::map($catList, '_group', 'group.name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetCurriculumSubjectTraining()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $sub_cat_id = $parents[1];
                $sub_cat_id2 = $parents[2];
                $catList = ECurriculumSubjectDetail::getTrainingByCurriculumSemesterSubject($cat_id, $sub_cat_id, $sub_cat_id2);
                $out = ArrayHelper::map($catList, '_training_type', 'trainingType.name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetCurriculumSubjectTopic()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $sub_cat_id = $parents[1];
                $sub_cat_id2 = $parents[2];
                $sub_cat_id3 = $parents[3];
                $catList = ECurriculumSubjectTopic::getTopicByCurriculumSemesterSubjectTraining($cat_id, $sub_cat_id, $sub_cat_id2, $sub_cat_id3);
                $out = ArrayHelper::map($catList, 'id', 'name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetSubjectTeachers()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0]; //curriculum
                $sub_cat_id = $parents[1]; //semester
                $sub_cat_id2 = $parents[2]; //subject
                $sub_cat_id3 = $parents[3];//exam_type
                // $sub_cat_id4 = $parents[4];//group
                if ($sub_cat_id3 != ExamType::EXAM_TYPE_FINAL && $sub_cat_id3 != ExamType::EXAM_TYPE_OVERALL) {
                    $catList = ESubjectSchedule::getTeacherByCurriculumSemesterSubject($cat_id, $sub_cat_id, $sub_cat_id2);
                    $out = ArrayHelper::map($catList, '_employee', 'employee.fullName');

                } else {
                    $out = EEmployeeMeta::getTeachers();
                }
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetCurriculumSubjectExamType()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $sub_cat_id = $parents[1];
                $sub_cat_id2 = $parents[2];

                $rating_grade = ECurriculumSubject::getByCurriculumSemesterSubject($cat_id, $sub_cat_id, $sub_cat_id2)->_rating_grade;
                if ($rating_grade == RatingGrade::RATING_GRADE_SUBJECT) {
                    $catList = ECurriculumSubjectExamType::getExamTypeByCurriculumSemesterSubject($cat_id, $sub_cat_id, $sub_cat_id2);
                } else {
                    $catList = ECurriculumSubjectExamType::getOtherExamTypeByCurriculumSemesterSubject($cat_id, $sub_cat_id, $sub_cat_id2);
                }
                $out = ArrayHelper::map($catList, '_exam_type', 'examType.name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => strtoupper($value)];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetCurriculumSubjectFinalExam()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $sub_cat_id = $parents[1];
                $curriculum = ECurriculum::findOne($cat_id);
                if ($curriculum === null) {
                    $this->notFoundException();
                }
                $final_exam_count = $curriculum->markingSystem->count_final_exams;
                $final = array();
                //if ($sub_cat_id == ExamType::EXAM_TYPE_FINAL || $sub_cat_id == ExamType::EXAM_TYPE_OVERALL) {
                $final = FinalExamType::getFinalExamTypeOptions($final_exam_count);
//                    if ($curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_RATING || $curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                $out = $final;
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => strtoupper($value)];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //}


                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetCurriculumSemesterSubjectGroups()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $sub_cat_id = $parents[1];
                $sub_cat_id2 = $parents[2];
                $sub_cat_id3 = $parents[3];
                $catList = EStudentSubject::getGroupsByCurriculumSemesterSubject($cat_id, $sub_cat_id, $sub_cat_id2);
                $out = ArrayHelper::map($catList, '_group', 'group.name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => $sub_cat_id3]);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetSemesterByGroup()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $group = EGroup::findOne($cat_id);
                $catList = Semester::getSemesterByCurriculum($group->_curriculum);
                $out = ArrayHelper::map($catList, 'code', 'name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetLevelByGroup()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $cat_id2 = $parents[1];
                $group = EGroup::findOne($cat_id);
                $curriculum = ECurriculum::findOne($group->_curriculum);
                $first_level = Course::COURSE_FIRST;
                $levels = array();

                $education_period = $curriculum->education_period;
                $i = 0;
                while ($i < (int)$education_period) {
                    $i++;
                    $levels [$first_level] = Course::findOne($first_level)->name;
                    $first_level++;
                }
                //$catList = ECurriculumWeek::getWeekByGroupCurriculum($group->_curriculum);
                //$out = ArrayHelper::map($catList, '_level', 'level.name');
                $out = $levels;
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return (['output' => $result]);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /*public function actionGetLevelByGroup()
    {
        //Yii::$app->response->format = Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $cat_id2 = $parents[1];

                $curriculum = ECurriculum::findOne($group->_curriculum);
                $first_level = Course::COURSE_FIRST;
                $levels = array();

                $education_period = $curriculum->education_period;
                $i = 0;
                while ($i < (int)$education_period) {
                    $i++;
                    $levels [$first_level] = Course::findOne($first_level)->name;
                    $first_level++;
                }
                //$catList = ECurriculumWeek::getWeekByGroupCurriculum($group->_curriculum);
                //$out = ArrayHelper::map($catList, '_level', 'level.name');
                $out = $levels;
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }

                return ['output' => $result, 'selected' => ''];

                //return;
            }
        }
        return ['output' => '', 'selected' => ''];
    }*/

    /**
     * @skipAccess
     */
    public function actionGetGroupBySpecialty()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $sub_cat_id = $parents[1];
                //$catList = EGroup::getOptionsByFaculty($cat_id, $sub_cat_id);
                $out = EGroup::getOptionsByFaculty($cat_id, $sub_cat_id);
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetGroupBySpecialtyEduForm()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $sub_cat_id = $parents[1];
                $sub_cat_id2 = $parents[2];
                //$catList = EGroup::getOptionsByFaculty($cat_id, $sub_cat_id);
                $out = EGroup::getOptionsByFacultyEduForm($cat_id, $sub_cat_id, $sub_cat_id2);
                $include = false;
                if ($this->_user()->role->isTutorRole()) {
                    $include = $this->_user()->tutorGroups;
                }

                $result = [];
                foreach ($out as $key => $value) {
                    if ($include === false || isset($include[$key])) {
                        $result[] = ['id' => $key, 'name' => $value];
                    }
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetEducationYearBySemestr()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $group = EGroup::findOne($cat_id);
                $catList = Semester::getSemesterByCurriculum($group->_curriculum);
                $out = ArrayHelper::map($catList, '_education_year', 'educationYear.name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetProjectMembers()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                if ($cat_id == ProjectExecutorType::PROJECT_EXECUTOR_TYPE_TEACHER) {
                    $out = EEmployee::getEmployees();
                } elseif ($cat_id == ProjectExecutorType::PROJECT_EXECUTOR_TYPE_STUDENT) {
                    $out = EStudent::getStudents();
                } elseif ($cat_id == ProjectExecutorType::PROJECT_EXECUTOR_TYPE_RESEARCHER) {
                    $out = EDoctorateStudent::getDoctorates();
                }


                // $out = ArrayHelper::map($catList, 'code', 'fullName');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetPublicationTypes()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                if ($cat_id == EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL) {
                    $out = MethodicalPublicationType::getClassifierOptions();
                } elseif ($cat_id == EPublicationAuthorMeta::PUBLICATION_TYPE_SCIENTIFIC) {
                    $out = ScientificPublicationType::getClassifierOptions();
                } elseif ($cat_id == EPublicationAuthorMeta::PUBLICATION_TYPE_PROPERTY) {
                    $out = PatientType::getClassifierOptions();
                }

                // $out = ArrayHelper::map($catList, 'code', 'fullName');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetDepartments()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                if ($cat_id > 0) {
                    $catList = EDepartment::getDepartmentList($cat_id);
                    $out = ArrayHelper::map($catList, 'id', 'name');
                }
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetGroupByCurruculum()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                //$catList = EGroup::getOptionsByFaculty($cat_id, $sub_cat_id);
                if (!empty($cat_id)) {
                    $out = EGroup::getOptions($cat_id);
                }
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetGroupsByCurriculum()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                //$catList = EGroup::getOptionsByFaculty($cat_id, $sub_cat_id);
                if (!empty($cat_id)) {
                    $out = EGroup::getOptions($cat_id);
                    $result = [];
                    $tmp_arr = [];
                    foreach ($out as $key => $value) {
                        $tmp_arr = ['id' => $key, 'name' => $value];
                        $result[] = $tmp_arr;
                    }
                    return Json::encode(['output' => $result, 'selected' => '']);
                }
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetExamTypeData($curriculum = null, $semester = null, $subject = null, $exam_type = null)
    {
        $result = [
            'error' => 0,
        ];
        if (Yii::$app->request->post('curriculum') && Yii::$app->request->post('semester') && Yii::$app->request->post('subject') && Yii::$app->request->post('exam_type')) {
            $curriculum = Yii::$app->request->post('curriculum');
            $semester = Yii::$app->request->post('semester');
            $subject = Yii::$app->request->post('subject');
            $exam_type = Yii::$app->request->post('exam_type');
            $max_ball = ECurriculumSubjectExamType::getExamTypeOneByCurriculumSemesterSubject($curriculum, $semester, $subject, $exam_type)->max_ball;
            try {
                $result['error'] = 0;
                $result['max'] = $max_ball;
                $result['message'] = "Ok";
            } catch (Exception $e) {
                $result['error'] = 1;
                $result['message'] = __('Request failed');
                $this->addError($e->getMessage());
            }
        } else {
            $curriculum = null;
            $semester = null;
            $subject = null;
            $exam_type = null;
            $result['error'] = 1;
            $result['message'] = __('Request failed');
        }
        return json_encode($result);
    }

    /**
     * @skipAccess
     */
    public function actionGetCurriculumSubjects()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $employee = $this->get('employee');
                $sub_cat_id = $parents[0];

                $ids = ESubjectExamSchedule::find()
                    ->select(['_subject'])
                    ->where([
                        '_curriculum' => $sub_cat_id,
                    ]);

                if (Yii::$app->user->identity->role->isTeacherRole()) {
                    $ids->andFilterWhere(['_employee' => Yii::$app->user->identity->_employee]);
                }

                $ids->groupBy(['_subject']);
                $out = ArrayHelper::map($ids->all(), '_subject', 'subject.name');

                if (count($out)) {
                    $result = [];
                    foreach ($out as $key => $value) {
                        $result[] = ['id' => $key, 'name' => $value];
                    }
                    return Json::encode(['output' => $result, 'selected' => $result[0]['id']]);
                }
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetCertificateCommittee()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $faculty = $parents[0];
                if (!empty($faculty)) {
                    //$sub_cat_id = $parents[1];
                    $models = ECertificateCommittee::getSelectOptions($faculty ?? false);
                    $result = [];
                    foreach ($models as $key => $value) {
                        $tmp_arr = ['id' => $key, 'name' => $value];
                        $result[] = $tmp_arr;
                    }
                    return Json::encode(['output' => $result, 'selected' => '']);
                    //return;
                }
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetCertificateCommittee2()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $faculty = $parents[0];
                $department = /*$parents[1] ?? */
                    "";
                $year = $parents[1] ?? "";
                if (!empty($faculty) &&/* !empty($department) &&*/ !empty($year)) {
                    //$sub_cat_id = $parents[1];
                    $models = ECertificateCommittee::getSelectOptions($faculty ?? "", $department, $year, ECertificateCommittee::TYPE_DEFEND);
                    $result = [];
                    foreach ($models as $key => $value) {
                        $tmp_arr = ['id' => $key, 'name' => $value];
                        $result[] = $tmp_arr;
                    }
                    return Json::encode(['output' => $result, 'selected' => '']);
                    //return;
                }
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetGraduateWorks()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $student = $parents[0];
                $sub_cat_id = $parents[1] ?? "";
                if (!empty($student) && !empty($sub_cat_id)) {
                    $models = EGraduateQualifyingWork::getSelectOptions($student ?? "", $sub_cat_id);
                    $result = [];
                    foreach ($models as $key => $value) {
                        $tmp_arr = ['id' => $key, 'name' => $value];
                        $result[] = $tmp_arr;
                    }
                    return Json::encode(['output' => $result, 'selected' => '']);
                    //return;
                }
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetSpecialtyGroups()
    {
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $specialty = $parents[0];
                if (!empty($specialty)) {
                    //$sub_cat_id = $parents[1];
                    $models = EGroup::find()
                        //->select(['_subject'])
                        ->where(
                            [
                                'active' => true,
                                'e_group._specialty_id' => $specialty,
                            ]
                        )->all();
                    $out = ArrayHelper::map($models, 'id', 'name');
                    $result = [];
                    foreach ($out as $key => $value) {
                        $tmp_arr = ['id' => $key, 'name' => $value];
                        $result[] = $tmp_arr;
                    }
                    return Json::encode(['output' => $result, 'selected' => '']);
                    //return;
                }
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetSpecialtiesByFaculty()
    {
        //  const EDUCATION_TYPE_BACHELOR = '11';
        // const EDUCATION_TYPE_MASTER = '12';

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $faculty = $parents[0];
                $cat_id = $parents[1] ?? null;
                if (!empty($cat_id)) {
                    if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
                        $faculty = $this->_user()->employee->deanFaculties->id;
                    }
                    if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
                        $faculty = $this->_user()->employee->headDepartments->parent;
                    }
                    if ($cat_id !== null) {
                        $out = ESpecialty::getHigherSpecialtyByType($cat_id, $faculty);
                    }
                    $result = [];
                    foreach ($out as $key => $value) {
                        $tmp_arr = ['id' => $key, 'name' => $value];
                        $result[] = $tmp_arr;
                    }
                    return Json::encode(['output' => $result, 'selected' => '']);
                    //return;
                }
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetGraduateWorkSubjects()
    {
        //  const EDUCATION_TYPE_BACHELOR = '11';
        // const EDUCATION_TYPE_MASTER = '12';

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $student = $parents[0];
                $gw = $parents[1] ?? null;
                if (!empty($student) && !empty($gw)/* && is_int($gw)*/) {
                    if (($this->_user()->role->code === AdminRole::CODE_DEAN || $this->_user()->role->code === AdminRole::CODE_DEPARTMENT)) {
                        $faculty = $this->_user()->employee->deanFaculties->id;
                    }
                    $student = EStudent::findOne($student);
                    if ($student !== null) {
                        $out = EStudentMeta::getStudentSubjects($student->meta)->andFilterWhere(['_rating_grade' => RatingGrade::RATING_GRADE_GRADUATE])->all();
                    }
                    $result = [];
                    foreach ($out as $key => $value) {
                        $tmp_arr = ['id' => $value->_subject, 'name' => $value->subject->name];
                        $result[] = $tmp_arr;
                    }
                    return Json::encode(['output' => $result, 'selected' => '']);
                    //return;
                }
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetQualifications()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $spec = $parents[0];
                $faculty = "";
                if (!empty($spec)) {
                    if ($this->_user()->role->code === AdminRole::CODE_DEAN) {
                        $faculty = $this->_user()->employee->deanFaculties->id;
                    }
                    if ($this->_user()->role->code === AdminRole::CODE_DEPARTMENT) {
                        $faculty = $this->_user()->employee->headDepartments->parent;
                    }
                    $out = EQualification::getSelectOptions($spec, $faculty);
                    $result = [];
                    foreach ($out as $key => $value) {
                        $tmp_arr = ['id' => $key, 'name' => $value];
                        $result[] = $tmp_arr;
                    }
                    return Json::encode(['output' => $result, 'selected' => '']);
                    //return;
                }
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetDepartmentsByEducationType()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $sub_cat_id = $parents[1];
                if ($sub_cat_id == EducationType::EDUCATION_TYPE_BACHELOR) {
                    if ($cat_id > 0) {
                        $catList = EDepartment::getDepartmentList($cat_id);
                        $out = ArrayHelper::map($catList, 'id', 'name');
                    }
                } elseif ($sub_cat_id == EducationType::EDUCATION_TYPE_MASTER) {
                    $catList = EDepartment::getDepartmentList();
                    $out = ArrayHelper::map($catList, 'id', 'name');
                }

                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetDiplomaBlank()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $type = $this->get('type');
                $cat_id = $parents[0];
                if ($type && $cat_id) {
                    $out = EDiplomaBlank::getSelectOptions($type, $cat_id);
                } else {
                    $out = EDiplomaBlank::getSelectOptions();
                }

                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetCurriculumBySpecialty()
    {
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $faculty = $parents[0];
                $eduType = $parents[1];
                $specialty = $parents[2];
                $eduForm = $parents[3];
                $out = ECurriculum::getOptionsByEduTypeFormSpec($eduType, $eduForm, $faculty, $specialty);
                /*$catList = ECurriculum::find()
                    ->where('_department = :_department AND _education_type = :type AND _specialty_id = :_specialty_id AND _education_form=:form', [':_department' => $faculty, ':type' => $eduType, ':_specialty_id' => $specialty, ':form' => $eduForm])
                    //->andFilterWhere(['_department' => $faculty])
                    ->all();
                $out = ArrayHelper::map($catList, 'id', 'name');*/
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGet_courses()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                $cat_id2 = $parents[1];
                $catList = Semester::getCourseOptions($cat_id, $cat_id2);
                $out = ArrayHelper::map($catList, '_level', 'level.name');
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '1']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    /**
     * @skipAccess
     */
    public function actionGetGraduateFieldTypes()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cat_id = $parents[0];
                if ($cat_id == EStudentEmployment::EMPLOYMENT_STATUS_MASTER) {
                    $out = GraduateFieldsType::getFieldTypeOptions();
                } elseif ($cat_id == EStudentEmployment::EMPLOYMENT_STATUS_EMPLOYEE) {
                    $out = GraduateFieldsType::getFieldTypeOptions('other');
                }
                /*else{
                    $out = GraduateFieldsType::getFieldTypeOptions('other');
                }*/
                $result = [];
                $tmp_arr = [];
                foreach ($out as $key => $value) {
                    $tmp_arr = ['id' => $key, 'name' => $value];
                    $result[] = $tmp_arr;
                }
                return Json::encode(['output' => $result, 'selected' => '']);
                //return;
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

}
