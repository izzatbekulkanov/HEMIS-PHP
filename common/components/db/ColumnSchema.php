<?php

namespace common\components\db;

use tigrov\pgsql\Schema;
use yii\db\ExpressionInterface;
use yii\db\JsonExpression;
use yii\db\PdoValue;

class ColumnSchema extends \tigrov\pgsql\ColumnSchema
{
    public function dbTypecastValue($value)
    {
        if ($value === null) {
            return null;
        }

        switch ($this->type) {
            case Schema::TYPE_BIT:
                return decbin($value);
            case Schema::TYPE_BINARY:
                return is_string($value) ? new PdoValue($value, \PDO::PARAM_LOB) : $value;
            case Schema::TYPE_TIMESTAMP:

            case Schema::TYPE_DATETIME:
                if ($value instanceof \DateTime) {
                    return $value->format('Y-m-d H:i:s');
                }
                return \Yii::$app->formatter->asDatetime($value, 'yyyy-MM-dd HH:mm:ss');
            case Schema::TYPE_DATE:
                if ($value instanceof \DateTime) {
                    return $value->format('Y-m-d');
                }
                return \Yii::$app->formatter->asDate($value, 'yyyy-MM-dd');
            case Schema::TYPE_TIME:
                if ($value instanceof \DateTime) {
                    return $value->format('H:i:s');
                }
                return \Yii::$app->formatter->asTime($value, 'HH:mm:ss');
            case Schema::TYPE_JSON:
                return new JsonExpression($value, $this->dbType);
            case Schema::TYPE_COMPOSITE:
                return $this->createCompositeExpression($value);
        }

        return $this->typecast($value);
    }
}