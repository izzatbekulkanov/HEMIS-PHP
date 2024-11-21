<?php

use Da\QrCode\QrCode;
use Da\QrCode\Format\BookmarkFormat;
use yii\helpers\Html;
use common\models\archive\EStudentDiploma;

$this->addBodyClass('login-page');
$this->title = __('Find Diploma');
?>

<!-- title row -->
<div class="col col-sm-3"></div>
<div class="col col-sm-6">
    <div class="login-bg">
        <div class="login-box" style="width: auto;max-width:none">
            <div class="box box-default">

                <div class="box-body no-padding">
                    <table class="table table-striped">
                        <tbody>
                        <tr>
                            <th colspan="2" class="text-center">
                                <?= __("Bitiruvchining shaxsiy ma'lumotlari") ?>
                            </th>
                        </tr>
                        <tr>
                            <th><?= __('Second Name') ?>:</th>
                            <td><?= @$selected->student->second_name; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('First Name') ?>:</th>
                            <td><?= @$selected->student->first_name; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Passport Number') ?>:</th>
                            <td><?= @$selected->student->passport_number; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Birth Date') ?>:</th>
                            <td><?= Yii::$app->formatter->asDate(@$selected->student->birth_date, 'php:d.m.Y'); ?></td>
                        </tr>

                        <tr>
                            <th colspan="2"
                                class="text-center"><?= __("Bitiruvchi tamomlagan OTMning ma'lumotlari") ?></th>
                        </tr>
                        <tr>
                            <th><?= __('University') ?></th>
                            <td><?= $selected->university_name; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Specialty') ?>:</th>
                            <td><?= $selected->specialty->name; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Education Degree') ?>:</th>
                            <td><?= $selected->education_type_name; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Education Form') ?>:</th>
                            <td><?= $selected->education_form_name; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Diploma Number') ?>:</th>
                            <td><?= $selected->diploma_number; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Register Date') ?>:</th>
                            <td><?= Yii::$app->formatter->asDate($selected->register_date, 'php:d.m.Y'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Diploma Category') ?>:</th>
                            <td><?= $selected->categoryLabel; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Year Of Enter') ?>:</th>
                            <td><?= $selected->student->year_of_enter; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Education Period') ?>:</th>
                            <td><?= $selected->education_period; ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="box-footer text-right">
                    <?php
                    $html = Html::a(
                        "" . __('Cancel'),
                        ['dashboard/diploma'],
                        ['data-pjax' => 0, 'class' => 'btn btn-default btn-flat']
                    );
                    if (file_exists($selected->getDiplomaFilePath())) {
                        $html .= ' ' . Html::a(
                                "<i class='fa fa-download'></i> " . __('Download Diploma'),
                                currentTo(['diploma' => 1]),
                                ['data-pjax' => 0, 'class' => 'btn btn-primary btn-flat']
                            );
                    }
                    if (file_exists($selected->getSupplementFilePath())) {
                        $html .= ' ' . Html::a(
                                "<i class='fa fa-download'></i> " . __('Download Supplement'),
                                currentTo(['supplement' => 1]),
                                ['data-pjax' => 0, 'class' => 'btn btn-primary btn-flat']
                            );
                    }
                    echo $html;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

