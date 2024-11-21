<?php

/**
 * @var $this View
 * @var $model \common\models\archive\EStudentDiploma
 */

use backend\assets\PdfAsset;
use backend\components\View;
use common\models\structure\EUniversity;
PdfAsset::register($this);
$this->title = 'Diploma';

$university = EUniversity::findCurrentUniversity()->name;

?>
<div id="page" class="A4">
<section id="diploma" class="diploma">
    <div class="row">
        <div class="col-xs-5 text-center debug">
            <span class="univer"><?= $university ?></span>
            <span class="line"></span>
            <span class="line"></span>
        </div>
        <div class="col-xs-2"></div>
        <div class="col-xs-5 text-center">
            <span class="univer"><?= $university ?></span>

            <span class="line"></span>
            <span class="line"></span>
        </div>
    </div>
</section>
</div>