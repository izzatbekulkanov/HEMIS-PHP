<?php
use Da\QrCode\QrCode;
use Da\QrCode\Format\BookmarkFormat;
use yii\helpers\Html;
?>

<section class="invoice">
    <!-- title row -->
    <div class="row">
        <div class="col-xs-12">
            <h4 class="text-center">
                Тўлов-контракт (икки томонлама) асосида мутахассис тайёрлашга
            </h4>
            <h4 class="text-center">
                КОНТРАКТ № <?= $selected->number;?>
            </h4>
        </div>
        <!-- /.col -->
    </div>
    <div class="row" style="margin: 10px 0 !important;">
        <table class="table">
            <tr>
                <td class="text-left"><?= $univer->address?></td>
                <td class="text-right"><?= Yii::$app->formatter->asDate($selected->date, 'php:d.m.Y');?></td>
            </tr>
        </table>
        <!-- /.col -->
    </div>

    <div class="clearfix"></div>
    <div class="row">
        <h5 class="text-center"><b>I. КОНТРАКТ ПРЕДМЕТИ</b></h5>
        <div class="col-xs-12">

            <div class="no-shadow text-justify" style="">
                <table>
                    <tbody>
                    <tr>
                        <td>Таълим босқичи: </td>
                        <th><?=$selected->educationType->name;?></th>
                    </tr>
                    <tr>
                        <td>Таълим шакли: </td>
                        <th><?=$selected->educationForm->name;?></th>
                    </tr>
                    <tr>
                        <td>Таълим йўналиши: </td>
                        <th><?=$selected->specialty->fullName;?></th>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
    <div class="clearfix"></div>
    <div class="row">
        <p class="text-center"><b>II. ТАЪЛИМ ХИЗМАТИНИ КЎРСАТИШ НАРХИ</b></p>
        <div class="col-xs-12">

            <div class="no-shadow text-justify" style="margin-top: 2px;">
                2.2. Ушбу контракт бўйича таълим олувчини бир йиллик ўқитиш учун тўлов <b><u><?= $selected->summa?></u></b> сўмни (<b><?= $selected->contractSummaType->name;?></b>) ташкил этади ва қуйидаги муддатларда амалга оширилади:

            </div>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
    <div class="clearfix"></div>

    <!-- /.row -->
    <div class="clearfix"></div>
    <div class="row invoice-info">
        <div class="col-xs-12">
            <p class="text-center">
                <b>
                    VII. ТОМОНЛАРНИНГ РЕКВИЗИТЛАРИ ВА ИМЗОЛАРИ
                </b>
            </p>
            <table>
                <tr>
                    <td width="50%">
                        <div class="col-sm-6 invoice-col">
                            <b>7.1. Таълим муассасаси:</b>
                            <address>
                                <b> <?= $univer->name;?></b>
                            </address>

                            <b>Почта манзили: </b>
                            <address>
                                <?= $univer->mailing_address;?>
                            </address>
                            <b>Банк реквизитлари: </b>
                            <address>
                                <?= $univer->bank_details;?>
                            </address>
                        </div>
                    </td>

                    <td>
                        <div class="col-sm-6 invoice-col">

                        </div>
                    </td>
                </tr>
            </table>


            <!-- /.col -->


        </div>
        <!-- /.col -->
        <div class="col-xs-12">
            <table>
                <tr>
                    <td width="50%">
                        Таълим муассасаси раҳбари: ______________________
                        <br>
                        <?php
                        //  $qrCode = (new QrCode(Yii::$app->formatter->asDate($selected->date, 'php:d.m.Y') . ' sanadagi '.$selected->number . "-raqamli shartnoma " . $selected->student->fullName . ' talabaga berilgan'))
                        $qrCode = (new QrCode(\yii\helpers\Url::to(
                            [
                                'finance/contract-info',
                                'code' => $selected->id,
                            ], [])))
                            ->setSize(100)
                            ->setMargin(2);
                        echo '<img src="' . $qrCode->writeDataUri() . '">';
                        //$format = new BookMarkFormat(['url' => 'http://tuit.uz']);

                        //$qrCode = new QrCode($format);

                        //header('Content-Type: ' . $qrCode->getContentType());

                        //   echo $qrCode->writeString();
                        //echo '<img src="' . $qrCode->writeDataUri() . '">';
                        ?>
                    </td>

                    <td>

                    </td>
                </tr>
            </table>


        </div>
        <!-- /.col -->
        <br>
        <br>
        <div class="col-xs-12">

            <div class="col-sm-6 invoice-col">
                <b>7.2. Таълим олувчи: </b>
                <div class="no-shadow text-justify" style="margin-top: 2px;">
                    Ф.И.Ш.: <?= $selected->student->fullName?> <br>
                    Яшаш манзили: <?= $selected->student->home_address?> <br>
                    Паспорт маълумотлари: <?= $selected->student->passport_number;?> <br>
                    Телефон рақами:  <br>

                </div>
            </div>


        </div>
        <!-- /.col -->

        <div class="col-xs-12">

            <div class="col-sm-12 invoice-col">
                Таълим олувчининг имзоси: ____________________
                <br>    (имзо)


            </div>


        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
    <!-- this row will not appear when printing -->

</section>