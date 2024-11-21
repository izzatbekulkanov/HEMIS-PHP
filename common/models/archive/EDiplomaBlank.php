<?php

namespace common\models\archive;

use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiSyncModel;
use common\models\student\ESpecialty;
use common\models\student\EStudent;
use common\models\system\_BaseModel;

use DateInterval;
use DateTime;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "e_diploma_blank".
 *
 * @property string $type
 * @property string $category
 * @property int $number
 * @property string $status
 * @property string $reason
 *
 * @property ESpecialty $specialty
 * @property EStudentDiploma $diploma
 * @property EStudent $student
 */
class EDiplomaBlank extends HemisApiSyncModel
{
    const STATUS_EMPTY = '10';
    const STATUS_ORDERED = '11';
    const STATUS_CANCELLED = '12';

    const TYPE_BACHELOR = '11';
    const TYPE_MASTER = '12';

    const CATEGORY_REGULAR = '11';
    const CATEGORY_PRIVILEGED = '12';

    const SCENARIO_INSERT = 'insert';
    protected $_translatedAttributes = [];

    public static function tableName()
    {
        return '{{%e_diploma_blank}}';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_EMPTY => __('Empty'),
            self::STATUS_ORDERED => __('Ordered'),
            self::STATUS_CANCELLED => __('Cancelled'),
        ];
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_BACHELOR => __('Bachelor'),
            self::TYPE_MASTER => __('Master'),
        ];
    }

    public static function getCategoryOptions()
    {
        return [
            self::CATEGORY_REGULAR => __('Regular'),
            self::CATEGORY_PRIVILEGED => __('Privileged'),
        ];
    }

    public function getStatusLabel()
    {
        return self::getStatusOptions()[$this->status] ?? $this->status;
    }

    public function getTypeLabel()
    {
        return self::getTypeOptions()[$this->type] ?? $this->type;
    }

    public function getCategoryLabel()
    {
        return self::getCategoryOptions()[$this->category] ?? $this->category;
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'number' => __('Diploma Number')
            ]
        );
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['number', 'type', 'category', 'status'], 'required', 'on' => [self::SCENARIO_INSERT]],
                [['number'], 'unique'],
                [['status', 'reason'], 'required', 'on' => self::SCENARIO_UPDATE],
                [['number', 'type', 'category', 'reason'], 'string'],
                [['status'], 'integer'],
            ]
        );
    }

    public function search($params)
    {
        $this->load($params);
        $query = self::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => ['number' => SORT_ASC],
                    'attributes' => [
                        'type',
                        'category',
                        'status',
                        'number',
                        'updated_at',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'pageSize' => 100,
                ],
            ]
        );

        if ($this->type) {
            $query->andWhere(['type' => $this->type]);
        }
        if ($this->category) {
            $query->andWhere(['category' => $this->category]);
        }
        if ($this->status) {
            $query->andWhere(['status' => $this->status]);
        }
        if ($this->number) {
            $query->andWhereLike('number', $this->number);
        }
        return $dataProvider;
    }

    public static function getSelectOptions($type = self::TYPE_BACHELOR, $category = self::CATEGORY_REGULAR)
    {
        return ArrayHelper::map(
            self::find()->orderBy('number')->where(['status' => self::STATUS_EMPTY, 'type' => $type, 'category' => $category])->all(),
            'number',
            'number'
        );
    }

    public static function importDataFromApi($data)
    {
        $count = 0;
        foreach ($data as $item) {
            if ($model = EDiplomaBlank::findOne(['_uid' => $item['id']])) {
                $options = $model->_options;
                if (isset($options['version']) && $options['version'] !== $item['version']) {
                    $count += $model->updateAttributes(
                        [
                            'year' => $item['blankYear']['code'],
                            'status' => $item['blankStatus']['code'],
                            'category' => $item['blankCategory']['code'],
                            'type' => $item['educationType']['code'],
                            'number' => $item['blankSeria'] . $item['blankNumber'],
                            'reason' => $item['cancelReason'] ?? $model->reason,
                            '_sync' => true,
                            '_sync_diff' => [],
                            '_sync_status' => 'actual',
                            '_sync_date' => new DateTime(),
                            '_options' => ['version' => $item['version'], 'university' => $item['university']['code']]
                        ]
                    );
                }
            } else {
                $model = new EDiplomaBlank(
                    [
                        'scenario' => EDiplomaBlank::SCENARIO_INSERT,
                        '_uid' => $item['id'],
                        'year' => $item['blankYear']['code'],
                        'status' => $item['blankStatus']['code'],
                        'category' => $item['blankCategory']['code'],
                        'type' => $item['educationType']['code'],
                        'number' => $item['blankSeria'] . $item['blankNumber'],
                        'reason' => $item['cancelReason'] ?? null,
                        '_sync' => true,
                        '_sync_diff' => [],
                        '_sync_status' => EDiplomaBlank::STATUS_ACTUAL,
                        '_sync_date' => new DateTime(),
                        '_options' => ['version' => $item['version'], 'university' => $item['university']['code']]
                    ]
                );
                if ($model->save()) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public function getDiploma()
    {
        return $this->hasOne(EStudentDiploma::class, ['diploma_number' => 'number']);
    }

    public function getIdForSync()
    {
        return $this->number;
    }

    public static function getModel($id)
    {
        return self::findOne(['number' => $id]);
    }

    public function getDescriptionForSync()
    {
        return $this->number;
    }
}
