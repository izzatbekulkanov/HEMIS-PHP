<?php

namespace common\models\curriculum;

use common\models\employee\EEmployee;
use common\models\student\EGroup;
use common\models\system\_BaseModel;
use common\models\system\classifier\Language;
use common\models\system\classifier\TrainingType;
use DateInterval;
use DateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Migration;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "e_subject_topic_question".
 *
 * @property int $id
 * @property int $_exam
 * @property int $_group
 * @property DateTime $start_at
 * @property DateTime $finish_at
 * @property DateTime $created_at
 *
 * @property Language $language
 * @property EExam $exam
 * @property EGroup $group
 */
class EExamGroup extends ActiveRecord
{
    public static function tableName()
    {
        return 'e_exam_group';
    }

    public function getExam()
    {
        return $this->hasOne(EExam::className(), ['id' => '_exam']);
    }

    public function getGroup()
    {
        return $this->hasOne(EGroup::className(), ['id' => '_group']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['position' => SORT_ASC],
                'attributes' => [
                    'name',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 400,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('name', $this->search);
        }

        return $dataProvider;
    }

    public function setStartAtDate($date)
    {
        if ($date) {
            if ($date = date_create_from_format('Y-m-d H:i', $date, new \DateTimeZone(Yii::$app->formatter->timeZone))) {
                $date->setTimezone(new \DateTimeZone('UTC'));
                $this->start_at = $date;

                return $this->save();
            }
        }

        return $this->updateAttributes(['start_at' => null]);
    }

    public function setFinishAtDate($date)
    {
        if ($date) {
            if ($date = date_create_from_format('Y-m-d H:i', $date, new \DateTimeZone(Yii::$app->formatter->timeZone))) {
                $date->setTimezone(new \DateTimeZone('UTC'));
                $this->finish_at = $date;

                return $this->save();
            }
        }

        return $this->updateAttributes(['finish_at' => null]);
    }

    public function getStartAtTime()
    {
        return $this->start_at ? $this->start_at : $this->exam->start_at;
    }

    public function getFinishAtTime()
    {
        return $this->finish_at ? $this->finish_at : $this->exam->finish_at;
    }
}
