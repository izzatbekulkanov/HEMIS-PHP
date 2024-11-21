<?php

namespace common\components\db;

use common\components\Config;
use common\models\system\_BaseModel;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class PgQuery extends ActiveQuery
{
    public function beforeDate(\DateTime $dateTime)
    {
        return $this->andWhere(['<', 'created_at', $dateTime->format('Y-m-d H:i:s')]);
    }

    public function afterDate(\DateTime $dateTime)
    {
        return $this->andWhere(['>', 'created_at', $dateTime->format('Y-m-d H:i:s')]);
    }

    public function betweenCurrentDate($startColumn, $endColumn)
    {
        return $this->andFilterWhere(new Expression("LOCALTIMESTAMP BETWEEN $startColumn and $endColumn"));
    }

    public function orWhereLikeTranslation($attribute, $search, $translation = '_translations')
    {
        $field = _BaseModel::getLanguageAttributeCode($attribute);
        return $this->orWhere(new Expression("lower($translation->>'$field') like lower(:search)", ['search' => '%' . $search . '%']));
    }

    public function orWhereLike($attribute, $search, $jsonField = false)
    {
        if ($jsonField) {
            return $this->orWhere(new Expression("lower($jsonField->>'$attribute') like lower(:search)", ['search' => '%' . $search . '%']));
        } else {
            return $this->orWhere(new Expression("lower($attribute) like lower(:search)", ['search' => '%' . $search . '%']));
        }
    }

    public function andWhereLike($attribute, $search, $jsonField = false)
    {
        if ($jsonField) {
            return $this->andWhere(new Expression("lower($jsonField->>'$attribute') like lower(:search)", ['search' => '%' . $search . '%']));
        } else {
            return $this->andWhere(new Expression("lower($attribute) like lower(:search)", ['search' => '%' . $search . '%']));
        }
    }

    public function orderByTranslationField($attribute, $order = 'ASC')
    {
        $field = _BaseModel::getLanguageAttributeCode($attribute);

        return $this->orderBy(new Expression("_translations->>'$field' $order, $attribute $order"));
    }
}