<?php

namespace common\models\attendance;

use common\models\employee\EEmployee;
use common\models\system\_BaseModel;
use common\models\system\Admin;
use DateInterval;
use DateTime;
use frontend\models\curriculum\StudentAttendance;
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
use Imagine\Image\ManipulatorInterface;

/**
 * This is the model class for table "e_attendance_activity".
 *
 * @property int $id
 * @property int $_attendance
 * @property int $_employee
 * @property bool|null $accepted
 * @property string|null $deadline
 * @property string|null $reason
 * @property int|null $status_for_activity
 * @property string|null $reworked_date
 * @property int|null $absent_on
 * @property int|null $absent_off
 * @property int|null $absent
 * @property bool|null $active
 * @property string[] $file
 * @property string $updated_at
 * @property string $created_at
 *
 * @property EAttendance $attendance
 * @property EEmployee $employee
 */
class EAttendanceActivity extends _BaseModel
{
    const STATUS_ENABLE = true;
    const STATUS_DISABLE = false;
    const SCENARIO_INSERT = 'register';
    const SCENARIO_CHANGE_DEAN = 'dean';
    const STATUS_FOR_CHANGE_CAUSE = 11;
    const STATUS_FOR_CHANGE_DELETE = 12;
    const STATUS_FOR_REWORK_DONE = 14;
    const STATUS_FOR_REWORK_UNDONE = 15;

    protected $_translatedAttributes = [];
    public $status;
    public $absent;
    public $selectedStudents;

    public static function tableName()
    {
        return 'e_attendance_activity';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ENABLE => __('Enable'),
            self::STATUS_DISABLE => __('Disable'),
        ];
    }

    public static function getChangeOptions()
    {
        return [
            self::STATUS_FOR_CHANGE_CAUSE => __('Change to Cause'),
            self::STATUS_FOR_CHANGE_DELETE => __('Correction'),
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            //[['_attendance', '_employee', 'updated_at', 'created_at'], 'required'],
            [['reason', 'status'], 'required', 'on' => self::SCENARIO_CHANGE_DEAN],
            [['_attendance', '_employee', 'status_for_activity', 'absent_on', 'absent_off'], 'default', 'value' => null],
            [['_attendance', '_employee', 'status_for_activity', 'absent_on', 'absent_off'], 'integer'],
            [['accepted', 'active', 'absent'], 'boolean'],
            [['file', 'selectedStudents'], 'safe'],
            [['deadline', 'reworked_date', 'updated_at', 'created_at', 'status'], 'safe'],
            [['reason'], 'string', 'max' => 255],
            [['_attendance'], 'exist', 'skipOnError' => true, 'targetClass' => EAttendance::className(), 'targetAttribute' => ['_attendance' => 'id']],
            //[['_employee'], 'exist', 'skipOnError' => true, 'targetClass' => EEmployee::className(), 'targetAttribute' => ['_employee' => 'id']],
        ]);
    }

    public function getAttendance()
    {
        return $this->hasOne(EAttendance::className(), ['id' => '_attendance']);
    }

    public function getEmployee()
    {
        return $this->hasOne(EEmployee::className(), ['id' => '_employee']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                //'defaultOrder' => ['lesson_date' => SORT_ASC],
                'attributes' => [
                    '_attendance',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->_attendance) {
            $query->andFilterWhere(['_attendance' => $this->_attendance]);
        }
        return $dataProvider;
    }

    public function searchForAttendance(EAttendance $attendance)
    {
        $query = self::find()
            ->andFilterWhere(['_attendance' => $attendance->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
        return $dataProvider;
    }

    public function beforeSave($insert)
    {
        if ($this->status == EAttendance::ATTENDANCE_ABSENT_ON) {
            $this->absent_off = 0;
            $this->absent_on = 2;
        } elseif ($this->status == EAttendance::ATTENDANCE_ABSENT_OFF) {
            $this->absent_off = 2;
            $this->absent_on = 0;
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($this->attendance) {
            $this->attendance->updateAttributes([
                'absent_off' => $this->absent_off,
                'absent_on' => $this->absent_on,
                'updated_at' => $this->getTimestampValueFormatted(),
            ]);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function processAttendanceActivity(Admin $admin)
    {
        $ids = array_filter(explode(',', $this->selectedStudents));

        /**
         * @var $item EAttendance
         */
        if (count($ids)) {
            $data = [];
            $time = $this->getTimestampValueFormatted();
            $items = EAttendance::find()
                ->with(['student'])
                ->where(['id' => $ids])
                ->all();

            $logItems = [];
            foreach ($items as $item) {
                if ($item->canChangeAttendance()) {
                    $logItems[] = $item->student->getFullName();

                    $data[] = [
                        '_attendance' => $item->id,
                        '_employee' => $admin->id,
                        'status_for_activity' => EAttendanceActivity::STATUS_FOR_CHANGE_CAUSE,
                        'reason' => strip_tags($this->reason),
                        'file' => json_encode($this->file),
                        'absent_on' => $this->status == EAttendance::ATTENDANCE_ABSENT_ON ? 2 : 0,
                        'absent_off' => $this->status == EAttendance::ATTENDANCE_ABSENT_OFF ? 2 : 0,
                        'created_at' => $time,
                        'updated_at' => $time,
                    ];
                }
            }

            $transaction = self::getDb()->beginTransaction();

            try {
                $count = Yii::$app->db
                    ->createCommand()
                    ->batchInsert(EAttendanceActivity::tableName(), array_keys($data[0]), $data)
                    ->execute();

                EAttendance::updateAll([
                    'absent_on' => $this->status == EAttendance::ATTENDANCE_ABSENT_ON ? 2 : 0,
                    'absent_off' => $this->status == EAttendance::ATTENDANCE_ABSENT_OFF ? 2 : 0,
                    'updated_at' => $time
                ], ['id' => $ids]);

                $transaction->commit();

                return $count;
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return false;
    }
}
