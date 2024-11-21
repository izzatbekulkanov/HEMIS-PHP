<?php

/**
 * @var $this View
 * @var $model \common\models\archive\EAcademicInformationData
 */

use backend\components\View;
use common\components\Config;
use common\models\system\classifier\EducationForm;
use common\models\archive\EAcademicInformationData;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<style>

    </style>


<?php
Yii::$app->formatter->language = Config::LANGUAGE_UZBEK;
?>
<div class="absolute bg-font bg-line-city-name-1"></div>
<div class="absolute bg-font bg-line-city-name-tip">(shahar nomi)</div>
<div class="absolute bg-font bg-line-un-name-2"></div>


<div class="absolute bg-font bg-line-reg-n-text-3">Roʻyxatga olish raqami:</div>
<div class="absolute bg-font bg-line-given-d-text-4">Berilgan sana:</div>
<div class="absolute bg-font bg-line-expel-r-5"></div>
<div class="absolute bg-font bg-line-expel-r-5-tip">(chetlashtirish sababi)</div>
<div class="absolute bg-font bg-line-order-t-6">_______ y. “_____” __________________</div>
 <div class="absolute bg-font bg-line-order-t2-6">dagi</div>
<div class="absolute bg-font bg-line-order-t-7">___________ -sonli buyrug‘i bilan oliy ta’lim muassasasidan chetlatildi</div>

<div class="absolute bg-font bg-line-stamp-place-t-1">Rektor:</div>
<div class="absolute bg-font bg-line-stamp-place-t-2">Dekan:</div>
<div class="absolute bg-font bg-line-stamp-place-t-3">Kotib(a):</div>
<div class="absolute bg-font bg-line-att-t-8">O‘zbekiston Respublikasining Davlat gerbi tasviri tushirilgan muhr
</div>



<div class="absolute bg-font r-bg-line-secondname-1">Familiyasi:</div>
<div class="absolute bg-font r-bg-line-firstname-2">Ismi:</div>
<div class="absolute bg-font r-bg-line-thirdname-3">Otasining ismi:</div>
<div class="absolute bg-font r-bg-line-birthdate-4">Tug‘ilgan kuni, oyi va yili: </div>
<div class="absolute bg-font r-bg-line-lastedu-5">Ma’lumoti haqida hujjat: </div>
<div class="absolute bg-font r-bg-line-admissionexam-6">Kirish imtihonlari: </div>
<div class="absolute bg-font r-bg-line-points-7">To‘plagan bali: </div>
<div class="absolute bg-font r-bg-line-passingscore-8">Minimal o‘tish bali:</div>

<div class="absolute bg-font r-bg-line-admission-univer-9">O‘qishga qabul qilinib,</div>
<div class="absolute bg-font r-bg-line-start_date-univer-10">______y. “____”____________</div>
<div class="absolute bg-font r-bg-line-start_date-univer2-10">dan</div>
<div class="absolute bg-font r-bg-line-end_date-univer-11">______y. “____”____________ </div>
<div class="absolute bg-font r-bg-line-end_date-univer2-11">gacha</div>

<div class="absolute bg-font r-bg-line-univer-12"></div>
<div class="absolute bg-font r-bg-line-univer-tip">(ta’lim muassasasi nomi)</div>
<div class="absolute bg-font r-bg-line-univer2-12"></div>
<div class="absolute bg-font r-bg-line-univer3-12"></div>

<div class="absolute bg-font r-bg-line-eduform-13"></div>
<div class="absolute bg-font r-bg-line-eduform-tip">(o‘qish shakli)</div>
<div class="absolute bg-font r-bg-line-label2-13">da o‘qidi.</div>


<div class="absolute bg-font r-bg-line-cont-start_date-univer-14">______y. “____”____________ </div>
<div class="absolute bg-font r-bg-line-cont-start_date-univer2-14">dan</div>
<div class="absolute bg-font r-bg-line-cont-end_date-univer-15">______y. “____”____________ </div>
<div class="absolute bg-font r-bg-line-cont-end_date-univer2-15">gacha</div>

<div class="absolute bg-font r-bg-line-cont-univer-16"></div>
<div class="absolute bg-font r-bg-line-cont-univer-tip">(ta’lim muassasasi nomi)</div>
<div class="absolute bg-font r-bg-line-cont-univer2-16"></div>
<div class="absolute bg-font r-bg-line-cont-univer3-16"></div>

<div class="absolute bg-font r-bg-line-eduform-17"></div>
<div class="absolute bg-font r-bg-line-eduform-tip-17">(o‘qish shakli)</div>
<div class="absolute bg-font r-bg-line-label2-17">da</div>
<div class="absolute bg-font r-bg-line-label3-17">davom ettirdi.</div>

<div class="absolute bg-font r-bg-line-spec-name-18">Ta’lim yo‘nalishi (mutaxassislik) nomi: </div>




<div class="absolute city-name"><?= $model->given_city; ?></div>
<div class="absolute univer-name"><?= $model->university_name; ?>

    <?//= date('y', @$model->order_date->getTimestamp()) ?></div>
<div class="absolute reg-number"><?= $model->register_number; ?></div>
<div class="absolute reg-date"><?= date('d.m.Y', $model->register_date->getTimestamp()) ?></div>
<div class="absolute expel-name"><?= $model->expulsion_decree_reason; ?></div>
<div class="absolute expel-year"><?= Yii::$app->formatter->asDate($model->expulsion_decree_date, 'php:Y'); ?></div>
<div class="absolute expel-day"><?= Yii::$app->formatter->asDate($model->expulsion_decree_date, 'php:d'); ?></div>
<div class="absolute expel-month"><?= Yii::$app->formatter->asDate($model->expulsion_decree_date, 'php:F'); ?></div>
<div class="absolute expel-order"><?= $model->expulsion_decree_number; ?></div>
<div class="absolute rector-name"><?= $model->rector_fullname; ?></div>
<div class="absolute dean-name"><?= $model->dean_fullname; ?></div>
<div class="absolute secretary-name"><?= $model->secretary_fullname; ?></div>


<div class="absolute r-second-name"><?= $model->second_name; ?></div>
<div class="absolute r-first-name"><?= $model->first_name; ?></div>
<div class="absolute r-third-name"><?= $model->third_name; ?></div>
<div class="absolute r-birthdate"><?= date('d.m.Y', $model->student_birthday->getTimestamp()) ?></div>
<div class="absolute r-last-education">
    <?= $model->last_education; ?>
</div>
<div class="absolute r-points"><?= $model->accumulated_points; ?></div>
<div class="absolute r-passingscore"><?= $model->passing_score; ?></div>

<?php
    if($model->studied_start_date !=null && $model->studied_start_date !=null):
        $studied_start_date_y  = Yii::$app->formatter->asDate($model->studied_start_date, 'php:Y');
        $studied_start_date_d  = Yii::$app->formatter->asDate($model->studied_start_date, 'php:d');
        $studied_start_date_m  = Yii::$app->formatter->asDate($model->studied_start_date, 'php:F');
        $studied_end_date_y  = Yii::$app->formatter->asDate($model->studied_end_date, 'php:Y');
        $studied_end_date_d  = Yii::$app->formatter->asDate($model->studied_end_date, 'php:d');
        $studied_end_date_m  = Yii::$app->formatter->asDate($model->studied_end_date, 'php:F');

        $continue_start_date_y  = Yii::$app->formatter->asDate($model->continue_start_date, 'php:Y');
        $continue_start_date_d  = Yii::$app->formatter->asDate($model->continue_start_date, 'php:d');
        $continue_start_date_m  = Yii::$app->formatter->asDate($model->continue_start_date, 'php:F');
        $continue_end_date_y  = Yii::$app->formatter->asDate($model->continue_end_date, 'php:Y');
        $continue_end_date_d  = Yii::$app->formatter->asDate($model->continue_end_date, 'php:d');
        $continue_end_date_m  = Yii::$app->formatter->asDate($model->continue_end_date, 'php:F');
        $moved_hei_name = ($model->moved_hei_name) ? EAcademicInformationData::normalizeStringLines(preg_split('/\s+/', $model->moved_hei_name), 46, 3) : "";
        $university_name = ($model->university_name) ? EAcademicInformationData::normalizeStringLines(preg_split('/\s+/', $model->university_name), 46, 3) : "";

        $moved_education_type = strtolower ($model->education_form_name_moved) .' ta’lim shakli';
        $studied_education_type = strtolower ($model->education_form_name).' ta’lim shakli';
    else:
        $studied_start_date_y  = Yii::$app->formatter->asDate($model->continue_start_date, 'php:Y');
        $studied_start_date_d  = Yii::$app->formatter->asDate($model->continue_start_date, 'php:d');
        $studied_start_date_m  = Yii::$app->formatter->asDate($model->continue_start_date, 'php:F');
        $studied_end_date_y  = Yii::$app->formatter->asDate($model->continue_end_date, 'php:Y');
        $studied_end_date_d  = Yii::$app->formatter->asDate($model->continue_end_date, 'php:d');
        $studied_end_date_m  = Yii::$app->formatter->asDate($model->continue_end_date, 'php:F');

        $continue_start_date_y  = "";
        $continue_start_date_d  = "";
        $continue_start_date_m  = "";
        $continue_end_date_y  = "";
        $continue_end_date_d  = "";;
        $continue_end_date_m  = "";
        $moved_hei_name = ($model->university_name) ? EAcademicInformationData::normalizeStringLines(preg_split('/\s+/', $model->university_name), 46, 3) : "";
        $university_name = "";
        $moved_education_type = strtolower ($model->education_form_name).' ta’lim shakli';
        $studied_education_type ="";

     endif;
?>
<div class="absolute r-study-start-year">
    <?= $studied_start_date_y; ?>
</div>
<div class="absolute r-study-start-day">
    <?= $studied_start_date_d; ?>
</div>
<div class="absolute r-study-start-month">
    <?= $studied_start_date_m; ?>
</div>

<div class="absolute r-study-end-year">
    <?= $studied_end_date_y; ?>
</div>
<div class="absolute r-study-end-day">
    <?= $studied_end_date_d; ?>
</div>
<div class="absolute r-study-end-month">
    <?= $studied_end_date_m; ?>
</div>
<?php

?>
<div class="absolute r-study-univer-name">
    <?= $moved_hei_name[0]; ?>
</div>
<div class="absolute r-study-univer-name-line2">
    <?= $moved_hei_name[1]; ?>
</div>
<div class="absolute r-study-univer-name-line3">
    <?= $moved_hei_name[2]; ?>
</div>
<div class="absolute r-study-eduform-name">
    <?= $moved_education_type;?>
</div>
<?php
    if($model->continue_start_date !=null && $model->continue_end_date !=null):
?>
<div class="absolute r-cont-start-year">
    <?= $continue_start_date_y; ?>
</div>
<div class="absolute r-cont-start-day">
    <?= $continue_start_date_d; ?>
</div>
<div class="absolute r-cont-start-month">
    <?= $continue_start_date_m; ?>
</div>

<div class="absolute r-cont-end-year">
    <?= $continue_end_date_y; ?>
</div>
<div class="absolute r-cont-end-day">
    <?= $continue_end_date_d; ?>
</div>
<div class="absolute r-cont-end-month">
    <?= $continue_end_date_m; ?>
</div>
    <?php
        endif;
    ?>
<div class="absolute r-cont-univer-name">
    <?= @$university_name[0]; ?>
</div>
<div class="absolute r-cont-univer-name-line2">
    <?= @$university_name[1]; ?>
</div>
<div class="absolute r-cont-univer-name-line3">
    <?= @$university_name[2]; ?>
</div>
<div class="absolute r-cont-eduform-name">
    <?= $studied_education_type;?>
</div>

<div class="absolute r-spec-name">
    <?= $model->specialty_code; ?> – <?= $model->specialty_name; ?>
</div>

    <pagebreak/>
<?php
echo $content2;
?>