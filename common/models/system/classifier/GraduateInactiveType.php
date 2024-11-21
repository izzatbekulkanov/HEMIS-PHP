<?php

namespace common\models\system\classifier;

class GraduateInactiveType extends _BaseClassifier
{
    const GRADUATE_INACTIVE_IN_MATERNITY_LEAVE = '11'; // 	Dekret ta’tilida
    const GRADUATE_INACTIVE_IN_CHILD_CARE_LEAVE = '12'; // 	Bola parvarishi ta’tilida
    const GRADUATE_INACTIVE_IN_CONSCRIPTION = '13'; // 	Muddatli harbiy xizmatda

    public static function tableName()
    {
        return 'h_graduate_inactive_type';
    }

    public static function getGraduateInactiveTypeOptions($first = false)
    {
        if(!$first){
            $items = self::find()
                ->where(['active' => true])
                ->andWhere(['in', 'code',
                    [
                        self::GRADUATE_INACTIVE_IN_MATERNITY_LEAVE,
                        self::GRADUATE_INACTIVE_IN_CHILD_CARE_LEAVE,
                        self::GRADUATE_INACTIVE_IN_CONSCRIPTION,
                    ]])
                ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
                ->all();
        }
        else{
            $items = self::find()
                ->where(['active' => true])
                ->andWhere(['not in', 'code',
                    [
                        self::GRADUATE_INACTIVE_IN_MATERNITY_LEAVE,
                        self::GRADUATE_INACTIVE_IN_CHILD_CARE_LEAVE,
                        self::GRADUATE_INACTIVE_IN_CONSCRIPTION,
                    ]])
                ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
                ->all();
        }
        return $items;
    }
}