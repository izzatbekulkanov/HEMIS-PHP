<?php

namespace backend\controllers;

use backend\assets\VendorAsset;
use backend\models\FilterForm;
use backend\models\FormUploadTrans;
use common\components\AccessResources;
use common\components\Config;
use common\models\archive\EStudentEmployment;
use common\models\curriculum\ECurriculumSubjectTopic;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ESubjectResource;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\finance\EStudentContract;
use common\models\finance\EStudentContractType;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use common\models\student\EGroup;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\curriculum\ECurriculum;
use common\models\system\AdminRole;
use common\models\system\classifier\AcademicDegree;
use common\models\system\classifier\AcademicRank;
use common\models\system\classifier\BachelorSpeciality;
use common\models\system\classifier\CitizenshipType;
use common\models\system\classifier\EducationType;
use common\models\system\classifier\EmploymentForm;
use common\models\system\classifier\Gender;
use common\models\system\classifier\GraduateFieldsType;
use common\models\system\classifier\GraduateInactiveType;
use common\models\system\classifier\MasterSpeciality;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\SemestrType;
use common\models\system\classifier\SocialCategory;
use common\models\system\classifier\StructureType;
use common\models\system\classifier\StudentStatus;
use common\models\system\classifier\TeacherPositionType;
use common\models\system\classifier\TeacherStatus;
use Yii;
use yii\data\ActiveDataProvider;
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

class StatisticalController extends BackendController
{
    public $activeMenu = 'statistical';

    public function actionByStudent()
    {
        $searchModel = new FilterForm();
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

        $autumn = array();
        $spring = array();
        $semesters = array();
        $result = array();
        $current_year = EducationYear::getCurrentYear();

        if ($current_year != null) {
            $searchModel->_education_year = $current_year->code;
            $searchModel->_semester_type = $current_year->_semestr_type;
        }

        if (!$searchModel->_faculty)
            $searchModel->_faculty = $faculty;

        if ($searchModel->load(Yii::$app->request->post())) {

            $semestr_list = EStudentMeta::find()->select('_semestr')->where(['_education_year' => $searchModel->_education_year, '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED])->groupBy(['_semestr'])->all();
            $level_list = EStudentMeta::find()->select('_level')->where(['_education_year' => $searchModel->_education_year, '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED])->groupBy(['_level'])->orderBy(['_level' => SORT_ASC])->all();
            foreach ($semestr_list as $semestr) {
                if ($semestr->_semestr % 2 == 1) {
                    $autumn[$semestr->_semestr] = $semestr->_semestr;
                } elseif ($semestr->_semestr % 2 == 0) {
                    $spring[$semestr->_semestr] = $semestr->_semestr;
                }
            }

            if ($searchModel->_semester_type == SemestrType::EDUCATION_TYPE_AUTUMN) {
                $semesters = $autumn;
            } elseif ($searchModel->_semester_type == SemestrType::EDUCATION_TYPE_SPRING) {
                $semesters = $spring;
            }
            if ($searchModel->_category == 11) {
                $report = EStudentMeta::find()
                    ->joinWith(['student'])
                    ->select('e_student_meta._level, e_student_meta._payment_form, e_student._gender as gender, COUNT(e_student_meta._student) as _students')
                    ->where([
                        'e_student_meta._education_year' => $searchModel->_education_year,
                        'e_student_meta._department' => $searchModel->_faculty,
                        'e_student_meta._education_type' => $searchModel->_education_type,
                        'e_student_meta._education_form' => $searchModel->_education_form,
                        'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                        'e_student_meta.active' => EStudentMeta::STATUS_ENABLE,
                    ])
                    ->andWhere(['in', 'e_student_meta._semestr', $semesters])
                    ->groupBy(['e_student_meta._level', 'e_student_meta._payment_form', 'e_student._gender'])
                    ->all();
                foreach ($level_list as $level) {
                    foreach ($report as $item) {
                        if ($level->_level == $item->_level) {
                            if ($item->_payment_form == PaymentForm::PAYMENT_FORM_BUDGET) {
                                if ($item->gender == Gender::GENDER_MALE) {
                                    $result[$item->_level][$item->_payment_form][$item->gender] = $item->_students;
                                } elseif ($item->gender == Gender::GENDER_FEMALE) {
                                    $result[$item->_level][$item->_payment_form][$item->gender] = $item->_students;
                                }
                            } elseif ($item->_payment_form == PaymentForm::PAYMENT_FORM_CONTRACT) {
                                if ($item->gender == Gender::GENDER_MALE) {
                                    $result[$item->_level][$item->_payment_form][$item->gender] = $item->_students;
                                } elseif ($item->gender == Gender::GENDER_FEMALE) {
                                    $result[$item->_level][$item->_payment_form][$item->gender] = $item->_students;
                                }
                            }
                        }
                    }
                }
            }

            if ($searchModel->_category == 12) {
                $report = EStudentMeta::find()
                    ->joinWith(['student'])
                    ->select('e_student_meta._specialty_id, e_student_meta._payment_form, e_student._gender as gender, COUNT(e_student_meta._student) as _students')
                    ->where([
                        'e_student_meta._education_year' => $searchModel->_education_year,
                        'e_student_meta._department' => $searchModel->_faculty,
                        'e_student_meta._education_type' => $searchModel->_education_type,
                        'e_student_meta._education_form' => $searchModel->_education_form,
                        'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                        'e_student_meta.active' => EStudentMeta::STATUS_ENABLE,
                    ])
                    ->andWhere(['in', 'e_student_meta._semestr', $semesters])
                    ->groupBy(['e_student_meta._specialty_id', 'e_student_meta._payment_form', 'e_student._gender'])
                    ->all();
                $specialty_list = array();
                foreach ($report as $item) {
                    $specialty_list[$item->_specialty_id] = $item->_specialty_id;
                }
                foreach ($specialty_list as $specialty) {
                    foreach ($report as $item) {
                        if ($specialty === $item->_specialty_id) {
                            if ($item->_payment_form == PaymentForm::PAYMENT_FORM_BUDGET) {
                                if ($item->gender == Gender::GENDER_MALE) {
                                    $result[$item->_specialty_id][$item->_payment_form][$item->gender] = $item->_students;
                                } elseif ($item->gender == Gender::GENDER_FEMALE) {
                                    $result[$item->_specialty_id][$item->_payment_form][$item->gender] = $item->_students;
                                }
                            } elseif ($item->_payment_form == PaymentForm::PAYMENT_FORM_CONTRACT) {
                                if ($item->gender == Gender::GENDER_MALE) {
                                    $result[$item->_specialty_id][$item->_payment_form][$item->gender] = $item->_students;
                                } elseif ($item->gender == Gender::GENDER_FEMALE) {
                                    $result[$item->_specialty_id][$item->_payment_form][$item->gender] = $item->_students;
                                }
                            }
                        }
                    }
                }
            }

            if ($searchModel->_category == 13) {
                $report = EStudentMeta::find()
                    ->joinWith(['student'])
                    ->select('e_student_meta._level, e_student._nationality as nationality, e_student._gender as gender, COUNT(e_student_meta._student) as _students')
                    ->where([
                        'e_student_meta._education_year' => $searchModel->_education_year,
                        'e_student_meta._department' => $searchModel->_faculty,
                        'e_student_meta._education_type' => $searchModel->_education_type,
                        'e_student_meta._education_form' => $searchModel->_education_form,
                        'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                        'e_student_meta.active' => EStudentMeta::STATUS_ENABLE,
                    ])
                    ->andWhere(['in', 'e_student_meta._semestr', $semesters])
                    ->groupBy(['e_student_meta._level', 'e_student._nationality', 'e_student._gender'])
                    ->all();
                $nation_list = array();
                foreach ($report as $item) {
                    $nation_list[$item->nationality] = $item->nationality;
                }
                foreach ($level_list as $level) {
                    foreach ($report as $item) {
                        if ($level->_level == $item->_level) {
                            if ($item->gender == Gender::GENDER_MALE) {
                                $result[$item->nationality][$item->_level][$item->gender] = $item->_students;
                            } elseif ($item->gender == Gender::GENDER_FEMALE) {
                                $result[$item->nationality][$item->_level][$item->gender] = $item->_students;
                            }
                        }
                    }
                }
            }

            if ($searchModel->_category == 14) {
                $report = EStudentMeta::find()
                    ->joinWith(['student'])
                    ->select('e_student_meta._level, e_student._province as province, e_student._gender as gender, COUNT(e_student_meta._student) as _students')
                    ->where([
                        'e_student_meta._education_year' => $searchModel->_education_year,
                        'e_student_meta._department' => $searchModel->_faculty,
                        'e_student_meta._education_type' => $searchModel->_education_type,
                        'e_student_meta._education_form' => $searchModel->_education_form,
                        'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                        'e_student_meta.active' => EStudentMeta::STATUS_ENABLE,
                    ])
                    ->andWhere(['in', 'e_student_meta._semestr', $semesters])
                    ->groupBy(['e_student_meta._level', 'e_student._province', 'e_student._gender'])
                    ->all();
                $province_list = array();
                foreach ($report as $item) {
                    $province_list[$item->province] = $item->province;
                }
                foreach ($level_list as $level) {
                    foreach ($report as $item) {
                        if ($level->_level == $item->_level) {
                            if ($item->gender == Gender::GENDER_MALE) {
                                $result[$item->province][$item->_level][$item->gender] = $item->_students;
                            } elseif ($item->gender == Gender::GENDER_FEMALE) {
                                $result[$item->province][$item->_level][$item->gender] = $item->_students;
                            }
                        }
                    }
                }
            }

        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'result' => @$result,
            'level_list' => @$level_list,
            'specialty_list' => @$specialty_list,
            'nation_list' => @$nation_list,
            'province_list' => @$province_list,
            'faculty' => $faculty,
        ]);
    }


    public function actionByTeacher()
    {
        $searchModel = new FilterForm();

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

        $result = array();
        if ($searchModel->load(Yii::$app->request->post())) {
            if ($searchModel->_faculty)
                $faculty = $searchModel->_faculty;
            $departments = EDepartment::find()->select('id, name')->where(['parent' => $faculty, '_structure_type' => StructureType::STRUCTURE_TYPE_DEPARTMENT, 'active' => EDepartment::STATUS_ENABLE])->all();
            $department_list = array();
            foreach ($departments as $item) {
                $department_list[$item->id] = $item->id;
            }

            if ($searchModel->_category == 11) {
                $degrees = AcademicDegree::find()
                    ->where(['active' => true])
                    ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
                    ->all();

                $report = EEmployeeMeta::find()
                    ->joinWith(['employee'])
                    ->select('e_employee_meta._department, e_employee._academic_degree as academic_degree, e_employee._gender as gender, COUNT(e_employee_meta._employee) as _employees')
                    ->where([
                        'e_employee_meta._employee_status' => TeacherStatus::TEACHER_STATUS_WORKING,
                        'e_employee.active' => EEmployee::STATUS_ENABLE,
                    ])
                    ->andWhere(['in', 'e_employee_meta._department', $department_list])
                    ->andWhere(['in', 'e_employee_meta._position', TeacherPositionType::TEACHER_POSITIONS])
                    ->groupBy(['e_employee_meta._department', 'e_employee._academic_degree', 'e_employee._gender'])
                    ->all();

                foreach ($departments as $dep) {
                    foreach ($report as $item) {
                        if ($dep->id == $item->_department) {
                            if ($item->gender == Gender::GENDER_MALE) {
                                if ($item->academic_degree == AcademicDegree::ACADEMIC_DEGREE_NONE) {
                                    $result[$item->_department][AcademicDegree::ACADEMIC_DEGREE_NONE][$item->gender] = $item->_employees;
                                } elseif ($item->academic_degree == AcademicDegree::ACADEMIC_DEGREE_PHD) {
                                    $result[$item->_department][AcademicDegree::ACADEMIC_DEGREE_PHD][$item->gender] = $item->_employees;
                                } elseif ($item->academic_degree == AcademicDegree::ACADEMIC_DEGREE_DSC) {
                                    $result[$item->_department][AcademicDegree::ACADEMIC_DEGREE_DSC][$item->gender] = $item->_employees;
                                }
                            } elseif ($item->gender == Gender::GENDER_FEMALE) {
                                if ($item->academic_degree == AcademicDegree::ACADEMIC_DEGREE_NONE) {
                                    $result[$item->_department][AcademicDegree::ACADEMIC_DEGREE_NONE][$item->gender] = $item->_employees;
                                } elseif ($item->academic_degree == AcademicDegree::ACADEMIC_DEGREE_PHD) {
                                    $result[$item->_department][AcademicDegree::ACADEMIC_DEGREE_PHD][$item->gender] = $item->_employees;
                                } elseif ($item->academic_degree == AcademicDegree::ACADEMIC_DEGREE_DSC) {
                                    $result[$item->_department][AcademicDegree::ACADEMIC_DEGREE_DSC][$item->gender] = $item->_employees;
                                }
                            }
                        }
                    }
                }
            }

            if ($searchModel->_category == 12) {
                $degrees = AcademicRank::find()
                    ->where(['active' => true])
                    ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
                    ->all();

                $report = EEmployeeMeta::find()
                    ->joinWith(['employee'])
                    ->select('e_employee_meta._department, e_employee._academic_rank as academic_rank, e_employee._gender as gender, COUNT(e_employee_meta._employee) as _employees')
                    ->where([
                        'e_employee_meta._employee_status' => TeacherStatus::TEACHER_STATUS_WORKING,
                        'e_employee.active' => EEmployee::STATUS_ENABLE,
                    ])
                    ->andWhere(['in', 'e_employee_meta._department', $department_list])
                    ->andWhere(['in', 'e_employee_meta._position', TeacherPositionType::TEACHER_POSITIONS])
                    ->groupBy(['e_employee_meta._department', 'e_employee._academic_rank', 'e_employee._gender'])
                    ->all();

                foreach ($departments as $dep) {
                    foreach ($report as $item) {
                        if ($dep->id == $item->_department) {
                            if ($item->gender == Gender::GENDER_MALE) {
                                if ($item->academic_rank == AcademicRank::ACADEMIC_RANK_NONE) {
                                    $result[$item->_department][AcademicRank::ACADEMIC_RANK_NONE][$item->gender] = $item->_employees;
                                } elseif ($item->academic_rank == AcademicRank::ACADEMIC_RANK_DOCENT) {
                                    $result[$item->_department][AcademicRank::ACADEMIC_RANK_DOCENT][$item->gender] = $item->_employees;
                                } elseif ($item->academic_rank == AcademicRank::ACADEMIC_RANK_SCIENTIFIC) {
                                    $result[$item->_department][AcademicRank::ACADEMIC_RANK_SCIENTIFIC][$item->gender] = $item->_employees;
                                } elseif ($item->academic_rank == AcademicRank::ACADEMIC_RANK_PROFESSOR) {
                                    $result[$item->_department][AcademicRank::ACADEMIC_RANK_PROFESSOR][$item->gender] = $item->_employees;
                                }
                            } elseif ($item->gender == Gender::GENDER_FEMALE) {
                                if ($item->academic_rank == AcademicRank::ACADEMIC_RANK_NONE) {
                                    $result[$item->_department][AcademicRank::ACADEMIC_RANK_NONE][$item->gender] = $item->_employees;
                                } elseif ($item->academic_rank == AcademicRank::ACADEMIC_RANK_DOCENT) {
                                    $result[$item->_department][AcademicRank::ACADEMIC_RANK_DOCENT][$item->gender] = $item->_employees;
                                } elseif ($item->academic_rank == AcademicRank::ACADEMIC_RANK_SCIENTIFIC) {
                                    $result[$item->_department][AcademicRank::ACADEMIC_RANK_SCIENTIFIC][$item->gender] = $item->_employees;
                                } elseif ($item->academic_rank == AcademicRank::ACADEMIC_RANK_PROFESSOR) {
                                    $result[$item->_department][AcademicRank::ACADEMIC_RANK_PROFESSOR][$item->gender] = $item->_employees;
                                }
                            }
                        }
                    }
                }
            }

            if ($searchModel->_category == 13) {
                $degrees = TeacherPositionType::getTeacherOptionList();

                $report = EEmployeeMeta::find()
                    ->joinWith(['employee'])
                    ->select('e_employee_meta._department, e_employee_meta._position, e_employee._gender as gender, COUNT(e_employee_meta._employee) as _employees')
                    ->where([
                        'e_employee_meta._employee_status' => TeacherStatus::TEACHER_STATUS_WORKING,
                        'e_employee.active' => EEmployee::STATUS_ENABLE,
                    ])
                    ->andWhere(['in', 'e_employee_meta._department', $department_list])
                    ->andWhere(['in', 'e_employee_meta._position', TeacherPositionType::TEACHER_POSITIONS])
                    ->groupBy(['e_employee_meta._department', 'e_employee_meta._position', 'e_employee._gender'])
                    ->all();

                foreach ($departments as $dep) {
                    foreach ($report as $item) {
                        if ($dep->id == $item->_department) {
                            if ($item->gender == Gender::GENDER_MALE) {
                                if ($item->_position == TeacherPositionType::TEACHER_POSITION_TYPE_INTERN) {
                                    $result[$item->_department][TeacherPositionType::TEACHER_POSITION_TYPE_INTERN][$item->gender] = $item->_employees;
                                } elseif ($item->_position == TeacherPositionType::TEACHER_POSITION_TYPE_ASSISTANT) {
                                    $result[$item->_department][TeacherPositionType::TEACHER_POSITION_TYPE_ASSISTANT][$item->gender] = $item->_employees;
                                } elseif ($item->_position == TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_TEACHER) {
                                    $result[$item->_department][TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_TEACHER][$item->gender] = $item->_employees;
                                } elseif ($item->_position == TeacherPositionType::TEACHER_POSITION_TYPE_ASSOCIATE_PROFESSOR) {
                                    $result[$item->_department][TeacherPositionType::TEACHER_POSITION_TYPE_ASSOCIATE_PROFESSOR][$item->gender] = $item->_employees;
                                } elseif ($item->_position == TeacherPositionType::TEACHER_POSITION_TYPE_PROFESSOR) {
                                    $result[$item->_department][TeacherPositionType::TEACHER_POSITION_TYPE_PROFESSOR][$item->gender] = $item->_employees;
                                } elseif ($item->_position == TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT) {
                                    $result[$item->_department][TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT][$item->gender] = $item->_employees;
                                }

                            } elseif ($item->gender == Gender::GENDER_FEMALE) {
                                if ($item->_position == TeacherPositionType::TEACHER_POSITION_TYPE_INTERN) {
                                    $result[$item->_department][TeacherPositionType::TEACHER_POSITION_TYPE_INTERN][$item->gender] = $item->_employees;
                                } elseif ($item->_position == TeacherPositionType::TEACHER_POSITION_TYPE_ASSISTANT) {
                                    $result[$item->_department][TeacherPositionType::TEACHER_POSITION_TYPE_ASSISTANT][$item->gender] = $item->_employees;
                                } elseif ($item->_position == TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_TEACHER) {
                                    $result[$item->_department][TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_TEACHER][$item->gender] = $item->_employees;
                                } elseif ($item->_position == TeacherPositionType::TEACHER_POSITION_TYPE_ASSOCIATE_PROFESSOR) {
                                    $result[$item->_department][TeacherPositionType::TEACHER_POSITION_TYPE_ASSOCIATE_PROFESSOR][$item->gender] = $item->_employees;
                                } elseif ($item->_position == TeacherPositionType::TEACHER_POSITION_TYPE_PROFESSOR) {
                                    $result[$item->_department][TeacherPositionType::TEACHER_POSITION_TYPE_PROFESSOR][$item->gender] = $item->_employees;
                                } elseif ($item->_position == TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT) {
                                    $result[$item->_department][TeacherPositionType::TEACHER_POSITION_TYPE_HEAD_OF_DEPARTMENT][$item->gender] = $item->_employees;
                                }
                            }
                        }
                    }
                }
            }

            if ($searchModel->_category == 15) {
                $degrees = EmploymentForm::find()
                    ->where(['active' => true])
                    ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
                    ->all();
                $report = EEmployeeMeta::find()
                    ->joinWith(['employee'])
                    ->select('e_employee_meta._department, e_employee_meta._employment_form, e_employee._gender as gender, COUNT(e_employee_meta._employee) as _employees')
                    ->where([
                        'e_employee_meta._employee_status' => TeacherStatus::TEACHER_STATUS_WORKING,
                        'e_employee.active' => EEmployee::STATUS_ENABLE,
                    ])
                    ->andWhere(['in', 'e_employee_meta._department', $department_list])
                    ->andWhere(['in', 'e_employee_meta._position', TeacherPositionType::TEACHER_POSITIONS])
                    ->groupBy(['e_employee_meta._department', 'e_employee_meta._employment_form', 'e_employee._gender'])
                    ->all();

                foreach ($departments as $dep) {
                    foreach ($report as $item) {
                        if ($dep->id == $item->_department) {
                            if ($item->gender == Gender::GENDER_MALE) {
                                if ($item->_employment_form == EmploymentForm::EMPLOYMENT_FORM_MAIN) {
                                    $result[$item->_department][EmploymentForm::EMPLOYMENT_FORM_MAIN][$item->gender] = $item->_employees;
                                } elseif ($item->_employment_form == EmploymentForm::EMPLOYMENT_FORM_INDOOR) {
                                    $result[$item->_department][EmploymentForm::EMPLOYMENT_FORM_INDOOR][$item->gender] = $item->_employees;
                                } elseif ($item->_employment_form == EmploymentForm::EMPLOYMENT_FORM_OUTDOOR) {
                                    $result[$item->_department][EmploymentForm::EMPLOYMENT_FORM_OUTDOOR][$item->gender] = $item->_employees;
                                } elseif ($item->_employment_form == EmploymentForm::EMPLOYMENT_FORM_TIMEBY) {
                                    $result[$item->_department][EmploymentForm::EMPLOYMENT_FORM_TIMEBY][$item->gender] = $item->_employees;
                                }


                            } elseif ($item->gender == Gender::GENDER_FEMALE) {
                                if ($item->_employment_form == EmploymentForm::EMPLOYMENT_FORM_MAIN) {
                                    $result[$item->_department][EmploymentForm::EMPLOYMENT_FORM_MAIN][$item->gender] = $item->_employees;
                                } elseif ($item->_employment_form == EmploymentForm::EMPLOYMENT_FORM_INDOOR) {
                                    $result[$item->_department][EmploymentForm::EMPLOYMENT_FORM_INDOOR][$item->gender] = $item->_employees;
                                } elseif ($item->_employment_form == EmploymentForm::EMPLOYMENT_FORM_OUTDOOR) {
                                    $result[$item->_department][EmploymentForm::EMPLOYMENT_FORM_OUTDOOR][$item->gender] = $item->_employees;
                                } elseif ($item->_employment_form == EmploymentForm::EMPLOYMENT_FORM_TIMEBY) {
                                    $result[$item->_department][EmploymentForm::EMPLOYMENT_FORM_TIMEBY][$item->gender] = $item->_employees;
                                }


                            }
                        }
                    }
                }
            }

        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'result' => @$result,
            'departments' => @$departments,
            'degrees' => @$degrees,
            'specialty_list' => @$specialty_list,
            'nation_list' => @$nation_list,
            'province_list' => @$province_list,
            'faculty' => $faculty,

        ]);
    }

    public function actionByResource()
    {
        $searchModel = new ECurriculum();
        $dataProvider = $searchModel->search($this->getFilterParams());
        if ($this->_user()->role->isDeanOrTutorRole()) {
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

        return $this->render('curriculum-list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionResourceInfo($id = "false", $code = "false")
    {
        $curriculum = $this->findCurriculumModel($id);

        $searchModel = new ESubjectResource();
        $dataProvider = $searchModel->search($this->getFilterParams());
        $dataProvider->query->andFilterWhere(['_curriculum' => $curriculum->id]);

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
        /*if ($code = $this->get('code')) {
            if ($model = ECurriculumSubjectTopic::findOne(['id' => $code])) {
                if ($this->get('delete')) {
                    try {
                        if ($model->delete()) {
                            $this->addSuccess(__('Item [{code}] of Curriculum Subject Topic is deleted successfully', ['code' => $model->id]));
                        }
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());
                    }
                    return $this->redirect(['curriculum/subject-topic', 'id' => $curriculum->id]);

                }
            } else {
                return $this->redirect(['curriculum/subject-topic', 'id' => $curriculum->id, 'code' => $model->id]);
            }
        }*/

        return $this->renderView([
            //'model' => $model,
            'curriculum' => $curriculum,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionByStudentGeneral()
    {
        $searchModel = new FilterForm();
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


        $autumn = array();
        $spring = array();
        $semesters = array();
        $result = array();
        $current_year = EducationYear::getCurrentYear();
        if ($current_year != null) {
            $searchModel->_education_year = $current_year->code;
            $searchModel->_semester_type = $current_year->_semestr_type;
        }
        if ($searchModel->load(Yii::$app->request->post())) {
            if ($searchModel->_faculty)
                $faculty = $searchModel->_faculty;
            $semestr_list = EStudentMeta::find()->select('_semestr')->where(['_education_year' => $searchModel->_education_year, '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED])->groupBy(['_semestr'])->all();
            $department_list = EDepartment::find()->select('id')->where(['_structure_type' => StructureType::STRUCTURE_TYPE_FACULTY, 'active' => EDepartment::STATUS_ENABLE])->orderBy(['name' => SORT_ASC])->all();
            $social_list = SocialCategory::find()->select('code')->where(['active' => SocialCategory::STATUS_ENABLE])->orderBy(['code' => SORT_ASC])->all();
            $education_form_list = EStudentMeta::find()->select('_education_form')->where(['_education_year' => $searchModel->_education_year, '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED])->groupBy(['_education_form'])->orderBy(['_education_form' => SORT_ASC])->all();
            $level_list = EStudentMeta::find()->select('_level')->where(['_education_year' => $searchModel->_education_year, '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED])->groupBy(['_level'])->orderBy(['_level' => SORT_ASC])->all();

            foreach ($semestr_list as $semestr) {
                if ($semestr->_semestr % 2 == 1) {
                    $autumn[$semestr->_semestr] = $semestr->_semestr;
                } elseif ($semestr->_semestr % 2 == 0) {
                    $spring[$semestr->_semestr] = $semestr->_semestr;
                }
            }

            if ($searchModel->_semester_type == SemestrType::EDUCATION_TYPE_AUTUMN) {
                $semesters = $autumn;
            } elseif ($searchModel->_semester_type == SemestrType::EDUCATION_TYPE_SPRING) {
                $semesters = $spring;
            }
            if ($searchModel->_category == 11) {
                $report = EStudentMeta::find()
                    ->joinWith(['student'])
                    ->select('e_student_meta._department as _department, e_student_meta._education_form as _education_form, e_student_meta._level as _level, COUNT(e_student_meta._student) as _students')
                    ->where([
                        'e_student_meta._education_year' => $searchModel->_education_year,
                        //                        'e_student_meta._department' => $faculty,
                        'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                        'e_student_meta.active' => EStudentMeta::STATUS_ENABLE,
                    ])
                    ->andWhere(['in', 'e_student_meta._semestr', $semesters])
                    ->groupBy(['e_student_meta._department', 'e_student_meta._education_form', 'e_student_meta._level'])
                    ->all();

                foreach ($department_list as $department) {
                    foreach ($report as $item) {
                        if ($department->id == $item->_department) {
                            foreach ($education_form_list as $form_list) {
                                if ($form_list->_education_form == $item->_education_form) {
                                    foreach ($level_list as $level) {
                                        if ($level->_level == $item->_level) {
                                            $result[$item->_department][$item->_education_form][$item->_level] = $item->_students;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($searchModel->_category == 12) {
                $report = EStudentMeta::find()
                    ->joinWith(['student'])
                    ->select('e_student_meta._department as _department, e_student._social_category as _social_category, COUNT(e_student_meta._student) as _students')
                    ->where([
                        'e_student_meta._education_year' => $searchModel->_education_year,
                        //                        'e_student_meta._department' => $faculty,
                        'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                        'e_student_meta.active' => EStudentMeta::STATUS_ENABLE,
                    ])
                    ->andWhere(['in', 'e_student_meta._semestr', $semesters])
                    ->groupBy(['e_student_meta._department', 'e_student._social_category'])
                    ->all();

                foreach ($department_list as $department) {
                    foreach ($report as $item) {
                        if ($department->id == $item->_department) {
                            foreach ($social_list as $social) {
                                if ($social->code == $item->_social_category) {
                                    $result[$item->_department][$item->_social_category] = $item->_students;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'result' => @$result,
            'department_list' => @$department_list,
            'education_form_list' => @$education_form_list,
            'level_list' => @$level_list,
            'social_list' => @$social_list,
            'faculty' => $faculty,
        ]);
    }

    public function actionByStudentSocial()
    {
        $searchModel = new FilterForm();
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
        $autumn = array();
        $spring = array();
        $semesters = array();

        $current_year = EducationYear::getCurrentYear();
        if ($current_year != null) {
            $searchModel->_education_year = $current_year->code;
            $searchModel->_semester_type = $current_year->_semestr_type;
        }

        if (!$searchModel->_faculty)
            $searchModel->_faculty = $faculty;

        if ($searchModel->load(Yii::$app->request->get())) {
            $semestr_list = EStudentMeta::find()->select('_semestr')->where(['_education_year' => $searchModel->_education_year, '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED])->groupBy(['_semestr'])->all();

            foreach ($semestr_list as $semestr) {
                if ($semestr->_semestr % 2 == 1) {
                    $autumn[$semestr->_semestr] = $semestr->_semestr;
                } elseif ($semestr->_semestr % 2 == 0) {
                    $spring[$semestr->_semestr] = $semestr->_semestr;
                }
            }

            if ($searchModel->_semester_type == SemestrType::EDUCATION_TYPE_AUTUMN) {
                $semesters = $autumn;
            } elseif ($searchModel->_semester_type == SemestrType::EDUCATION_TYPE_SPRING) {
                $semesters = $spring;
            }

            $report = EStudentMeta::find();
            $report->joinWith(['student']);
            // $report->select('e_student.first_name, e_student.second_name, e_student_meta._department as _department, e_student._social_category as _social_category');
            $report->where([
                'e_student_meta._education_year' => $searchModel->_education_year,
                'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                'e_student_meta.active' => EStudentMeta::STATUS_ENABLE,
            ]);
            $report->andWhere(['in', 'e_student_meta._semestr', $semesters]);
            if ($searchModel->_faculty) {
                $report->andWhere([
                    'e_student_meta._department' => $searchModel->_faculty,
                ]);
            }
            if ($searchModel->_social_category) {
                $report->andWhere([
                    'e_student._social_category' => $searchModel->_social_category,
                ]);
            }

            $dataProvider = new ActiveDataProvider(
                [
                    'query' => $report,
                    'sort' => [
                        'defaultOrder' => ['e_student.second_name' => SORT_ASC],
                        'attributes' => [
                            'e_student.second_name',
                            '_department',
                            '_education_year',
                            '_education_type',
                            '_education_form',
                            '_semestr',
                            'e_student._social_category',
                            'updated_at',
                            'created_at',
                        ],
                    ],
                    'pagination' => [
                        'pageSize' => 100,
                    ],
                ]
            );

            //$report = $report->all();

        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'faculty' => $faculty,
            'dataProvider' => @$dataProvider,
        ]);
    }

    public function actionByContract()
    {
        $searchModel = new FilterForm();
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


        $autumn = array();
        $spring = array();
        $semesters = array();
        $result = array();
        $resultContract = array();

        if (!$searchModel->_faculty)
            $searchModel->_faculty = $faculty;

        if ($searchModel->load(Yii::$app->request->post())) {

            $semestr_list = EStudentMeta::find()->select('_semestr')->where(['_education_year' => $searchModel->_education_year, '_student_status' => StudentStatus::STUDENT_TYPE_STUDIED])->groupBy(['_semestr'])->all();
            foreach ($semestr_list as $semestr) {
                if ($semestr->_semestr % 2 == 1) {
                    $autumn[$semestr->_semestr] = $semestr->_semestr;
                } elseif ($semestr->_semestr % 2 == 0) {
                    $spring[$semestr->_semestr] = $semestr->_semestr;
                }
            }

            if ($searchModel->_semester_type == SemestrType::EDUCATION_TYPE_AUTUMN) {
                $semesters = $autumn;
            } elseif ($searchModel->_semester_type == SemestrType::EDUCATION_TYPE_SPRING) {
                $semesters = $spring;
            }
            $report = EStudentMeta::find()
                ->select('e_student_meta._level, COUNT(e_student_meta._student) as _students, e_student_meta._education_form')
                ->where([
                    'e_student_meta._education_year' => $searchModel->_education_year,
                    'e_student_meta._department' => $searchModel->_faculty,
                    'e_student_meta._education_type' => $searchModel->_education_type,
                    'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_STUDIED,
                    'e_student_meta._payment_form' => PaymentForm::PAYMENT_FORM_CONTRACT,
                ])
                ->andWhere(['in', 'e_student_meta._semestr', $semesters])
                ->groupBy(['e_student_meta._level', 'e_student_meta._education_form'])
                ->all();

            $reportContract = EStudentContract::find()
                ->select('e_student_contract._level, COUNT(e_student_contract._student) as _students, e_student_contract._education_form')
                ->where([
                    'e_student_contract._education_year' => $searchModel->_education_year,
                    'e_student_contract._department' => $searchModel->_faculty,
                    'e_student_contract._education_type' => $searchModel->_education_type,
                    'e_student_contract.contract_status' => EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED,
                    'e_student_contract._manual_type' => EStudentContract::MANUAL_STATUS_TYPE_AUTO,
                    'e_student_contract.accepted' => EStudentContract::STATUS_ENABLE,
                    'e_student_contract.active' => EStudentContract::STATUS_ENABLE,
                ])
                ->groupBy(['e_student_contract._level', 'e_student_contract._education_form'])
                ->all();
            $level_list = [];
            $education_form_list = [];
            foreach ($report as $item) {
                $level_list[$item->_level] = $item->_level;
                $education_form_list[$item->_education_form] = $item->_education_form;
            }

            foreach ($education_form_list as $form) {
                foreach ($report as $item) {
                    if ($form == $item->_education_form) {
                        $result[$item->_education_form][$item->_level] = $item->_students;
                    }
                }
                foreach ($reportContract as $contract) {
                    if ($form == $contract->_education_form) {
                        $resultContract[$contract->_education_form][$contract->_level] = $contract->_students;
                    }
                }
            }

        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'result' => @$result,
            'resultContract' => @$resultContract,
            'level_list' => @$level_list,
            'faculty' => $faculty,
            'education_form_list' => @$education_form_list,
        ]);
    }

    public function actionByEmployment()
    {
        $searchModel = new FilterForm();
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

        $result = [];
        $result_student = [];
        $result_work = [];
        $result_inactive = [];
        $result_citizen = [];
        if ($searchModel->load(Yii::$app->request->post())) {
            if ($searchModel->_faculty)
                $faculty = $searchModel->_faculty;
            $education_types = EducationType::find()
                ->where(['active' => true])
                ->andWhere(
                    ['in', 'code',
                        [
                            EducationType::EDUCATION_TYPE_BACHELOR,
                            EducationType::EDUCATION_TYPE_MASTER,
                        ]
                    ]
                )
                ->orderBy(['code' => SORT_ASC])
                ->all();
            $graduate_field_type_list = GraduateFieldsType::find()->where(['active' => GraduateFieldsType::STATUS_ENABLE])->orderBy(['code' => SORT_ASC])->all();
            $graduate_inactive_type_list_first = GraduateInactiveType::getGraduateInactiveTypeOptions();
            $graduate_inactive_type_list_second = GraduateInactiveType::getGraduateInactiveTypeOptions('other');
            //$graduate_inactive_type_list = GraduateInactiveType::find()->where(['active'=>GraduateInactiveType::STATUS_ENABLE])->orderBy(['code'=>SORT_ASC])->all();
            $workplace_compatibility_list = EStudentEmployment::getWorkplaceCompatibilityStatusOptions();

            $reportStudent = EStudentMeta::find();
            $reportStudent->joinWith(['student']);
            $reportStudent->select('e_student_meta._education_type, _payment_form, e_student._gender as gender, e_student._citizenship as citizenship, COUNT(e_student_meta._student) as _students');
            $reportStudent->where([
                'e_student_meta._education_year' => $searchModel->_education_year,
                'e_student_meta._student_status' => StudentStatus::STUDENT_TYPE_GRADUATED,
            ]);
            if ($searchModel->_education_form) {
                $reportStudent->andFilterWhere([
                    'e_student_meta._education_form' => $searchModel->_education_form,
                ]);
            }
            if ($searchModel->_specialty) {
                $reportStudent->andFilterWhere([
                    'e_student_meta._specialty_id' => $searchModel->_specialty,
                ]);
            }
            if ($faculty) {
                $reportStudent->andFilterWhere([
                    'e_student_meta._department' => $faculty,
                ]);
            }
            $reportStudent->groupBy(['e_student_meta._education_type', 'e_student_meta._payment_form', 'e_student._gender', 'e_student._citizenship']);
            $reportStudent = $reportStudent->all();


            $report = EStudentEmployment::find();
            $report->select('_education_type, _gender, _payment_form, e_student_employment._graduate_fields_type, e_student_employment._graduate_inactive, workplace_compatibility, COUNT(e_student_employment._student) as _students');
            $report->where([
                'e_student_employment._education_year' => $searchModel->_education_year,
                'e_student_employment.active' => EStudentEmployment::STATUS_ENABLE,
            ]);
            if ($searchModel->_education_form) {
                $report->andFilterWhere([
                    'e_student_employment._education_form' => $searchModel->_education_form,
                ]);
            }
            if ($searchModel->_specialty) {
                $report->andFilterWhere([
                    'e_student_employment._specialty' => $searchModel->_specialty,
                ]);
            }
            if ($faculty) {
                $report->andFilterWhere([
                    'e_student_employment._department' => $faculty,
                ]);
            }
            $report->groupBy(['_education_type', '_gender', '_payment_form', 'e_student_employment._graduate_fields_type', 'e_student_employment._graduate_inactive', 'workplace_compatibility']);
            $report = $report->all();

            $level_list = [];
            $education_form_list = [];

            foreach ($education_types as $education_type) {
                foreach ($report as $item) {
                    if ($education_type->code == $item->_education_type) {
                        foreach ($graduate_field_type_list as $type) {
                            if ($type->code == $item->_graduate_fields_type) {
                                if ($item->_gender == Gender::GENDER_FEMALE) {
                                    @$result[$item->_education_type][Gender::GENDER_FEMALE][$item->_graduate_fields_type]['field_female'] += $item->_students;
                                }
                                if ($item->_payment_form == PaymentForm::PAYMENT_FORM_BUDGET) {
                                    @$result[$item->_education_type][PaymentForm::PAYMENT_FORM_BUDGET][$item->_graduate_fields_type]['field_b'] += $item->_students;

                                } elseif ($item->_payment_form == PaymentForm::PAYMENT_FORM_CONTRACT) {
                                    @$result[$item->_education_type][PaymentForm::PAYMENT_FORM_CONTRACT][$item->_graduate_fields_type]['field_c'] += $item->_students;
                                }

                            }
                        }

                        foreach ($workplace_compatibility_list as $key => $compatibility) {
                            if ($key == $item->workplace_compatibility) {
                                if ($item->_gender == Gender::GENDER_FEMALE) {
                                    @$result_work[$item->_education_type][Gender::GENDER_FEMALE][$item->workplace_compatibility]['work_female'] += $item->_students;
                                }
                                if ($item->_payment_form == PaymentForm::PAYMENT_FORM_BUDGET) {
                                    @$result_work[$item->_education_type][PaymentForm::PAYMENT_FORM_BUDGET][$item->workplace_compatibility]['work_b'] += $item->_students;

                                } elseif ($item->_payment_form == PaymentForm::PAYMENT_FORM_CONTRACT) {
                                    @$result_work[$item->_education_type][PaymentForm::PAYMENT_FORM_CONTRACT][$item->workplace_compatibility]['work_c'] += $item->_students;
                                }

                            }
                        }

                        foreach ($graduate_inactive_type_list_first as $type) {
                            if ($type->code == $item->_graduate_inactive) {
                                if ($item->_gender == Gender::GENDER_FEMALE) {
                                    @$result_inactive[$item->_education_type][Gender::GENDER_FEMALE][$item->_graduate_inactive]['inactive_female'] += $item->_students;
                                }
                                if ($item->_payment_form == PaymentForm::PAYMENT_FORM_BUDGET) {
                                    @$result_inactive[$item->_education_type][PaymentForm::PAYMENT_FORM_BUDGET][$item->_graduate_inactive]['inactive_b'] += $item->_students;
                                } elseif ($item->_payment_form == PaymentForm::PAYMENT_FORM_CONTRACT) {
                                    @$result_inactive[$item->_education_type][PaymentForm::PAYMENT_FORM_CONTRACT][$item->_graduate_inactive]['inactive_c'] += $item->_students;
                                }

                            }
                        }

                        foreach ($graduate_inactive_type_list_second as $type) {
                            if ($type->code == $item->_graduate_inactive) {
                                if ($item->_gender == Gender::GENDER_FEMALE) {
                                    @$result_inactive[$item->_education_type][Gender::GENDER_FEMALE][$item->_graduate_inactive]['inactive_female'] += $item->_students;
                                }
                                if ($item->_payment_form == PaymentForm::PAYMENT_FORM_BUDGET) {
                                    @$result_inactive[$item->_education_type][PaymentForm::PAYMENT_FORM_BUDGET][$item->_graduate_inactive]['inactive_b'] += $item->_students;
                                } elseif ($item->_payment_form == PaymentForm::PAYMENT_FORM_CONTRACT) {
                                    @$result_inactive[$item->_education_type][PaymentForm::PAYMENT_FORM_CONTRACT][$item->_graduate_inactive]['inactive_c'] += $item->_students;
                                }

                            }
                        }
                    }
                }


                foreach ($reportStudent as $item) {
                    if ($education_type->code == $item->_education_type) {
                        if ($item->gender == Gender::GENDER_FEMALE) {
                            @$result_student[$item->_education_type][Gender::GENDER_FEMALE]['female'] += @$item->_students;
                        }
                        if ($item->_payment_form == PaymentForm::PAYMENT_FORM_BUDGET) {
                            @$result_student[$item->_education_type][PaymentForm::PAYMENT_FORM_BUDGET]['b'] += @$item->_students;
                        } elseif ($item->_payment_form == PaymentForm::PAYMENT_FORM_CONTRACT) {
                            @$result_student[$item->_education_type][PaymentForm::PAYMENT_FORM_CONTRACT]['c'] += @$item->_students;
                        }
                        if ($item->citizenship == CitizenshipType::CITIZENSHIP_TYPE_FOREIGN) {
                            if ($item->gender == Gender::GENDER_FEMALE) {
                                @$result_citizen[$item->_education_type][Gender::GENDER_FEMALE]['female'] += @$item->_students;
                            }
                            if ($item->_payment_form == PaymentForm::PAYMENT_FORM_BUDGET) {
                                @$result_citizen[$item->_education_type][PaymentForm::PAYMENT_FORM_BUDGET]['b'] += @$item->_students;
                            } elseif ($item->_payment_form == PaymentForm::PAYMENT_FORM_CONTRACT) {
                                @$result_citizen[$item->_education_type][PaymentForm::PAYMENT_FORM_CONTRACT]['c'] += @$item->_students;
                            }
                        }

                    }
                }

            }

        }

        return $this->renderView([
            'searchModel' => $searchModel,
            'result' => @$result,
            'result_work' => @$result_work,
            'result_inactive' => @$result_inactive,
            'result_student' => @$result_student,

            'faculty' => $faculty,
            'education_types' => @$education_types,
            'graduate_field_type_list' => @$graduate_field_type_list,
            'graduate_inactive_type_list_first' => @$graduate_inactive_type_list_first,
            'graduate_inactive_type_list_second' => @$graduate_inactive_type_list_second,
            'workplace_compatibility_list' => @$workplace_compatibility_list,
            'result_citizen' => @$result_citizen,
        ]);
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

    protected function findCurriculumModel($id)
    {
        if (($model = ECurriculum::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }


}
