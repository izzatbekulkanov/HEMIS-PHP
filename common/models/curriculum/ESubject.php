<?php
namespace common\models\curriculum;
use common\components\Config;
use common\models\system\_BaseModel;
use common\models\system\classifier\EducationType;
use common\models\curriculum\SubjectGroup;
use common\models\structure\EDepartment;
use common\models\student\ESpecialty;
use DateInterval;
use DateTime;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
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

/**
 * This is the model class for table "e_subject".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $_subject_group
 * @property string $_education_type
 * @property int|null $position
 * @property bool|null $active
 * @property string|null $_translations
 * @property string $updated_at
 * @property string $created_at
 *
 * @property HEducationType $educationType
 * @property HSubjectGroup $subjectGroup
 */
class ESubject extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
	const SCENARIO_CREATE = 'create';
	
	protected $_translatedAttributes = ['name'];
	
    public static function tableName()
    {
        return 'e_subject';
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
         return array_merge(parent::rules(), [
            [['name', '_subject_group', '_education_type', 'code'], 'required', 'on' => self::SCENARIO_CREATE],
            [['position'], 'default', 'value' => null],
            [['position'], 'integer'],
            [['active'], 'boolean'],
            [['_translations', 'updated_at', 'created_at'], 'safe'],
            [['code', '_subject_group', '_education_type'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 256],
            [['code'], 'unique'],
            [['_education_type'], 'exist', 'skipOnError' => true, 'targetClass' => EducationType::className(), 'targetAttribute' => ['_education_type' => 'code']],
            [['_subject_group'], 'exist', 'skipOnError' => true, 'targetClass' => SubjectGroup::className(), 'targetAttribute' => ['_subject_group' => 'code']],
        ]);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::className(), ['code' => '_education_type']);
    }

    public function getSubjectGroup()
    {
        return $this->hasOne(SubjectGroup::className(), ['code' => '_subject_group']);
    }
	
	public function search($params, $asProvider = true)
    {
        $this->load($params);

        $query = self::find();
        $defaultOrder = ['name' => SORT_ASC];

        if ($this->search) {
            $query->orWhereLike('name_uz', $this->search, '_translations');
            $query->orWhereLike('name_oz', $this->search, '_translations');
            $query->orWhereLike('name_ru', $this->search, '_translations');
            $query->orWhereLike('name', $this->search);
        }
        if ($this->_subject_group) {
            $query->andFilterWhere(['_subject_group' => $this->_subject_group]);
        }
        if ($this->_education_type) {
            $query->andFilterWhere(['_education_type' => $this->_education_type]);
        }

        if ($asProvider) {
            return new ActiveDataProvider(
                [
                    'query' => $query,
                    'sort' => [
                        'defaultOrder' => $defaultOrder,
                        'attributes' => [
                            'name',
                            'id',
                            'position',
                            '_subject_group',
                            '_education_type',
                            'updated_at',
                            'created_at',
                        ],
                    ],
                    'pagination' => [
                        'pageSize' => 400,
                    ],
                ]
            );
        }
        else {
            $query->addOrderBy($defaultOrder);
        }
        return $query;
    }

    public static function generateDownloadFile($query)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('Subject List'));

        $row = 1;
        $col = 1;

        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Education Type'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Name'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Name (En)'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('Code'), DataType::TYPE_STRING);

        foreach ($query->all() as $i => $model) {
            $col = 1;
            $row++;

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->educationType ? $model->educationType->name : '',
                DataType::TYPE_STRING
            );

            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->getTranslation('name', Config::LANGUAGE_UZBEK),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->getTranslation('name', Config::LANGUAGE_ENGLISH),
                DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicitByColumnAndRow(
                $col++,
                $row,
                $model->code,
                DataType::TYPE_STRING
            );
        }

        $name = 'Fanlar bazasi-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $dir = Yii::getAlias('@backend/runtime/export/');
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir, 0777);
        }
        $fileName = $dir . $name;
        $writer->save($fileName);

        return $fileName;
    }
    public function getFullName()
    {
        return trim($this->name . ' [' . $this->code . ']');
    }


}
