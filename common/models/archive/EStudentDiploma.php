<?php

namespace common\models\archive;

use common\components\Config;
use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiSyncModel;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\RatingGrade;
use common\models\curriculum\Semester;
use common\models\employee\EEmployeeMeta;
use common\models\structure\EDepartment;
use common\models\structure\EUniversity;
use common\models\student\EGroup;
use common\models\student\EQualification;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\student\EStudentMeta;
use common\models\system\_BaseModel;

use common\models\system\classifier\EducationType;
use common\models\system\classifier\EducationYear;
use common\models\system\classifier\University;
use DateInterval;
use DateTime;
use frontend\models\system\Student;
use phpDocumentor\Reflection\Types\This;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "e_student_diploma".
 *
 * @property string $_specialty_id
 * @property string $hash
 * @property int $_student
 * @property int $_department
 * @property string $student_id_number
 * @property string $student_name
 * @property string $department_name
 * @property string $diploma_number
 * @property string $register_number
 * @property string $specialty_name
 * @property DateTime $register_date
 * @property int|null $position
 * @property bool $active
 * @property bool $accepted
 * @property bool $published
 *
 * @property string $_university
 * @property string $university_name
 * @property string $_education_type
 * @property string $education_type_name
 * @property string $_education_form
 * @property string $education_form_name
 * @property string $specialty_code
 * @property int $_qualification
 * @property string $qualification_name
 * @property string $qualification_data
 * @property string $admission_information
 * @property string $given_hei_information
 * @property string $next_edu_information
 * @property string $professional_activity
 * @property string $_group
 * @property string $group_name
 * @property string $student_birthday
 * @property string $_education_year
 * @property int $diploma_category
 * @property DateTime $order_date
 * @property string $rector_fullname
 * @property string $given_city
 * @property string $post_address
 * @property string $education_language
 * @property string $education_period
 * @property string $last_education
 * @property string $marking_system
 * @property string $university_accreditation
 * @property string $diploma_link
 * @property string $suplement_link
 * @property int $diploma_status
 *
 * @property ESpecialty $specialty
 * @property EStudent $student
 * @property EDiplomaBlank $diplomaBlank
 */
class EStudentDiploma extends HemisApiSyncModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';

    protected $_translatedAttributes = [
        'specialty_name',
        'student_name',
        'university_name',
        'department_name',
        'education_type_name',
        'education_form_name',
        'qualification_name',
        'marking_system',
        'rector_fullname',
        'post_address',
        'last_education',
        'given_city',
        'education_language',
        'university_accreditation',
        'admission_information',
        'qualification_data',
        'given_hei_information',
        'next_edu_information',
        'professional_activity',
        'graduate_qualifying_work',
        'moved_hei',
        'additional_info',
    ];

    public static function tableName()
    {
        return 'e_student_diploma';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'diploma_number',
                        'diploma_category',
                        'register_number',
                        'register_date',
                        'order_date',
                        '_student',
                        'student_id_number',
                        '_department',
                        '_specialty_id',
                        '_university',
                        '_education_type',
                        '_education_form',
                        'specialty_code',
                        '_group',
                        'student_birthday',
                        '_education_year',
                        'education_period',
                        'marking_system',
                        'specialty_name',
                        'student_name',
                        'university_name',
                        'department_name',
                        'education_type_name',
                        'education_form_name',
                        'qualification_name',
                        'marking_system',
                        'rector_fullname',
                        'post_address',
                        'last_education',
                        'given_city',
                        'education_language',
                        'university_accreditation',
                        'admission_information',
                        'qualification_data',
                        'given_hei_information',
                        'next_edu_information',
                        'professional_activity',
                    ],
                    'required',
                    'on' => self::SCENARIO_INSERT
                ],
                //[$this->_translatedAttributes, 'required', 'on' => self::SCENARIO_INSERT],
                //[['_student', 'position'], 'default', 'value' => null],
                [['_student', 'position', '_department', '_specialty_id'], 'integer'],
                [['register_date'], 'safe'],
                [['active'], 'boolean'],
                [
                    ['graduate_qualifying_work'],
                    'required',
                    'when' => function (self $model) {
                        if (!$model->student) {
                            return false;
                        }
                        return $model->student->meta->getSubjects()
                            ->joinWith('curriculumSubject')
                            ->andWhere(['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_GRADUATE])
                            ->exists();
                    },
                    'whenClient' => 'function () { return $("estudentdiploma-graduate_qualifying_work").data("required"); }',
                ],
                //[['_specialty'], 'string', 'max' => 64],
                [['diploma_number', 'register_number'], 'unique'],
                [['specialty_name', 'student_name', 'department_name'], 'string', 'max' => 256],
                [['student_id_number', 'diploma_number'], 'string', 'max' => 20],
                [['register_number'], 'string', 'max' => 30],
                [['graduate_qualifying_work'], 'string', 'max' => 500],
                [['qualification_data'], 'string', 'max' => 1700],
                [['marking_system'], 'string', 'max' => 1500],
                [['university_accreditation'], 'string', 'max' => 255],
                [
                    [
                        'admission_information',
                        'given_hei_information',
                        'next_edu_information',
                        'professional_activity',
                        'moved_hei',
                        'additional_info'
                    ],
                    'string',
                    'max' => 1000
                ],
                [
                    ['_specialty_id'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => ESpecialty::className(),
                    'targetAttribute' => ['_specialty_id' => 'id']
                ],
                [
                    ['_education_year'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EducationYear::className(),
                    'targetAttribute' => ['_education_year' => 'code']
                ],
                [
                    ['_student'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => EStudent::className(),
                    'targetAttribute' => ['_student' => 'id']
                ],
                [
                    [
                        '_group',
                        '_specialty_id',
                        '_department',
                        'search',
                    ],
                    'safe'
                ],
            ]
        );
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'department_name' => __('Faculty'),
                'order_date' => __('DAK qarori sanasi'),
                'accepted' => __('Accept')
            ]
        );
    }

    public function getSpecialty()
    {
        return $this->hasOne(ESpecialty::className(), ['id' => '_specialty_id']);
    }

    public function getQualification()
    {
        return $this->hasOne(EQualification::className(), ['id' => '_qualification']);
    }

    public function getStudent()
    {
        return $this->hasOne(EStudent::className(), ['id' => '_student']);
    }

    public function getDiplomaBlank()
    {
        return $this->hasOne(EDiplomaBlank::class, ['number' => 'diploma_number']);
    }

    public function search($params)
    {
        $this->load($params);
        $query = self::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    //'defaultOrder' => ['lesson_date' => SORT_ASC],
                    'attributes' => [
                        '_student',
                        'diploma_number',
                        'register_number',
                        'register_date',
                        'position',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 400,
                ],
            ]
        );

        /*if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('name', $this->search);
        }*/
        return $dataProvider;
    }

    public function getDescriptionForSync()
    {
        $student = $this->student->getFullName();
        return $this->diploma_number . " ($student)";
    }

    public function canAccept()
    {
        return $this->diploma_number && $this->register_number && file_exists(
                $this->getDiplomaFilePath()
            ) && file_exists($this->getSupplementFilePath());
    }

    public function getCategoryLabel()
    {
        return EDiplomaBlank::getCategoryOptions()[$this->diploma_category] ?? $this->diploma_category;
    }

    public function getDiplomaFilePath()
    {
        return Yii::getAlias(
                '@root/private/diploma/'
            ) . $this->_education_year . '/' . $this->_department . '/' . "diploma-{$this->student_id_number}.pdf";
    }

    public function getSupplementFilePath()
    {
        return Yii::getAlias(
                '@root/private/diploma/'
            ) . $this->_education_year . '/' . $this->_department . '/' . "supplement-{$this->student_id_number}.pdf";
    }

    public function fillFromStudent(EStudentMeta $student)
    {
        $lang = Config::getLanguageCode() === Config::LANGUAGE_ENGLISH_CODE ? Config::LANGUAGE_ENGLISH : Config::LANGUAGE_UZBEK;
        $this->student_name = $student->student->fullName;
        $this->student_birthday = $student->student->birth_date;
        $this->specialty_name = $student->specialty->mainSpecialty->getTranslation('name', $lang);
        $this->specialty_code = $student->specialty->code;
        $this->department_name = $student->department->getTranslation('name', $lang);
        $this->university_name = EUniversity::findCurrentUniversity()->getTranslation('name', $lang);

        if ($meta = EEmployeeMeta::getRectorName()) {
            $rector = $meta->employee;
            if ($lang == Config::LANGUAGE_ENGLISH) {
                $this->rector_fullname = implode(".", [mb_substr($rector->first_name, 0, 1), $rector->second_name]);
            } else {
                $this->rector_fullname = implode(".", [mb_substr($rector->first_name, 0, 1), mb_substr($rector->third_name, 0, 1), $rector->second_name]);
            }
        }

        $this->university_accreditation = EUniversity::findCurrentUniversity()->getTranslation('accreditation_info', $lang);
        $this->post_address = EUniversity::findCurrentUniversity()->getTranslation('mailing_address', $lang);
        $this->given_city = EUniversity::findCurrentUniversity()->getTranslation('address', $lang);
        $this->education_form_name = $student->educationForm->getTranslation('name', $lang);
        $this->education_type_name = $student->educationType->getTranslation('name', $lang);
        $this->education_language = $student->group->educationLang->getTranslation('name', $lang);
        $this->education_period = $student->curriculum->education_period;
        $this->last_education = $student->student->getTranslation('other', $lang);
        $this->marking_system = $student->curriculum->markingSystem->getTranslation('description', $lang);
        $this->group_name = $student->group->name;
        $this->education_year_name = $student->educationYear->getTranslation('name', $lang);
        $this->qualification_name = $student->curriculum->qualification ? $student->curriculum->qualification->getTranslation('name', $lang) : '';
        $this->qualification_data = $student->curriculum->qualification ? $student->curriculum->qualification->getTranslation('description', $lang) : '';
        $this->graduate_qualifying_work = $student->student->graduateWork ? $student->student->graduateWork->getTranslation('work_name', $lang) : '';
        $certificateResult = $student->getCertificateCommitteeResult()
            ->joinWith('certificateCommittee')
            ->orderBy('e_certificate_committee.type')
            ->one();
        if ($certificateResult) {
            $this->order_date = $certificateResult->order_date;
        }
        if (Yii::$app->language === Config::LANGUAGE_ENGLISH) {
            $this->given_hei_information = self::getGivenHEIInformationEn();
            if ($student->educationType->code === EducationType::EDUCATION_TYPE_BACHELOR) {
                $this->admission_information = self::getAdmissionInformationEn();
                $this->next_edu_information = self::getNextEduInfoEn();
                $this->professional_activity = self::getProfessionalActivityEn();
            } else {
                $this->admission_information = self::getAdmissionInformationEn(EDiplomaBlank::TYPE_MASTER);
                $this->next_edu_information = self::getNextEduInfoEn(EDiplomaBlank::TYPE_MASTER);
                $this->professional_activity = self::getProfessionalActivityEn(EDiplomaBlank::TYPE_MASTER);
            }
        } else {
            $this->given_hei_information = self::getGivenHEIInformationUz();
            if ($student->educationType->code === EducationType::EDUCATION_TYPE_BACHELOR) {
                $this->admission_information = self::getAdmissionInformationUz();
                $this->next_edu_information = self::getNextEduInfoUz();
                $this->professional_activity = self::getProfessionalActivityUz();
            } else {
                $this->admission_information = self::getAdmissionInformationUz(EDiplomaBlank::TYPE_MASTER);
                $this->next_edu_information = self::getNextEduInfoUz(EDiplomaBlank::TYPE_MASTER);
                $this->professional_activity = self::getProfessionalActivityUz(EDiplomaBlank::TYPE_MASTER);
            }
        }
    }

    public function fillKeysFromStudent(EStudentMeta $student)
    {
        $this->_student = $student->_student;
        $this->_specialty_id = $student->_specialty_id;
        $this->_department = $student->_department;
        $this->student_id_number = $student->student->student_id_number;
        $this->_university = EUniversity::findCurrentUniversity()->id;
        $this->_education_form = $student->educationForm->code;
        $this->_education_type = $student->educationType->code;
        $this->_education_year = $student->educationYear->educationYear ? $student->educationYear->educationYear->code : $student->educationYear->code;
        $this->_qualification = $student->curriculum->_qualification;
        $this->_group = $student->_group;
    }

    public function fillTranslationsFromStudent(EStudentMeta $student, $lang = Config::LANGUAGE_UZBEK)
    {
        $this->setTranslation(
            'specialty_name',
            $student->specialty->mainSpecialty->getTranslation('name', $lang, true),
            $lang
        );
        $this->setTranslation('department_name', $student->department->getTranslation('name', $lang, true), $lang);
        $this->setTranslation(
            'marking_system',
            $student->curriculum->markingSystem->getTranslation('description', $lang, true),
            $lang
        );
        $this->setTranslation(
            'given_city',
            EUniversity::findCurrentUniversity()->getTranslation('address', $lang, true),
            $lang
        );
        if ($lang === Config::LANGUAGE_UZBEK) {
            $fullName = strtoupper(
                trim(
                    $student->student->getTranslation('second_name', $lang, true)
                    . ' ' . $student->student->getTranslation('first_name', $lang, true)
                    . ' ' . $student->student->getTranslation('third_name', $lang, true)
                )
            );
        } else {
            $fullName = strtoupper(
                trim(
                    $student->student->getTranslation('second_name', $lang, true)
                    . ' ' . $student->student->getTranslation('first_name', $lang, true)
                )
            );
        }
        $this->setTranslation('student_name', $fullName, $lang);
        $this->setTranslation(
            'university_name',
            EUniversity::findCurrentUniversity()->getTranslation('name', $lang, true),
            $lang
        );
        $this->setTranslation(
            'education_type_name',
            $student->educationType->getTranslation('name', $lang, true),
            $lang
        );
        $this->setTranslation(
            'education_form_name',
            $student->educationForm->getTranslation('name', $lang, true),
            $lang
        );
        if ($student->curriculum->qualification !== null) {
            $this->setTranslation(
                'qualification_name',
                $student->curriculum->qualification->getTranslation('name', $lang, true),
                $lang
            );
            $this->setTranslation(
                'qualification_data',
                $student->curriculum->qualification->getTranslation('description', $lang, true),
                $lang
            ) ?? '';
        }

        if ($meta = EEmployeeMeta::getRectorName()) {
            $rector = $meta->employee;
            if ($lang == Config::LANGUAGE_ENGLISH) {
                $rector_fullname = implode(".", [mb_substr($rector->getTranslation('first_name', $lang, true), 0, 1), $rector->getTranslation('second_name', $lang, true)]);
            } else {
                $rector_fullname = implode(".", [mb_substr($rector->getTranslation('first_name', $lang, true), 0, 1), mb_substr($rector->getTranslation('third_name', $lang, true), 0, 1), $rector->getTranslation('second_name', $lang, true)]);
            }
        }

        $this->setTranslation(
            'rector_fullname',
            $rector_fullname,
            $lang
        );
        $this->setTranslation(
            'post_address',
            EUniversity::findCurrentUniversity()->getTranslation('mailing_address', $lang, true),
            $lang
        );
        $this->setTranslation('last_education', $student->student->getTranslation('other', $lang, true), $lang);
        $this->setTranslation(
            'education_language',
            $student->group->educationLang->getTranslation('name', $lang, true),
            $lang
        );
        $this->setTranslation(
            'university_accreditation',
            EUniversity::findCurrentUniversity()->getTranslation('accreditation_info', $lang, true),
            $lang
        );
        /*$graduateSubject = $this->student->getAcademicRecords()
            ->joinWith('curriculumSubject')
            ->andWhere(['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_GRADUATE])
            ->one();*/
        $this->setTranslation(
            'graduate_qualifying_work',
            $student->student->graduateWork ? $student->student->graduateWork->getTranslation(
                'work_name',
                $lang,
                true
            ) : '',
            $lang
        );
        if ($lang === Config::LANGUAGE_ENGLISH) {
            $this->setTranslation('given_hei_information', self::getGivenHEIInformationEn(), $lang);
            if ($student->educationType->code === EducationType::EDUCATION_TYPE_BACHELOR) {
                $this->setTranslation('admission_information', self::getAdmissionInformationEn(), $lang);
                $this->setTranslation('next_edu_information', self::getNextEduInfoEn(), $lang);
                $this->setTranslation('professional_activity', self::getProfessionalActivityEn(), $lang);
            } else {
                $this->setTranslation(
                    'admission_information',
                    self::getAdmissionInformationEn(EDiplomaBlank::TYPE_MASTER),
                    $lang
                );
                $this->setTranslation(
                    'next_edu_information',
                    self::getNextEduInfoEn(EDiplomaBlank::TYPE_MASTER),
                    $lang
                );
                $this->setTranslation(
                    'professional_activity',
                    self::getProfessionalActivityEn(EDiplomaBlank::TYPE_MASTER),
                    $lang
                );
            }
        } else {
            $this->setTranslation('given_hei_information', self::getGivenHEIInformationUz(), $lang);
            if ($student->educationType->code === EducationType::EDUCATION_TYPE_BACHELOR) {
                $this->setTranslation('admission_information', self::getAdmissionInformationUz(), $lang);
                $this->setTranslation('next_edu_information', self::getNextEduInfoUz(), $lang);
                $this->setTranslation('professional_activity', self::getProfessionalActivityUz(), $lang);
            } else {
                $this->setTranslation(
                    'admission_information',
                    self::getAdmissionInformationUz(EDiplomaBlank::TYPE_MASTER),
                    $lang
                );
                $this->setTranslation(
                    'next_edu_information',
                    self::getNextEduInfoUz(EDiplomaBlank::TYPE_MASTER),
                    $lang
                );
                $this->setTranslation(
                    'professional_activity',
                    self::getProfessionalActivityUz(EDiplomaBlank::TYPE_MASTER),
                    $lang
                );
            }
        }
    }

    public function beforeSave($insert)
    {
        $result = parent::beforeSave($insert);

        if (!in_array(Config::getLanguageCode(), [Config::LANGUAGE_UZBEK_CODE, Config::LANGUAGE_ENGLISH_CODE], true)) {
            foreach ($this->_translatedAttributes as $translatedAttribute) {
                $this->setTranslation($translatedAttribute, $this->$translatedAttribute, Config::LANGUAGE_UZBEK);
            }
        }
        //$this->validateTranslations();

        if ($this->hasErrors()) {
            return false;
        }

        if ($this->hash == null) {
            $this->hash = gen_uuid();
        }

        return $result;
    }

    public function generateDiplomaLinks()
    {
        if ($this->hash !== null) {
            $this->updateAttributes(
                [
                    'diploma_link' => Yii::getAlias('@frontendUrl') . '/api/info/diploma?param=' . $this->hash,
                    'suplement_link' => Yii::getAlias(
                            '@frontendUrl'
                        ) . '/api/info/diploma-supplement?param=' . $this->hash
                ]
            );
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (empty($this->diploma_link)) {
            $this->generateDiplomaLinks();
        }
        if ($insert || isset($changedAttributes['diploma_number'])) {
            $blank = EDiplomaBlank::findOne(['number' => $this->diploma_number]);
            if ($blank !== null) {
                $blank->scenario = EDiplomaBlank::SCENARIO_INSERT;
                $blank->status = EDiplomaBlank::STATUS_ORDERED;
                $blank->save();
            }
        }
        if (file_exists($this->getDiplomaFilePath())) {
            unlink($this->getDiplomaFilePath());
        }

        if (file_exists($this->getSupplementFilePath())) {
            unlink($this->getSupplementFilePath());
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        $blank = EDiplomaBlank::findOne(['number' => $this->diploma_number]);
        if ($blank !== null) {
            $blank->scenario = EDiplomaBlank::SCENARIO_INSERT;
            $blank->status = EDiplomaBlank::STATUS_EMPTY;
            $blank->save();
        }
        parent::afterDelete();
    }

    public function validateTranslations()
    {
        $this->validate();
        if ($this->hasAttribute('_translations')) {
            $translations = $this->_translations ?: [];
            foreach ([Config::LANGUAGE_UZBEK, Config::LANGUAGE_ENGLISH] as $lang) {
                $langCode = Config::getShortLanguageCodes()[$lang];
                foreach ($this->_translatedAttributes as $attribute) {
                    if (!$this->isAttributeRequired($attribute)) {
                        continue;
                    } elseif ($attribute === 'graduate_qualifying_work') {
                        $graduateSubject = $this->student->meta->getSubjects()
                            ->joinWith('curriculumSubject')
                            ->andWhere(['e_curriculum_subject._rating_grade' => RatingGrade::RATING_GRADE_GRADUATE])
                            ->exists();
                        if (!$graduateSubject) {
                            continue;
                        }
                    }
                    if (!isset($translations[$attribute . '_' . $langCode]) || empty($translations[$attribute . '_' . $langCode])) {
                        $this->addError(
                            $attribute . '_' . $langCode,
                            __(
                                '{lang} translation for `{attribute}` can not be blank.',
                                [
                                    'lang' => Config::getLanguageLabel($lang),
                                    'attribute' => $this->getAttributeLabel($attribute)
                                ]
                            )
                        );
                    }
                }
            }
        }

        return !$this->hasErrors();
    }

    public function validateSubjectsTranslations()
    {
        /** @var ECurriculumSubject $studentSubject */
        foreach (EStudentMeta::getStudentSubjects($this->student->meta)->all() as $studentSubject) {
            $record = $studentSubject->getStudentSubjectRecord($this->_student);
            if (!$record) {
                $this->addError(
                    'subject_record',
                    __(
                        'Academic record for \'{subject}\' subject does not exist.',
                        ['subject' => $studentSubject->subject->name]
                    )
                );
            }
            if ($studentSubject->subject->hasAttribute('_translations')) {
                $translations = $studentSubject->subject->_translations ?: [];
                foreach ([Config::LANGUAGE_UZBEK, Config::LANGUAGE_ENGLISH] as $lang) {
                    $langCode = Config::getShortLanguageCodes()[$lang];
                    if (!isset($translations['name_' . $langCode]) || empty($translations['name_' . $langCode])) {
                        $this->addError(
                            'subject_name_' . $langCode,
                            __(
                                '{lang} translation for subject name `{subject}` can not be blank.',
                                [
                                    'lang' => Config::getLanguageLabel($lang),
                                    'subject' => $studentSubject->subject->name,
                                ]
                            )
                        );
                    }
                }
            }
        }
        return !$this->hasErrors();
    }

    public static function getAdmissionInformationUz($type = EDiplomaBlank::TYPE_BACHELOR)
    {
        if ($type === EDiplomaBlank::TYPE_BACHELOR) {
            return 'Umumiy o‘rta (10-11-sinflar negizida), o‘rta maxsus, kasb-hunar ma’lumoti to‘g‘risidagi tegishli hujjatga ega bo‘lgan va kirish imtihonlari (test sinovlari)dan muvaffaqiyatli o‘tish';
        }
        return 'Oliy ma’lumot to‘g‘risidagi tegishli hujjatga ega bo‘lgan va kirish imtihonlari (test sinovlari)dan muvaffaqiyatli o‘tish';
    }

    public static function getAdmissionInformationEn($type = EDiplomaBlank::TYPE_BACHELOR)
    {
        if ($type === EDiplomaBlank::TYPE_BACHELOR) {
            return 'Having the documents about general secondary (based on grades 10-11), secondary special and vocational education, and successful passing entrance examinations (tests)';
        }
        return 'Having the documents about the higher education, and successful passing entrance examinations (tests)';
    }

    public static function getGivenHEIInformationUz()
    {
        return 'Respublika miqyosidagi davlat Oliy ta’lim muassasasi';
    }

    public static function getGivenHEIInformationEn()
    {
        return 'Republican State Higher Education Institution';
    }

    public static function getNextEduInfoUz($type = EDiplomaBlank::TYPE_BACHELOR)
    {
        if ($type === EDiplomaBlank::TYPE_BACHELOR) {
            return 'Mazkur ta’lim yo‘nalishi bo‘yicha berilgan diplom tegishli va turdosh mutaxassisliklar bo‘yicha magistraturada o‘qishni davom ettirish imkoniyatini beradi.';
        }
        return 'Mazkur mutaxassislik  bo‘yicha berilgan diplom doktorantura mutaxassisliklari bo‘yicha o‘qishni davom ettirish imkonini beradi.';
    }

    public static function getNextEduInfoEn($type = EDiplomaBlank::TYPE_BACHELOR)
    {
        if ($type === EDiplomaBlank::TYPE_BACHELOR) {
            return 'A diploma given in this direction of education allows you to continue your studies in the master\'s degree for the relevant or related specialties';
        }
        return 'A diploma given in this specialty allows you to continue your studies in doctoral specialties.';
    }

    public static function getProfessionalActivityUz($type = EDiplomaBlank::TYPE_BACHELOR)
    {
        if ($type === EDiplomaBlank::TYPE_BACHELOR) {
            return 'Berilgan mutaxassislik ta’lim darajasi va malakaga muvofiq kasbiy faoliyat yuritish (ishga joylashish) huquqini beradi.';
        }
        return 'Berilgan mutaxassislik ta’lim darajasi va malakaga muvofiq kasbiy faoliyat yuritish (ishga joylashish) huquqini beradi.';
    }

    public static function getProfessionalActivityEn($type = EDiplomaBlank::TYPE_BACHELOR)
    {
        if ($type === EDiplomaBlank::TYPE_BACHELOR) {
            return 'Gives the right to carry out professional activity (employment) according to the level of education and qualification by this specialty';
        }
        return 'Gives the right to carry out professional activity (employment) according to the level of education and qualification by this specialty';
    }

    public function searchDiplomaList($params, $asProvider = true)
    {
        $this->load($params);

        $query = self::find()
            ->with(['student']);

        if ($this->search) {
            $query->orWhereLike('student_name', $this->search);
            $query->orWhereLike('specialty_name', $this->search);
            $query->orWhereLike('qualification_name', $this->search);
            $query->orWhereLike('specialty_code', $this->search);
            $query->orWhereLike('diploma_number', $this->search);
            $query->orWhereLike('register_number', $this->search);
        }

        if ($this->_department) {
            $query->andFilterWhere(['_department' => $this->_department]);
        }
        if ($this->_group) {
            $query->andFilterWhere(['_group' => $this->_group]);
        }
        if ($this->_specialty_id) {
            $query->andFilterWhere(['_specialty_id' => $this->_specialty_id]);
        }

        $query->andFilterWhere(['accepted' => true]);
        if ($asProvider) {
            $dataProvider = new ActiveDataProvider(
                [
                    'query' => $query,
                    'sort' => [
                        'defaultOrder' => ['register_number' => SORT_ASC],
                    ],
                    'pagination' => [
                        'pageSize' => 200,
                    ],
                ]
            );

            return $dataProvider;
        }
        return $query;
    }

    public function getDepartmentItems()
    {
        return ArrayHelper::map(
            EDepartment::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(
                    [
                        'active' => true,
                        'id' => self::find()
                            ->select(['_department'])
                            ->distinct()
                            ->column()
                    ]
                )
                ->all(),
            'id',
            'name'
        );
    }

    public function getSpecialtyItems()
    {
        return ArrayHelper::map(
            ESpecialty::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(
                    [
                        'active' => true,
                        'id' => self::find()
                            ->select(['_specialty_id'])
                            ->distinct()
                            ->column()
                    ]
                )
                ->all(),
            'id',
            'name'
        );
    }

    public function getGroupItems()
    {
        return ArrayHelper::map(
            EGroup::find()
                ->orderBy(['name' => SORT_ASC])
                ->where(
                    [
                        'active' => true,
                        'id' => self::find()
                            ->select(['_group'])
                            ->distinct()
                            ->column()
                    ]
                )
                ->all(),
            'id',
            'name'
        );
    }

    public static function generateDiplomaListFile($query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Diploma list'));

        $row = 1;
        $col = 1;

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Register number'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Diploma number'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Student'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Passport'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Specialty'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow(
            $col++,
            $row,
            __('Certificate committee info'),
            DataType::TYPE_STRING
        );
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Diploma category'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Register date'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Group'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Payment Form'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Type'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Form'), DataType::TYPE_STRING);

        foreach ($query->orderBy('register_number')->all() as $diploma) {
            $col = 1;
            $row++;
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                htmlspecialchars($diploma->register_number),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                htmlspecialchars($diploma->diploma_number),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                htmlspecialchars($diploma->student_name),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                htmlspecialchars($diploma->student->passport_number),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                htmlspecialchars($diploma->specialty_name),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                Yii::$app->formatter->asDate($diploma->order_date, 'php:j F Y'),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $diploma->getCategoryLabel(),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                Yii::$app->formatter->asDate(
                    $diploma->register_date->getTimestamp(),
                    'php:j F Y'
                ),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                htmlspecialchars($diploma->group_name),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                htmlspecialchars($diploma->student->meta->paymentForm->name),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                htmlspecialchars($diploma->education_type_name),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                htmlspecialchars($diploma->education_form_name),
                DataType::TYPE_STRING
            );
        }

        $name = 'Diplomlar-' . Yii::$app->formatter->asDatetime(
                time(),
                'php:d_m_Y_h_i_s'
            ) . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }

    public static function normalizeStringLines($strings, $length, $lines)
    {
        $i = 0;
        $firstLine = '';
        foreach ($strings as $string) {
            if (strlen($firstLine .= ' ' . $string) < $length + 1) {
                $i++;
            } else {
                break;
            }
        }
        $result = [];
        $result[] = implode(' ', array_slice($strings, 0, $i));
        if ($lines >= 3) {
            $result[] = implode(' ', array_slice($strings, $i, $i));
            $result[] = implode(' ', array_slice($strings, $i * 2));
        } else {
            $result[] = implode(' ', array_slice($strings, $i));
        }
        return $result;
    }
}
