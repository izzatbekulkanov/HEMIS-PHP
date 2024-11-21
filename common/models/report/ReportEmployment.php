<?php

namespace common\models\report;

use common\components\hemis\HemisApi;
use common\components\hemis\sync\ReportEmploymentUpdater;
use common\models\structure\EDepartment;
use yii\db\Expression;

/**
 * Class ReportContract
 * @property integer $id
 * @property integer $qty
 * @property integer $_department
 * @property integer $_education_year
 * @property integer $_education_form
 * @property integer $_education_type
 * @property integer $_payment_form
 * @property integer $_gender
 * @property integer $_citizenship
 * @property integer $_graduate_fields_type
 * @property integer $_graduate_inactive_type
 * @property integer $workplace_compatibility
 * @property EDepartment $department
 */
class ReportEmployment extends BaseReport
{

    protected $name = 'Report: Employment';

    public static function tableName()
    {
        return 'r_employment';
    }

    public function getDescriptionForSync()
    {
        return $this->department->name . ' / ' . $this->qty;
    }

    public function getDepartment()
    {
        return $this->hasOne(EDepartment::className(), ['id' => '_department']);
    }

    public static function runReport($syncOnlyFailed = true)
    {
        if ($syncOnlyFailed == false) {
            self::deleteAll();
        }

        $count = 0;

        $sql = "
SELECT count(1)           as qty,
       _department,
       _education_year,
       _education_type,
       _education_form,
       _payment_form,
       _gender,
       workplace_compatibility,
       _graduate_fields_type,
       _graduate_inactive as _graduate_inactive_type,
       false              as _sync,
       true               as last
FROM e_student_employment se
WHERE se._department IS NOT NULL
GROUP BY _department,
         _education_year,
         _education_type,
         _education_form,
         _payment_form,
         _gender,
         workplace_compatibility,
         _graduate_fields_type,
         _graduate_inactive
";
        ReportEmployment::updateAll(['last' => false]);

        if ($results = self::getDb()
            ->createCommand($sql, [])
            ->query()
            ->readAll()) {

            foreach ($results as $row) {
                if (!$row['_graduate_inactive_type']) $row['_graduate_inactive_type'] = 0;
                if (!$row['_graduate_fields_type']) $row['_graduate_fields_type'] = 0;
                if (!$row['workplace_compatibility']) $row['workplace_compatibility'] = '11';

                $updated = [
                    'qty' => $row['qty'],
                    'last' => true,
                    '_sync' => new Expression('r_employment._sync AND r_employment.qty=' . $row['qty'])
                ];
                if ($r = self::getDb()->createCommand()->upsert(self::tableName(), $row, $updated)->execute()) {
                    $count++;
                }
            }
        }

        $sql = "
SELECT count(1)  as qty,
       _department,
       _education_year,
       _education_type,
       _education_form,
       _payment_form,
       s._gender as _gender,
       15        AS workplace_compatibility,
       11        as _graduate_fields_type,
       11        as _graduate_inactive_type,
       false     as _sync,
       true      as last
FROM e_student_meta sm
         LEFT JOIN e_student s on s.id = sm._student
WHERE sm._student_status = '14' AND s._citizenship = '12'
GROUP BY _department,
         _education_year,
         _education_type,
         _education_form,
         _payment_form,
         _gender,
         workplace_compatibility,
         _graduate_fields_type,
         _graduate_inactive_type";

        if ($results = self::getDb()
            ->createCommand($sql, [])
            ->query()
            ->readAll()) {

            foreach ($results as $row) {
                $updated = [
                    'qty' => $row['qty'],
                    'last' => true,
                    '_sync' => new Expression('r_employment._sync AND r_employment.qty=' . $row['qty'])
                ];
                if ($r = self::getDb()->createCommand()->upsert(self::tableName(), $row, $updated)->execute()) {
                    $count++;
                }
            }
        }


        $sql = "
SELECT count(1)  as qty,
       _department,
       _education_year,
       _education_type,
       _education_form,
       _payment_form,
       s._gender as _gender,
       16        AS workplace_compatibility,
       11        as _graduate_fields_type,
       11        as _graduate_inactive_type,
       false     as _sync,
       true      as last
FROM e_student_meta sm
         LEFT JOIN e_student s on s.id = sm._student
WHERE sm._student_status = '14'
GROUP BY _department,
         _education_year,
         _education_type,
         _education_form,
         _payment_form,
         _gender,
         workplace_compatibility,
         _graduate_fields_type,
         _graduate_inactive_type";

        if ($results = self::getDb()
            ->createCommand($sql, [])
            ->query()
            ->readAll()) {

            foreach ($results as $row) {
                $updated = [
                    'qty' => $row['qty'],
                    'last' => true,
                    '_sync' => new Expression('r_employment._sync AND r_employment.qty=' . $row['qty'])
                ];
                if ($r = self::getDb()->createCommand()->upsert(self::tableName(), $row, $updated)->execute()) {
                    $count++;
                }
            }
        }

        self::deleteAll(['last' => false]);

        if ($count) {
            HemisApi::getApiClient()->syncAllModelsToApi(self::class, $syncOnlyFailed);
        }

        return $count;
    }

    public function getUniqueId()
    {
        return md5(json_encode($this->getAttributes([
            '_department',
            '_education_year',
            '_education_type',
            '_education_form',
            '_payment_form',
            '_gender',
            '_graduate_fields_type',
            '_graduate_inactive_type',
            'workplace_compatibility',
        ])));
    }

    public function syncToApi($delete = false)
    {
        $result = false;

        if ($this->isSyncEnabled()) {
            $this->setAsShouldBeSynced();
            $result = ReportEmploymentUpdater::updateModel($this, $delete);

            if ($this->_sync_status != self::STATUS_ERROR)
                $this->setAsSyncPerformed();
        }

        return $result;
    }
}