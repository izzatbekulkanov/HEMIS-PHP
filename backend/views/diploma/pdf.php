<?php

/**
 * @var $this View
 * @var $model EStudentDiploma
 */

use backend\components\View;
use common\components\Config;
use common\models\archive\EStudentDiploma;
use common\models\structure\EUniversity;
use common\models\system\classifier\EducationType;
use Da\QrCode\QrCode;
use yii\helpers\Html;
use yii\helpers\Url;

$universityNameUz = $model->getTranslation('university_name', Config::LANGUAGE_UZBEK);
$universityNameEn = $model->getTranslation('university_name', Config::LANGUAGE_ENGLISH);
$specializationUz = $model->getTranslation('specialty_name', Config::LANGUAGE_UZBEK);
$specializationEn = $model->getTranslation('specialty_name', Config::LANGUAGE_ENGLISH);
$specializationUz = EStudentDiploma::normalizeStringLines(preg_split('/\s+/', $specializationUz), 54, 3);
$specializationEn = EStudentDiploma::normalizeStringLines(preg_split('/\s+/', $specializationEn), 50, 3);
/*if (strlen($specializationUz) > 54) {
    $specializationUz = preg_split('/\s+/', $specializationUz, 3);
    if (count($specializationUz) > 1 && strlen($specializationUz[0] . ' ' . $specializationUz[1]) < 55) {
        $specializationUz = [$specializationUz[0] . ' ' . $specializationUz[1], $specializationUz[2] ?? ''];
    }
} else {
    $specializationUz = [$specializationUz];
}*/
/*if (strlen($specializationEn) > 50) {
    $specializationEn = preg_split('/\s+/', $specializationEn, 3);
    if (count($specializationEn) > 1 && strlen($specializationEn[0] . ' ' . $specializationEn[1]) < 51) {
        $specializationEn = [$specializationEn[0] . ' ' . $specializationEn[1], $specializationEn[2] ?? ''];
    }
} else {
    $specializationEn = [$specializationEn];
}*/
$qualificationTitleUz = $model->getTranslation('qualification_name', Config::LANGUAGE_UZBEK);
$qualificationTitleUz = EStudentDiploma::normalizeStringLines(preg_split('/\s+/', $qualificationTitleUz), 54, 2);
/*if (strlen($qualificationTitleUz) > 50) {
    $qualificationTitleUz = preg_split('/\s+/', $qualificationTitleUz, 4);
    if (count($qualificationTitleUz) > 1 && strlen($qualificationTitleUz[0] . ' ' . $qualificationTitleUz[1] . ' ' . $qualificationTitleUz[2]) < 52) {
        $qualificationTitleUz = [$qualificationTitleUz[0] . ' ' . $qualificationTitleUz[1] . ' ' . $qualificationTitleUz[2], $qualificationTitleUz[3]];
    } elseif (count($qualificationTitleUz) > 1 && strlen($qualificationTitleUz[0] . ' ' . $qualificationTitleUz[1]) < 51) {
        $qualificationTitleUz = [$qualificationTitleUz[0] . ' ' . $qualificationTitleUz[1], $qualificationTitleUz[2]];
    } else {
        $qualificationTitleUz = [$qualificationTitleUz[0], $qualificationTitleUz[1] . ' ' . $qualificationTitleUz[2] . ' ' . $qualificationTitleUz[3]];
    }
} else {
    $qualificationTitleUz = [$qualificationTitleUz];
}*/
$qualificationTitleEn = $model->getTranslation('qualification_name', Config::LANGUAGE_ENGLISH);
$qualificationTitleEn = EStudentDiploma::normalizeStringLines(preg_split('/\s+/', $qualificationTitleEn), 49, 2);
/*if (strlen($qualificationTitleEn) > 45) {
    $qualificationTitleEn = preg_split('/\s+/', $qualificationTitleEn, 4);
    if (count($qualificationTitleEn) > 1 && strlen($qualificationTitleEn[0] . ' ' . $qualificationTitleEn[1] . ' ' . $qualificationTitleEn[2]) < 47) {
        $qualificationTitleEn = [$qualificationTitleEn[0] . ' ' . $qualificationTitleEn[1] . ' ' . $qualificationTitleEn[2], $qualificationTitleEn[3]];
    } elseif (count($qualificationTitleEn) > 1 && strlen($qualificationTitleEn[0] . ' ' . $qualificationTitleEn[1]) < 46) {
        $qualificationTitleEn = [$qualificationTitleEn[0] . ' ' . $qualificationTitleEn[1], $qualificationTitleEn[2]];
    } else {
        $qualificationTitleEn = [$qualificationTitleEn[0], $qualificationTitleEn[1] . ' ' . $qualificationTitleEn[2] . ' ' . $qualificationTitleEn[3]];
    }
} else {
    $qualificationTitleEn = [$qualificationTitleEn];
}*/
$oldLang = Yii::$app->language;
Yii::$app->formatter->locale = Config::LANGUAGE_UZBEK;
$graduateInfoUz = sprintf(
    '%s-yil %s, %s',
    Yii::$app->formatter->asDate($model->register_date->getTimestamp(), 'php:Y'),
    Yii::$app->formatter->asDate($model->register_date->getTimestamp(), 'php:j-F'),
    $model->getTranslation('given_city', Config::LANGUAGE_UZBEK)
);
$order_date_uz = Yii::$app->formatter->asDate($model->order_date, 'php:j-F');
Yii::$app->formatter->locale = Config::LANGUAGE_ENGLISH;
$graduateInfoEn = sprintf(
    '%s, %s',
    Yii::$app->formatter->asDate($model->register_date->getTimestamp(), 'php:F j, Y'),
    $model->getTranslation('given_city', Config::LANGUAGE_ENGLISH)
);
$order_date_en = Yii::$app->formatter->asDate($model->order_date, 'php:F j, Y');
Yii::$app->formatter->locale = $oldLang;

$rector_full_name = $model->getTranslation('rector_fullname', Config::LANGUAGE_UZBEK);
$rector_full_name_en = $model->getTranslation('rector_fullname', Config::LANGUAGE_ENGLISH);
$registrationNumber = $model->register_number;

$qrCode = (new QrCode($model->diploma_link))
    ->setSize(100)
    ->setMargin(2)
    ->writeDataUri();


?>

<div class="absolute bg-font bg-line-un-name-1"></div>
<div class="absolute bg-font bg-line-un-name-2"></div>
<div class="absolute bg-font bg-line-un-name-tip">(taʼlim muassasasining nomi)</div>
<div class="absolute bg-font bg-line-att-t1">Davlat attestatsiya komissiyasining</div>
<div class="absolute bg-font bg-line-att-t2">20____-yil________________dagi qaroriga binoan</div>
<div class="absolute bg-font bg-line-st-name-l1"></div>
<div class="absolute bg-font bg-line-st-name-l1-tip">(bitiruvchining familiyasi, ismi, otasining ismi)</div>
<div class="absolute bg-font bg-line-sp-l1"></div>
<div class="absolute bg-font bg-line-sp-l1-text">ga</div>
<div class="absolute bg-font bg-line-sp-l2"></div>
<div class="absolute bg-font bg-line-sp-l2-tip">(<?= $model->_education_type === EducationType::EDUCATION_TYPE_BACHELOR ? 'taʼlim yoʻnalishining nomi' : 'mutaxassislik nomi' ?>)</div>
<div class="absolute bg-font bg-line-sp-l3"></div>
<div class="absolute bg-font bg-line-sp-l4"></div>
<div class="absolute bg-font bg-line-sp-l4-tip"><?= $model->_education_type === EducationType::EDUCATION_TYPE_BACHELOR ? 'yoʻnalishi boʻyicha' : 'mutaxassisligi boʻyicha' ?></div>
<div class="absolute bg-font bg-line-type-of-education-t1"><?= $model->_education_type === EducationType::EDUCATION_TYPE_BACHELOR ? 'BAKALAVR' : 'MAGISTR' ?></div>
<div class="absolute bg-font bg-line-type-of-education-t2">DARAJASI</div>
<div class="absolute bg-font bg-line-qs-1"></div>
<div class="absolute bg-font bg-line-qs-1-tip">va</div>
<div class="absolute bg-font bg-line-qs-1-tip-tip">(kvalifikatsiya nomi)</div>
<div class="absolute bg-font bg-line-qs-2"></div>
<div class="absolute bg-font bg-line-qs-2-tip">kvalifikatsiyasi berildi</div>
<div class="absolute bg-font bg-line-stamp-place">M.Oʻ.</div>
<div class="absolute bg-font bg-line-stamp-place-t1">Rektor:</div>
<div class="absolute bg-font bg-line-reg-n-text">Roʻyxatga olish raqami</div>
<div class="absolute bg-font bg-line-reg-n-l1"></div>
<div class="absolute bg-font bg-line-ip-l1"></div>
<div class="absolute bg-font bg-line-ip-l2">(berilgan sana va joy)</div>

<div class="absolute bg-font e-bg-line-un-name-1"></div>
<div class="absolute bg-font e-bg-line-un-name-2"></div>
<div class="absolute bg-font e-bg-line-un-name-2-tip">(the educational institution)</div>
<div class="absolute bg-font e-bg-line-att-t1">In accordance with the decision of</div>
<div class="absolute bg-font e-bg-line-att-t2">the State Attestation Commission from ______________________</div>
<div class="absolute bg-font e-bg-line-st-name-1"></div>
<div class="absolute bg-font e-bg-line-st-name-2"></div>
<div class="absolute bg-font e-bg-line-st-name-2-tip">(graduate's surname, first name, other name)</div>
<div class="absolute bg-font e-bg-line-sp-l1"></div>
<div class="absolute bg-font e-bg-line-sp-l1-tip">is awarded with</div>
<div class="absolute bg-font e-bg-line-sp-l1-tip-tip">(in the speciality)</div>
<div class="absolute bg-font e-bg-line-sp-l2"></div>
<div class="absolute bg-font e-bg-line-sp-l3"></div>
<div class="absolute bg-font e-bg-line-type-of-education-t1"><?= $model->_education_type === EducationType::EDUCATION_TYPE_BACHELOR ? 'BACHELOR\'S' : 'MASTER\'S' ?></div>
<div class="absolute bg-font e-bg-line-type-of-education-t2">DEGREE</div>
<div class="absolute bg-font e-bg-line-qs-1"></div>
<div class="absolute bg-font e-bg-line-qs-1-tip">and qualified as</div>
<div class="absolute bg-font e-bg-line-qs-1-tip-tip">(qualification)</div>
<div class="absolute bg-font e-bg-line-qs-2"></div>
<div class="absolute bg-font e-bg-line-stamp-place">P.S.</div>
<div class="absolute bg-font e-bg-line-stamp-place-t1">Rector:</div>
<div class="absolute bg-font e-bg-line-reg-n-text">Registration number</div>
<div class="absolute bg-font e-bg-line-reg-n-l1"></div>
<div class="absolute bg-font e-bg-line-ip-l1"></div>
<div class="absolute bg-font e-bg-line-ip-l2">(date, place)</div>

<div class="absolute uz-un-name"><?= $universityNameUz ?></div>
<div class="absolute uz-issue-year"><?= date('y', $model->order_date->getTimestamp()) ?></div>
<div class="absolute uz-reg-number"><?= $order_date_uz ?></div>
<div class="absolute uz-full-name"><?= $model->getTranslation('student_name', Config::LANGUAGE_UZBEK) ?></div>
<div class="absolute uz-direction-1"><?= $specializationUz[0] ?></div>
<div class="absolute uz-direction-2"><?= $specializationUz[1] ?? '' ?></div>
<div class="absolute uz-direction-3"><?= $specializationUz[2] ?? '' ?></div>
<div class="absolute uz-qualification-1"><?= $qualificationTitleUz[0] ?></div>
<div class="absolute uz-qualification-2"><?= implode(' ', array_splice($qualificationTitleUz, 1)) ?></div>
<div class="absolute uz-rector-full-name"><?= $rector_full_name ?></div>
<div class="absolute uz-registration-number"><?= $registrationNumber ?></div>
<div class="absolute uz-graduate-info"><?= $graduateInfoUz ?></div>

<div class="absolute en-un-name"><?= $universityNameEn ?></div>
<div class="absolute en-issue-date"><?= $order_date_en ?></div>
<div class="absolute en-full-name"><?= $model->getTranslation('student_name', Config::LANGUAGE_ENGLISH) ?></div>
<div class="absolute en-direction-1"><?= $specializationEn[0] ?></div>
<div class="absolute en-direction-2"><?= $specializationEn[1] ?? '' ?></div>
<div class="absolute en-direction-3"><?= $specializationEn[2] ?? '' ?></div>
<div class="absolute en-qualification-1"><?= $qualificationTitleEn[0] ?></div>
<div class="absolute en-qualification-2"><?= implode(' ', array_splice($qualificationTitleEn, 1)) ?></div>
<div class="absolute en-rector-full-name"><?= $rector_full_name_en ?></div>
<div class="absolute en-registration-number"><?= $registrationNumber ?></div>
<div class="absolute en-graduate-info"><?= $graduateInfoEn ?></div>
<div class="absolute qr-code">
    <?= Html::img($qrCode) ?>
</div>