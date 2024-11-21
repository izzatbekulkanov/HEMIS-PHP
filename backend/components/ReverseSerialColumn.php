<?php


namespace backend\components;


use yii\grid\SerialColumn;

class ReverseSerialColumn extends SerialColumn
{
    protected function renderDataCellContent($model, $key, $index)
    {
        $pagination = $this->grid->dataProvider->getPagination();
        if ($pagination !== false) {
            return $pagination->totalCount - $index ;
        }

        return $index + 1;
    }
}