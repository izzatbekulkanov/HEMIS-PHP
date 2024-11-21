<?php


namespace common\models\system;


use yii\db\ActiveRecord;
use yii\db\Expression;

class Counter extends ActiveRecord
{

    public static function getNextSequence($identifier, $default = 1)
    {
        $sql = "INSERT INTO e_counter
VALUES (:identifier, :value)
ON CONFLICT ON CONSTRAINT e_counter_identifier_key
DO UPDATE SET value = e_counter.value+1
RETURNING *";

        if ($row = self::getDb()->createCommand($sql, [
            'identifier' => $identifier,
            'value' => $default
        ])->query()->read()) {
            return $row['value'];
        }

        return $default;
    }
}