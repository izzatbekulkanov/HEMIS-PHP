<?php

namespace frontend\models\system;

use common\models\archive\EAcademicInformation;
use common\models\archive\EAcademicInformationData;
use common\models\archive\EStudentAcademicSheetMeta;
use common\models\archive\EStudentDiploma;
use common\models\curriculum\EducationYear;
use common\models\curriculum\Semester;
use common\models\student\EGroup;
use common\models\system\Contact;
use frontend\models\academic\StudentDiploma;
use kartik\mpdf\Pdf;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class Student
 * @property $currentGroups
 * @property Contact contact
 * @package frontend\models\system
 */
class Student extends \common\models\student\EStudent implements \yii\web\IdentityInterface
{
    public $login;

    public static function findIdentity($id)
    {
        return self::findOne(['id' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return self::findOne(['access_token' => $token]);
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key == $authKey;
    }


    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public static function findByLogin($login)
    {
        return self::findOne([self::getLoginIdAttribute() => $login]);
    }

    public function canAccessToResource($url)
    {
        return true;
    }

    public function getContact()
    {
        return $this->hasOne(Contact::className(), ['_student' => 'id']);
    }

    public function getFullName()
    {
        return trim(ucfirst(strtolower($this->first_name)) . ' ' . ucfirst(strtolower($this->second_name)));
    }


    public function getLoginParam()
    {
        return $this->{self::getLoginIdAttribute()};
    }

    public function getCurrentGroups()
    {
        return $this->hasMany(EGroup::className(), ['id' => '_group'])
            ->viaTable('e_student_meta', ['_student' => 'id'])
            ->andFilterWhere([
                '_education_year' => EducationYear::getCurrentYear()->code,
            ]);
    }

    public function getSemesterOptions()
    {
        return ArrayHelper::map($this->getSemesters(), 'code', 'name');
    }

    /**
     * @return Semester[]
     */
    public function getSemesters()
    {
        return Semester::find()
            ->where([
                '_curriculum' => $this->meta->_curriculum,
                'active' => self::STATUS_ENABLE
            ])
            ->orderBy(['code' => SORT_ASC])
            ->indexBy('code')
            ->all();
    }

    public function getCurrentSemester()
    {
        return $this->meta->curriculum->lastSemester;
    }

    public function getDownloadableDocuments()
    {
        $documents = [];
        /**
         * @var $diploma EStudentDiploma
         */
        if ($diploma = EStudentDiploma::findOne([
            '_student' => $this->id,
            'active' => true,
            'accepted' => true,
        ])) {
            $documents[] = [
                'id' => $diploma->id,
                'name' => __('Diploma'),
                'attributes' => [
                    [
                        'label' => __('Diploma Number'),
                        'value' => $diploma->diploma_number
                    ],
                    [
                        'label' => __('Register Date'),
                        'value' => Yii::$app->formatter->asDate($diploma->register_date->getTimestamp())
                    ],
                    [
                        'label' => __('Register Number'),
                        'value' => $diploma->register_number
                    ]
                ],
                'callback' => function () use ($diploma) {
                    if (file_exists($diploma->getDiplomaFilePath())) {
                        return Yii::$app->response->sendFile($diploma->getDiplomaFilePath(), "diplom-{$diploma->student->student_id_number}.pdf");
                    }
                }
            ];
            $documents[] = [
                'id' => $diploma->id,
                'name' => __('Diploma Supplement'),
                'attributes' => [
                    [
                        'label' => __('Diploma Number'),
                        'value' => $diploma->diploma_number
                    ],
                    [
                        'label' => __('Specialty Code'),
                        'value' => $diploma->specialty_code
                    ],
                    [
                        'label' => __('Education Language'),
                        'value' => $diploma->education_language
                    ]
                ],
                'callback' => function () use ($diploma) {
                    if (file_exists($diploma->getSupplementFilePath())) {
                        return Yii::$app->response->sendFile($diploma->getSupplementFilePath(), "diplom-{$diploma->student->student_id_number}.pdf");
                    }
                }
            ];
        }

        $documents[] = [
            'id' => $this->meta->id,
            'name' => __('Archive Academic Sheet'),
            'attributes' => [
                [
                    'label' => __('Level'),
                    'value' => $this->meta->level->name
                ],
                [
                    'label' => __('Education Year'),
                    'value' => $this->meta->educationYear->name
                ],
                [
                    'label' => __('Semester'),
                    'value' => $this->meta->semester->name
                ]
            ],
            'callback' => function () {
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'tempDir' => Yii::getAlias('@runtime/mpdf'),
                ]);
                $mpdf->shrink_tables_to_fit = 1;
                $mpdf->keep_table_proportions = true;

                $mpdf->SetTitle(__('Archive Academic Sheet'));
                $mpdf->SetDisplayMode('fullwidth');
                $mpdf->WriteHTML(Yii::$app->view->renderFile('@backend/views/archive/academic-sheet-view.php', ['model' => EStudentAcademicSheetMeta::findOne($this->meta->id)]));

                return $mpdf->Output('talaba-varaqasi-' . $this->student_id_number . '.pdf', Destination::DOWNLOAD);
            }
        ];

        /**
         * @var $model EAcademicInformation
         */
        foreach (EAcademicInformation::find()->where(['_student' => $this->id])->all() as $model) {
            $documents[] = [
                'id' => $model->id,
                'name' => __('Archive Transcript'),
                'attributes' => [
                    [
                        'label' => __('Academic Number'),
                        'value' => $model->academic_number
                    ],
                    [
                        'label' => __('Register Number'),
                        'value' => $model->academic_register_number
                    ],
                    [
                        'label' => __('Register Date'),
                        'value' => Yii::$app->formatter->asDate($model->academic_register_date->getTimestamp())
                    ]
                ],
                'callback' => function () use ($model) {
                    $mpdf = new Mpdf([
                        'mode' => 'utf-8',
                        'format' => 'A4',
                        'tempDir' => Yii::getAlias('@runtime/mpdf'),
                    ]);
                    $mpdf->defaultCssFile = Yii::getAlias('@backend/assets/app/css/pdf-print.css');
                    $mpdf->shrink_tables_to_fit = 1;
                    $mpdf->keep_table_proportions = true;

                    $mpdf->SetDisplayMode('fullwidth');
                    $mpdf->WriteHTML(Yii::$app->view->renderFile('@backend/views/archive/transcript-pdf.php', ['model' => $model]));

                    return $mpdf->Output('transkript-' . $model->student->student_id_number . '.pdf', Destination::DOWNLOAD);
                }
            ];
        }

        /**
         * @var $model EAcademicInformationData
         */
        foreach (EAcademicInformationData::find()->where(['_student' => $this->id])->all() as $model) {
            $documents[] = [
                'id' => $model->id,
                'name' => __('Archive Academic Information Data'),
                'attributes' => [
                    [
                        'label' => __('Blank Number'),
                        'value' => $model->blank_number
                    ],
                    [
                        'label' => __('Register Number'),
                        'value' => $model->register_number
                    ],
                    [
                        'label' => __('Register Date'),
                        'value' => Yii::$app->formatter->asDate($model->register_date->getTimestamp())
                    ]
                ],
                'callback' => function () use ($model) {
                    $content2 = Yii::$app->view->renderFile('@backend/views/archive/academic-information-data-subjects-pdf.php', [
                        'model' => $model,
                        'marking_system' => $model->studentMeta->curriculum->_marking_system
                    ]);

                    $content = Yii::$app->view->renderFile('@backend/views/archive/academic-information-data-pdf.php', [
                        'model' => $model,
                        'content2' => $content2,
                    ]);
                    $pdf = new Pdf([
                        'mode' => Pdf::MODE_UTF8,
                        'format' => Pdf::FORMAT_A4,
                        'orientation' => Pdf::ORIENT_PORTRAIT,
                        'content' => $content,
                        'cssFile' => [
                            '@backend/assets/app/css/academic.css'
                        ],
                        'methods' => [
                            'SetHeader' => [],
                            'SetFooter' => [],
                        ]
                    ]);

                    return $pdf->Output($content, 'akademik-malumotnoma-' . $model->student->student_id_number . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
                }
            ];
        }

        return $documents;
    }
}