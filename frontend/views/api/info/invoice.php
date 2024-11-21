<?php

use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\RatingGrade;
use common\models\student\EStudentMeta;
use Da\QrCode\QrCode;
use Da\QrCode\Format\BookmarkFormat;
use yii\helpers\Html;
use common\models\archive\EStudentDiploma;


?>
<style>
    @media (min-width: 480px) {
        table th {
            width: 25%;
        }
    }
</style>
<section class="invoice">
    <!-- title row -->
    <div class="row">
        <div class="col-xs-12">
            <h4 class="text-center">
                <b>Talaba ma'lumotlari</b>
            </h4>
            <table class="table table-striped table-bordered table-responsive">
                <tbody>
                <tr>
                    <th>Talaba F.I.SH. </th>
                    <td><?=@$selected->student->fullName;?></td>
                </tr>
                <tr>
                    <th>Ta'lim turi: </th>
                    <td><?=@$selected->educationType->name;?></td>
                </tr>
                <tr>
                    <th>Ta'lim shakli: </th>
                    <td><?=@$selected->educationForm->name;?></td>
                </tr>
                <tr>
                    <th>O'quv yili: </th>
                    <td><?=@$selected->educationYear->name;?></td>
                </tr>
                <tr>
                    <th>O'quv kursi: </th>
                    <td><?=@$selected->level->name;?></td>
                </tr>
                <tr>
                    <th>Mutaxassislik: </th>
                    <td><?=$selected->specialty->fullName;?>

                    </td>
                </tr>
                <tr>
                    <th>Ta'lim muassasasi: </th>
                    <td><?= $univer->name;?></td>
                </tr>
                </tbody>
            </table>

        </div>
        <!-- /.col -->
    </div>

    <div class="clearfix"></div>

    <div class="row">
        <div class="col-xs-12">
            <h4 class="text-center">
                <b>Hisob-faktura ma'lumotlari</b>
            </h4>
            <table class="table table-striped table-bordered table-responsive hei-info">
                <tbody>
                <tr>
                    <th>Hisob-faktura raqami</th>
                    <td><?=$selected->invoice_number;?></td>
                </tr>
                <tr>
                    <th>Hisob-faktura sanasi</th>
                    <td><?= Yii::$app->formatter->asDate($selected->invoice_date, 'php:d.m.Y');?></td>
                </tr>
                <tr>
                    <th>Hisob-faktura summasi: </th>
                    <td><?=Yii::$app->formatter->asCurrency($selected->invoice_summa);?></td>
                </tr>
                <tr>
                    <th> </th>
                    <td> &nbsp;</td>
                </tr>

                <tr>
                    <th>Shartnoma sanasi: </th>
                    <td><?= Yii::$app->formatter->asDate($selected->studentContract->date, 'php:d.m.Y');?></td>
                </tr>

                <tr>
                    <th>Shartnoma raqami</th>
                    <td><?=$selected->studentContract->number;?></td>
                </tr>
                <tr>
                    <th>Shartnoma sanasi: </th>
                    <td><?= Yii::$app->formatter->asDate($selected->studentContract->date, 'php:d.m.Y');?></td>
                </tr>
                <tr>
                    <th>Shartnoma turi: </th>
                    <td><?=@$selected->studentContract->contractType->name;?></td>
                </tr>
                <tr>
                    <th>Shartnoma shakli: </th>
                    <td><?= \common\models\finance\EStudentContractType::getContractFormOptions()[$selected->studentContract->contract_form_type];?></td>
                </tr>
                <tr>
                    <th>Shartnoma summasi turi: </th>
                    <td><?=@$selected->studentContract->contractSummaType->name;?></td>
                </tr>
                <tr>
                    <th>Shartnoma summasi: </th>
                    <td><?=Yii::$app->formatter->asCurrency($selected->studentContract->summa);?></td>
                </tr>
                <tr>
                    <th>Chegirma: </th>
                    <td><?=@$selected->studentContract->discount;?></td>
                </tr>

                </tbody>
            </table>

        </div>
        <!-- /.col -->
    </div>

</section>