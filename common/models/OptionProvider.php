<?php

namespace common\models;

use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use common\models\student\EStudentMeta;
use common\models\system\classifier\StructureType;

class OptionProvider
{
    public static function getCurrentYearGroupOptions()
    {
        /**
         * @var $department EDepartment
         * @var $group EGroup
         */
        $year = EducationYear::getCurrentYear();
        $groups = EGroup::find()
            ->with(['department'])
            ->where(['active' => true])
            ->orderBy(['_department' => SORT_ASC, '_specialty_id' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($groups as $group) {
            if (!isset($result[$group->department->name])) {
                $result[$group->department->name] = [];
            }
            $result[$group->department->name][$group->id] = $group->name;
        }

        return $result;
    }

    public static function getDepartmentOptions()
    {
        $result = [];
        foreach (StructureType::getClassifierOptions() as $type => $label) {
            $department = EDepartment::find()
                ->where(['active' => EDepartment::STATUS_ENABLE, '_structure_type' => $type])
                ->orderBy(['position' => SORT_ASC]);

            $department = $department->all();

            if (count($department)) {
                $result[$label] = [];
                foreach ($department as $item) {
                    $result[$label][$item->id] = $item->name;
                }
            }
        }

        return $result;
    }
}