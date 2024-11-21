<?php

/**
 * @var $this View
 * @var $model EStudentDiploma
 * @var $records array|\common\models\archive\EAcademicRecord[]
 */

use backend\components\View;
use common\components\Config;
use common\models\archive\EStudentDiploma;
use common\models\structure\EUniversity;
use Da\QrCode\QrCode;
use yii\helpers\Html;
use yii\helpers\Url;
$univer = EUniversity::findCurrentUniversity();
?>
<?php if(!isset($_GET['ready'])):?>
<style type="text/css">
    .custom-width {
        width:50% !important;
    }
</style>
<?php endif;?>
    <div class="p6-table-box">
        <table class="table table-bordered">
            <tr>
                <td class="custom-width" style="width:360px; text-align: center; vertical-align: top;">
                    <img class='logo' style="width:120px;" src="<?= $this->getImageUrl('img/gerb.png') ?>">
                    <br>O'ZBEKISTON RESPUBLIKASI

                    <div class="absolute bg-font bg-line-un-name-2"><?= $univer->address; ?></div>

                    <div class="absolute bg-font bg-line-un-name-tip" style="border-top: 1px dotted #ccc;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(shahar nomi) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                    <div class="absolute bg-font bg-line-un-name-1"></div>
                    <div class="absolute bg-font bg-line-un-name-2"><?= $univer->name; ?></div>
                    <div class="absolute bg-font bg-line-un-name-tip" style="border-top: 1px dotted #ccc;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (ta'lim muassasasi nomi) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                    <br>
                    <br>
                    <div class="p3-student-full-name"><b>AKADEMIK <br>MA'LUMOTNOMA</b></div>
                    <br>


                    <div class="p1-diploma-number"><b><?= $model->academic_number;?></b></div>
                    <br>
                    <table class="table table-custom" border="0px" style="border:0px;">
                        <tr>
                            <td style="width:41%; text-align: left;">Ro'yxatga olish raqami:</td>
                            <td><div class="custom-underline" style="text-align: left;"><?= $model->academic_register_number;?></div></td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">Berilgan sana:</td>
                            <td><div class="custom-underline" style="text-align: left;"><?= Yii::$app->formatter->asDate($model->academic_register_date, 'dd.MM.Y') ?></div></td>
                        </tr>
                    </table>

                    <table class="table table-custom" border="0px" style="border:0px;">
                        <tr>
                            <td colspan="2" style="text-align: center;">

                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: center;border-top: 1px dotted #ccc;">
                                <div class="custom-line">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(chetlashtirish sababi)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: left;">
                                20__ y. "____" ___________________ dagi
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: justify;">
                                ___-sonli buyrug'i bilan oliy ta'lim muassasasidan chetlatildi.
                            </td>
                        </tr>
                    </table>



                    <table class="table table-custom" border="0px" style="border:0px;">
                        <tr>
                            <td style="text-align:left; width:20%">Rektor:
                            </td>
                            <td style="text-align: left;">_______________
                            </td>
                            <td style="text-align: left;"><?= $model->rector;?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">Dekan:
                            </td>
                            <td style="text-align: left;">_______________
                            </td>
                            <td style="text-align: left;"><?= $model->dean;?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left;">Kotib(a):
                            </td>
                            <td style="text-align: left;">_______________
                            </td>
                            <td style="text-align: left;"><?= $model->secretary;?>
                            </td>
                        </tr>
                    </table>

                </td>
                <td style="vertical-align: top;">
                    <table class="table table-custom" border="0px" style="border:0px;">
                        <tr>
                            <td style="width:30%">Familiyasi:</td>
                            <td><div class="custom-underline"><?= $student->student->second_name;?></div></td>
                        </tr>
                        <tr>
                            <td>Ismi:</td>
                            <td><div class="custom-underline"><?= $student->student->first_name;?></div></td>
                        </tr>
                        <tr>
                            <td>Otasining ismi:</td>
                            <td><div class="custom-underline"><?= $student->student->third_name;?></div></td>
                        </tr>
                    </table>

                    <table class="table table-custom" border="0px" style="border:0px;">
                        <tr>
                            <td style="width:48%">Tug'ilgan kuni, oyi va yili:</td>
                            <td><div class="custom-underline"><?= Yii::$app->formatter->asDate($student->student->birth_date, 'dd.MM.Y') ?></div></td>
                        </tr>
                        <tr>
                            <td><br></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Ma'lumoti haqidagi hujjat:</td>
                            <td><div class="custom-underline"></div></td>
                        </tr>
                    </table>

                    <table class="table table-custom" border="0px" style="border:0px;">
                        <tr>
                            <td style="width:45%">
                                Kirish imtihonlari: <br>
                                (test: to'plagan bali –
                            </td>
                            <td><div class="custom-underline"><? ?></div></td>
                        </tr>
                        <tr>
                            <td style="width:45%">
                                minimal o'tish bali -
                            </td>
                            <td><div class="custom-underline"><? ?></div>).</td>
                        </tr>

                        <tr>
                            <td colspan="2">
                                O'qishga qabul qilinib, <br>
                                20__ y. "____" ___________________ dan
                            </td>

                        </tr>
                        <tr>
                            <td colspan="2">

                                20__ y. "____" ___________________ gacha
                            </td>

                        </tr>
                    </table>


                    <table class="table table-custom" border="0px" style="border:0px;">
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                <?= $univer->name; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: center;border-top: 1px dotted #ccc;">
                                <div class="custom-line">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(ta'lim muassasasi nomi)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                <?= $student->educationForm->name; ?> da o'qidi
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: justify;">
                                <div class="custom-line">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(o'qish shakli)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                            </td>
                        </tr>
                    </table>

                    <table class="table table-custom" border="0px" style="border:0px;">
                        <tr>
                            <td colspan="2">
                                20__ y. "____" ___________________ dan
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">

                                20__ y. "____" ___________________ gacha
                            </td>

                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                <?= $univer->name; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: center;border-top: 1px dotted #ccc;">
                                <div class="custom-line">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(ta'lim muassasasi nomi)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                <?= $student->educationForm->name; ?> da davom ettirdi.
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: justify;">
                                <div class="custom-line">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(o'qish shakli)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                            </td>
                        </tr>
                    </table>

                    <table class="table table-custom" border="0px" style="border:0px;">
                        <tr>
                            <td style="width:60%">Ta'lim yo'nalishi (mutaxassislik):</td>
                            <td><div class="custom-underline"><?= $student->specialty->mainSpecialty ? $student->specialty->mainSpecialty->name : $student->specialty->name ?></div></td>
                        </tr>
                    </table>




                </td>
            </tr>


        </table>
    </div>

