<?php

namespace common\models\report;

use common\components\hemis\HemisApi;
use common\components\hemis\sync\ReportContractUpdater;
use common\models\finance\EStudentContract;
use common\models\finance\EStudentContractType;
use common\models\structure\EDepartment;
use DateTime;
use yii\db\Expression;

/**
 * Class ReportContract
 * @property integer $id
 * @property integer $total
 * @property integer $qty
 * @property integer $_department
 * @property integer $_education_year
 * @property integer $_education_form
 * @property integer $_education_type
 * @property integer $_course
 * @property DateTime $date
 * @property EDepartment $department
 */
class ReportContract extends BaseReport
{

    protected $name = 'Report: Contract';

    public static function tableName()
    {
        return 'r_contract';
    }

    public function getDescriptionForSync()
    {
        return $this->department->name . ' / ' . $this->date->format('Y-m-d');
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public static function runReport($syncOnlyFailed = true)
    {
        $sql = "
SELECT _department,
       _education_year,
       _education_form,
       _education_type,
       _level as _course,
       date,
       count(1) as qty,
       false as _sync
FROM        e_student_contract
WHERE       contract_status = :contract_status and _level IS NOT NULL and _manual_type = :manual_type
GROUP BY    _department,
            _education_year,
            _education_form,
            _education_type,
            _level,
            date
";
        if ($results = self::getDb()
            ->createCommand($sql, [
                'contract_status' => EStudentContractType::CONTRACT_REQUEST_STATUS_GENERATED,
                'manual_type' => EStudentContract::MANUAL_STATUS_TYPE_AUTO,
            ])
            ->query()
            ->readAll()) {
            $count = 0;
            foreach ($results as $row) {
                $updated = [
                    'qty' => $row['qty'],
                    '_sync' => new Expression('r_contract._sync AND r_contract.qty=' . $row['qty'])
                ];
                if ($r = self::getDb()->createCommand()->upsert(self::tableName(), $row, $updated)->execute()) {
                    $count++;
                }
            }

            if ($count) {
                HemisApi::getApiClient()->syncAllModelsToApi(self::class, $syncOnlyFailed);
            }

            return $count;
        }


        return false;
    }

    public function syncToApi($delete = false)
    {
        $result = false;

        if ($this->isSyncEnabled()) {
            $this->setAsShouldBeSynced();

            $result = ReportContractUpdater::updateModel($this, $delete);

            if ($this->_sync_status != self::STATUS_ERROR)
                $this->setAsSyncPerformed();
        }

        return $result;
    }
}