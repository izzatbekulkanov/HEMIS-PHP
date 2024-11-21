<?php

namespace common\components\hemis;

use common\components\Config;
use common\components\hemis\sync\DepartmentUpdater;
use common\components\hemis\sync\DiplomaBlankUpdater;
use common\components\hemis\sync\DissertationDefenseUpdater;
use common\components\hemis\sync\DoctorateStudentUpdater;
use common\components\hemis\sync\EmployeeMetaUpdater;
use common\components\hemis\sync\EmployeeUpdater;
use common\components\hemis\sync\ProjectExecutorUpdater;
use common\components\hemis\sync\ProjectMetaUpdater;
use common\components\hemis\sync\ProjectUpdater;
use common\components\hemis\sync\PublicationAuthorMetaUpdater;
use common\components\hemis\sync\PublicationMethodicalUpdater;
use common\components\hemis\sync\PublicationPropertyUpdater;
use common\components\hemis\sync\PublicationScientificUpdater;
use common\components\hemis\sync\ScientificPlatformProfileUpdater;
use common\components\hemis\sync\StudentDiplomaUpdater;
use common\components\hemis\sync\UniversityUpdater;
use common\models\archive\EDiplomaBlank;
use common\models\archive\EStudentDiploma;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\science\EDissertationDefense;
use common\models\science\EDoctorateStudent;
use common\models\science\EProject;
use common\models\science\EProjectExecutor;
use common\models\science\EProjectMeta;
use common\models\science\EPublicationAuthorMeta;
use common\models\science\EPublicationMethodical;
use common\models\science\EPublicationProperty;
use common\models\science\EPublicationScientific;
use common\models\science\EScientificPlatformProfile;
use common\models\structure\EDepartment;
use common\models\structure\EUniversity;
use common\models\student\EStudent;
use common\models\system\_BaseModel;
use common\models\system\SystemClassifier;
use DateTime;
use yii\data\ActiveDataProvider;

/**
 * Class HemisApiSyncModel
 * @property string _uid
 * @property string _sync
 * @property string _qid
 * @property string _sync_status
 * @property DateTime _sync_date
 * @property string[] _sync_diff
 * @package common\components\hemis
 */
abstract class HemisApiSyncModel extends _BaseModel
{
    public const STATUS_NOT_CHECKED = 'not_checked';
    public const STATUS_ACTUAL = 'actual';
    public const STATUS_ERROR = 'error';
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_DIFFERENT = 'different';


    public static function getSyncStatusOptions()
    {
        return [
            self::STATUS_NOT_CHECKED => __('Not Checked'),
            self::STATUS_ACTUAL => __('Actual'),
            self::STATUS_DIFFERENT => __('Different'),
            self::STATUS_ERROR => __('Sync Error'),
            self::STATUS_NOT_FOUND => __('Not Found'),
        ];
    }

    public function getSyncStatusLabel()
    {
        $options = self::getSyncStatusOptions();
        return isset($options[$this->_sync_status]) ? $options[$this->_sync_status] : $this->_sync_status;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            ['_sync_status', 'safe']
        ]);
    }

    public abstract function getDescriptionForSync();

    public function getIdForSync()
    {
        return $this->id;
    }

    public static function getModel($id)
    {
        return self::findOne(['id' => $id]);
    }

    /**
     * @param false $delete
     * @return HemisUniversity|HemisResponseDiploma|bool|int|HemisDepartment|HemisResponseEmployee|HemisResponseStudent|HemisResponseEmployeePosition
     * @throws HemisApiError
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function syncToApi($delete = false)
    {
        $result = false;

        if ($this->isSyncEnabled()) {
            $this->setAsShouldBeSynced();
            $this->refresh();

            if ($this instanceof SystemClassifier) {
                $result = HemisApi::getApiClient()->updateClassifier($this);
            } else if ($this instanceof EStudent) {
                $result = HemisApi::getApiClient()->updateStudent($this, $delete);
            } else if ($this instanceof EEmployee) {
                $result = EmployeeUpdater::updateModel($this, $delete);
            } else if ($this instanceof EDepartment) {
                $result = DepartmentUpdater::updateModel($this, $delete);
            } else if ($this instanceof EEmployeeMeta) {
                $result = EmployeeMetaUpdater::updateModel($this, $delete);
            } else if ($this instanceof EDoctorateStudent) {
                $result = DoctorateStudentUpdater::updateModel($this, $delete);
            } else if ($this instanceof EDissertationDefense) {
                $result = DissertationDefenseUpdater::updateModel($this, $delete);
            } else if ($this instanceof EProject) {
                $result = ProjectUpdater::updateModel($this, $delete);
            } else if ($this instanceof EProjectExecutor) {
                $result = ProjectExecutorUpdater::updateModel($this, $delete);
            } else if ($this instanceof EProjectMeta) {
                $result = ProjectMetaUpdater::updateModel($this, $delete);
            } else if ($this instanceof EPublicationMethodical) {
                $result = PublicationMethodicalUpdater::updateModel($this, $delete);
            } else if ($this instanceof EPublicationScientific) {
                $result = PublicationScientificUpdater::updateModel($this, $delete);
            } else if ($this instanceof EPublicationProperty) {
                $result = PublicationPropertyUpdater::updateModel($this, $delete);
            } else if ($this instanceof EScientificPlatformProfile) {
                $result = ScientificPlatformProfileUpdater::updateModel($this, $delete);
            } else if ($this instanceof EStudentDiploma) {
                /////////////////////
                $result = StudentDiplomaUpdater::updateModel($this, $delete);
            } else if ($this instanceof EDiplomaBlank) {
                $result = DiplomaBlankUpdater::updateModel($this, $delete);
            } else if ($this instanceof EUniversity) {
                $result = UniversityUpdater::updateModel($this);
            }

            $this->setAsSyncPerformed();
        }

        return $result;
    }


    public function checkToApi($updateIfDifferent = true)
    {
        $result = false;
        if ($this->isSyncEnabled()) {
            $this->setAsShouldBeSynced();

            if ($this instanceof EStudent) {
                $result = HemisApi::getApiClient()->checkStudentData($this, $updateIfDifferent);
            } else if ($this instanceof EEmployee) {
                $result = EmployeeUpdater::checkModel($this, $updateIfDifferent);
            } else if ($this instanceof EEmployeeMeta) {
                $result = EmployeeMetaUpdater::checkModel($this, $updateIfDifferent);
            } else if ($this instanceof EDoctorateStudent) {
                $result = DoctorateStudentUpdater::checkModel($this, $updateIfDifferent);
            } else if ($this instanceof EDissertationDefense) {
                $result = DissertationDefenseUpdater::checkModel($this, $updateIfDifferent);
            } else if ($this instanceof EProject) {
                $result = ProjectUpdater::checkModel($this, $updateIfDifferent);
            } else if ($this instanceof EProjectExecutor) {
                $result = ProjectExecutorUpdater::checkModel($this, $updateIfDifferent);
            } else if ($this instanceof EProjectMeta) {
                $result = ProjectMetaUpdater::checkModel($this, $updateIfDifferent);
            } else if ($this instanceof EPublicationMethodical) {
                $result = PublicationMethodicalUpdater::checkModel($this, $updateIfDifferent);
            } else if ($this instanceof EPublicationScientific) {
                $result = PublicationScientificUpdater::checkModel($this, $updateIfDifferent);
            } else if ($this instanceof EPublicationProperty) {
                $result = PublicationPropertyUpdater::checkModel($this, $updateIfDifferent);
            } else if ($this instanceof EScientificPlatformProfile) {
                $result = ScientificPlatformProfileUpdater::checkModel($this, $updateIfDifferent);
            } else if ($this instanceof EDepartment) {
                $result = DepartmentUpdater::checkModel($this, $updateIfDifferent);
            } else if ($this instanceof EStudentDiploma) {
                $result = StudentDiplomaUpdater::checkModel($this);
            } else if ($this instanceof EDiplomaBlank) {
                $result = DiplomaBlankUpdater::checkModel($this, $updateIfDifferent);
            }

            if ($this->_sync_status != self::STATUS_ERROR)
                $this->setAsSyncPerformed();
        }

        return $result;
    }

    public function setAsShouldBeSynced()
    {
        return $this->updateAttributes(['_sync' => false]);
    }

    public function setAsSyncPerformed()
    {
        return $this->updateAttributes(['_sync' => true, '_qid' => null]);
    }

    public function getSyncDiffAsLabel()
    {
        if (is_array($this->_sync_diff)) {
            return !empty($this->_sync_diff) ? json_encode($this->_sync_diff, JSON_PRETTY_PRINT) : '';
        }
        return $this->_sync_diff;
    }

    public function searchForSyncDetail($params)
    {
        $this->load($params);
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            foreach ($this->_searchableAttributes as $attribute) {
                $query->orWhereLike($attribute, $this->search);
            }
        }

        if ($this->_sync_status) {
            $query->andFilterWhere(['_sync_status' => $this->_sync_status]);
        }

        return $dataProvider;
    }

    public function isSyncEnabled()
    {
        return HEMIS_INTEGRATION && !boolval(Config::get('disable_sync_model_' . static::class));
    }
}