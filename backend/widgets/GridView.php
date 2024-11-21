<?php

namespace backend\widgets;

use backend\components\View;
use backend\widgets\checkbo\CheckBo;
use yii\db\ActiveRecord;

class GridView extends \yii\grid\GridView
{
    public $toggleAttribute = false;
    public $toggleHeader = false;
    public $toggleAll = false;
    public $sortable = false;
    public $mobile = false;
    public $sticky = false;
    public $sortLink = false;
    public $toggleLink = false;
    public $toggleDisable = false;
    public $togglePos = 'end';
    public $summaryOptions = ['tag' => 'span', 'class' => 'summary'];

    public $layout = "<div class='box-body no-padding'>{items}</div><div class='box-footer'>{summary}{pager}</div>";
    public $tableOptions = ['class' => 'table table-responsive table-striped table-hover '];

    public $currentGroup = null;

    public function init()
    {
        if ($this->mobile) {
            $this->tableOptions['class'] .= ' table-mobile';
        }

        if (!$this->toggleHeader) {
            $this->toggleHeader = __('Active');
        }
        if ($this->toggleAttribute || $this->toggleLink) {
            $this->tableOptions['class'] .= ' toggle_table';

            if ($this->toggleLink) {
                $link = $this->toggleLink;
            } else {
                $class = $this->dataProvider->query->modelClass;
                $link = linkTo(
                    ['dashboard/toggle', 'attribute' => $this->toggleAttribute, 'model' => $class::tableName()]
                );
            }
            $toggleDisable = $this->toggleDisable;

            $this->view->registerJs(
                "
             function toggleAllRows(element){
                var checked=$(element).is(':checked');
                $('.switch-row input').prop('checked',checked);
                var items=[];
                $('.switch-row input').each(function(){items.push($(this).data('id'))});
                $.post('$link&batch=1',{items:items,state:checked?1:0});
             }
            ",
                View::POS_END
            );

            $toggleCol =
                [
                    'attribute' => $this->toggleAttribute,
                    'format' => 'raw',
                    'header' => $this->toggleAll ? CheckBo::widget(
                        [
                            'type' => 'switch',
                            'options' => [
                                'onclick' => 'toggleAllRows(this)',
                                'disabled' => $toggleDisable,
                            ],
                            'name' => 'sd',
                            'value' => 0,
                        ]
                    ) : $this->toggleHeader,
                    'headerOptions' => ['width' => '70px'],
                    'value' => function ($data) use ($link, $toggleDisable) {
                        $attribute = $data->primaryKey()[0];
                        $value = $data->{$this->toggleAttribute};
                        if ($value === 'enable') {
                            $value = true;
                        } else {
                            if ($value === 'disable') {
                                $value = false;
                            }
                        }

                        if ($toggleDisable instanceof \Closure) {
                            $toggleDisable = $toggleDisable($data);
                        }

                        $id = $data->$attribute;

                        return CheckBo::widget(
                            [
                                'type' => 'switch',
                                'labelClass' => 'switch switch-xs switch-row',
                                'options' => [
                                    'onclick' => "$.get('$link',{'id':'$id'})",
                                    'disabled' => $toggleDisable,
                                    'data-id' => $data->$attribute,
                                ],
                                'name' => $data->$attribute,
                                'value' => $value,
                            ]
                        );
                    },
                ];

            if ($this->togglePos == 'end') {
                array_push($this->columns, $toggleCol);
            } else {
                array_unshift($this->columns, $toggleCol);
            }
        }

        if ($this->sortable) {
            array_unshift(
                $this->columns,
                [
                    'format' => 'raw',
                    'value' => function ($data) {
                        return '<span class="sort_handle"><i class="fa fa-bars" </span>';
                    },
                ]
            );
        }

        parent::init();
    }

    public function run()
    {
        if ($this->sticky) {
            $this->view->registerJs(
                "
             $('{$this->sticky}').theiaStickySidebar({
                    additionalMarginTop: 20,
                    additionalMarginBottom: 20
                });
            "
            );
        }

        if ($this->sortable) {
            $this->tableOptions['class'] .= ' sorted_table';

            if ($this->sortLink) {
                $link = $this->sortLink;
            } else {
                $class = $this->dataProvider->query->modelClass;
                $link = linkTo(['dashboard/sort', 'model' => $class::tableName()]);
            }

            $this->view->registerJs("sortTable('#" . $this->id . " tbody', '$link');");
        }

        parent::run();
    }

}