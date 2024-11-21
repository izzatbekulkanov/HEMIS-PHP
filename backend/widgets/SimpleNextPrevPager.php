<?php


namespace backend\widgets;


use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\LinkPager;

class SimpleNextPrevPager extends LinkPager
{
    public $hideOnSinglePage = true;

    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }

        $buttons = [];
        $currentPage = $this->pagination->getPage();

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = Html::a('<i class="fa fa-chevron-left"></i>', $this->pagination->createUrl($page), ['disabled' => $currentPage <= 0, 'class' => 'btn btn-default btn-sm']);
        }

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = Html::a('<i class="fa fa-chevron-right"></i>', $this->pagination->createUrl($page), ['disabled' => $currentPage >= $pageCount - 1, 'class' => 'btn btn-default btn-sm']);
        }

        return Html::tag("div", implode("\n", $buttons), ['class' => 'btn-group']);
    }

}