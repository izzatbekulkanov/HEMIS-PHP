<?php

namespace backend\widgets;

use backend\components\View;
use backend\widgets\checkbo\CheckBo;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\grid\Column;
use yii\helpers\Url;

class MailGridView extends GridView
{
    public $showHeader = false;
    public $summaryOptions = ['tag' => 'span', 'class' => 'badge'];
    public $pager = [
        'class' => SimpleNextPrevPager::class
    ];


    public function init()
    {
        $this->layout = file_get_contents(Yii::getAlias('@backend/views/message/my-messages-layout.php'));
        $this->rowOptions = function ($item) {
            return [
                'class' => !$item->opened && $item->type == 'inbox' ? 'not-read' : ''
            ];
        };

        return parent::init();
    }
}