<?php


namespace backend\widgets;


use common\models\system\AdminResource;
use yii\widgets\Breadcrumbs;

class BreadCrumbsDefault extends Breadcrumbs
{
    public function init()
    {
        if (!empty($this->links)) {
            foreach ($this->links as &$link) {
                if (is_array($link) && isset($link['url'])) {
                    if (is_array($link['url']) && count($link['url']) == 1 || is_string($link['url'])) {
                        if ($r = AdminResource::getResourceByPath(is_array($link['url']) ? $link['url'][0] : $link['url'])) {
                            $link['label'] = $r->getNameLabel();
                        }
                    }
                } else {

                }
            }
        }
        parent::init();
    }


}