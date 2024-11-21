<?php if(!isset($_GET['ready-pdf'])):?>
    <style>
        address{
            margin-bottom: 4px !important;
        }
        .bank_detail {
            background-color: white !important;
            font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif !important;
            font-size: 14px !important;
            padding: 0 !important;
            border: 0;
            overflow: visible;
        }


    </style>
    <?php
    $this->params['breadcrumbs'][] = ['url' => ['finance/student-contract'], 'label' => __('Finance Student Contract')];
    $this->params['breadcrumbs'][] = $this->title;
    ?>
<?php endif;?>
<?php
use Da\QrCode\QrCode;
use Da\QrCode\Format\BookmarkFormat;
use yii\helpers\Html;
use yii\helpers\Url;
?>

<section class="invoice">
    <!-- title row -->
    <div class="row">
        <div class="col-xs-12">
            <h4 class="text-center">
                To‘lov-kontrakt asosida xorijiy fuqarolarni o‘qitishga
            </h4>
            <h4 class="text-center">
                KONTRAKT № <?= $selected->number;?>
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

    <div class="row">
        <div class="col-xs-12">
            <p class="no-shadow text-justify" style="margin-top: 15px;">
                O‘zbekiston Respublikasi Prezidentining 2019-yil 17-iyundagi PQ-4359 - son Qarori, Vazirlar Mahkamasining
                2017-yil 20-iyundagi 393-son va 2008-yil 4-avgustdagi 169-son Qarorlari, Oliy va o‘rta maxsus ta’lim vazirligining
                2012-yil 28-dekabrdagi 508-son buyrug‘i bilan tasdiqlangan, Adliya vazirligida 2013-yil 26-fevralda 2431-son
                bilan davlat ro‘yxatidan o‘tkazilgan “Oliy va o‘rta maxsus, kasb-hunar ta’limi muassasalarida o‘qitishning
                to‘lov-kontrakt shakli va undan tushgan mablag‘larni taqsimlash tartibi to‘g‘risida”gi Nizomga muvofiq,
                <b><?= strtoupper($univer->name);?></b> (keyingi o‘rinlarda “Ta’lim muassasasi”) nomidan rektor (direktor)
                <b><?= $selected->rector;?></b> bir tomondan, <b><?= $selected->student->country->name;?></b> fuqarosi
                <b><?= $selected->student->fullName;?></b>
                (keyingi o‘rinlarda “Ta’lim oluvchi”) ikkinchi tomondan, birgalikda “Tomonlar” deb ataladigan shaxslar mazkur
                kontraktni quyidagicha tuzdilar:
            </p>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
    <div class="clearfix"></div>
    <div class="row">
        <h5 class="text-center"><b>I. KONTRAKT PREDMETI</b></h5>
        <div class="col-xs-12">

            <div class="no-shadow text-justify" style="">
                1.1. Ta’lim muassasasi belgilangan ta’lim yo‘nalishi yoki mutaxassislik bo‘yicha Ta’lim oluvchiga ta’lim xizmatini ko‘rsatishni, Ta’lim oluvchi tasdiqlangan o‘quv reja bo‘yicha ta’lim olishni o‘z zimmalariga oladi. Ta’lim oluvchining ta’lim ma’lumotlari quyidagicha:
                <table>
                    <tbody>
                    <tr>
                        <td>Ta’lim bosqichi: </td>
                        <th><?=$selected->educationType->name;?></th>
                    </tr>
                    <tr>
                        <td>Ta’lim shakli: </td>
                        <th><?=$selected->educationForm->name;?></th>
                    </tr>
                    <tr>
                        <td>O‘qish muddati: </td>
                        <th><?=$selected->curriculum->education_period;?> yil <?= $selected->education_period ? '('.$selected->education_period.')' : "";?> </th>
                    </tr>
                    <tr>
                        <td>O‘quv kursi: </td>
                        <th><?=@$selected->level->name;?></th>
                    </tr>
                    <tr>
                        <td>Ta’lim yo‘nalishi: </td>
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
        <h5 class="text-center"><b>II. TA’LIM XIZMATINI KO‘RSATISH NARXI, TO‘LASH MUDDATI VA TARTIBI</b></h5>
        <div class="col-xs-12">
            <div class="no-shadow text-justify" style="margin-top: 2px;">
                2.1. Kontrakt bo‘yicha ta’lim oluvchini bir yillik o‘qitish uchun to‘lov <b><u><?= Yii::$app->formatter->asCurrency($selected->summa)?></u></b>ni tashkil etadi va quyidagi muddatlarda to’lash amalga oshiriladi:
                <div class="title"></div>
                <ul>
                    <li>
                        kuzgi semestr uchun kontrakt summasining 50 foizini to‘lash talabalikka tavsiya etilgan abiturientlar uchun 15 sentabrgacha yoki Davlat komissiyasi tomonidan belgilangan muddatgacha, ikkinchi va undan yuqori bosqich talabalari uchun 1 oktabrgacha yoki oliy ta'lim muassasasi tomonidan belgilangan muddatlarda amalga oshiriladi;
                    </li>
                    <li>
                        bahorgi semestr uchun kontrakt summasining qolgan 50 foizini to‘lash barcha talabalar uchun 1 martgacha bo‘lgan muddatda amalga oshiriladi.
                    </li>
                </ul>

            </div>
            <div class="no-shadow text-justify" style="margin-top: 2px;">
                2.2. Ushbu kontraktning 2.1. bandida ko‘zda tutilgan to‘lov muddatlarini o‘zgartirish, talabalikka tavsiya etilgan abiturientlar uchun - Davlat komissiyasining qarori asosida, ikkinchi va undan yuqori bosqich talabalar uchun Ta’lim oluvchining asosli sabablari ko‘rsatilgan holdagi yozma murojaatiga ko‘ra Ta’lim muassasasi kengashining qarori asosida qabul qilinadi va bir semestr uchun amal qiladi.
            </div>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
    <div class="clearfix"></div>
    <div class="row">
        <h5 class="text-center"><b>III. TOMONLARNING MAJBURIYATLARI</b></h5>
        <div class="col-xs-12">

            <div class="no-shadow text-justify" style="margin-top: 2px;">
                <b>3.1. Ta’lim muassasasi majburiyatlari:</b>
                <ul>
                    <li>O‘qitish uchun belgilangan to‘lov o‘z vaqtida amalga oshirilgandan so‘ng, Ta’lim oluvchini buyruq asosida talabalikka qabul qilish.</li>
                    <li>Ta’lim oluvchiga tanlangan ta’lim yo’nalishi yoki mutaxassislik bo‘yicha ishlab chiqilgan o‘quv rejasi va dasturlar asosida to‘laqonli o‘qitishni tashkil etish.</li>
                    <li>Ta’lim oluvchiga kutubxonalar, o‘quv zallari, sport va madaniy majmualardan foydalanish huquqini, shuningdek kafedralar, talabalar ilmiy jamiyatlari va to‘garaklarining tadqiqot ishlarida ishtirok etishini ta’minlash.</li>
                    <li>O‘zbekiston Respublikasida o‘qish, respublikani tark etish va O‘zbekiston hududi bo‘ylab harakatlanish uchun O‘zbekiston Respublikasi qonun hujjatlarida belgilangan tartibda kirish-chiqish vizalarini olishda, shuningdek vaqtincha yashash joyida ro‘yxatdan o‘tishda yordam ko‘rsatish.</li>
                    <li>Ta’lim muassasasining barcha talabalari uchun belgilangan tartibda sog‘liqni saqlash muassasasida tibbiy yordam ko‘rsatilishini ta’minlash.</li>
                    <li>Ta’lim oluvchining huquq va erkinliklari, qonuniy manfaatlari hamda ta’lim muassasasi Ustaviga muvofiq professor-o‘qituvchilar tomonidan o‘zlarining funksional vazifalarini to‘laqonli bajarishini ta’minlash.</li>

                </ul>
            </div>
            <div class="no-shadow text-justify" style="margin-top: 2px;">
                <b>3.2. Ta’lim oluvchining majburiyatlari:</b>
                <ul>
                    <li>Kontraktning 2.1. bandida belgilangan to‘lov summasini shu bandda ko‘rsatilgan muddatlarda to‘lash.</li>
                    <li>Ta’lim oluvchi o‘qish uchun belgilangan to‘lov miqdorini to‘laganlik to‘g‘risidagi bank tasdiqnomasi va kontraktning bir nusxasini o‘z vaqtida hujjatlarni rasmiylashtirish uchun Ta’lim muassasasiga topshirish.</li>
                    <li>Tahsil olayotgan ta’lim yo‘nalishining (mutaxassisligining) tegishli malaka tavsifnomasiga muvofiq kelajakda mustaqil faoliyat yuritishga zarur bo‘lgan barcha bilimlarni egallash, dars va mashg‘ulotlarga to‘liq qatnashish.</li>
                    <li>O‘zbekiston Respublikasi Konstitutsiyasi va O‘zbekiston Respublikasining boshqa qonun hujjatlariga rioya etish, chet el fuqarolari uchun O‘zbekiston Respublikasida yashash va harakatlanish qoidalariga rioya qilish, Ta’lim muassasasining ichki nizomlariga qa’tiy rioya qilish, professor-o‘qituvchilar va xodimlarga hurmat bilan qarash, Ta’lim muassasasi obro‘siga putur yetkazmaslik, moddiy bazasini asrash, ziyon keltirmaslik, ziyon keltirganda o‘rnini qoplash.</li>
                    <li>Ta’lim muassasasi tomonidan berilgan barcha topshiriqlarni bajarish, belgilangan muddatdan kechikmasdan Ta’lim muassasasiga etib kelish va o’quv jarayonlarida o‘z vaqtida qatnashish.</li>
                    <li>Vaqtincha yashash joyida viza va ro‘yxatga olish muddati tugashidan kamida ikki hafta oldin Ta'lim muassasasiga vizani amal qilish muddatini uzaytirish va vaqtincha yashash joyida ro‘yxatdan o‘tish uchun murojaat qilish. Agar ta’lim oluvchi Ta'lim muassasasiga vizaning amal qilish muddatini yoki vaqtincha yashash joyida ro‘yxatdan o‘tishni belgilangan muddatdan kechiktirib murojaat qilgan taqdirda, ta’lim oluvchi turli jarimalar qo‘llanilishi natijasida yuzaga keladigan barcha harajatlarni to‘lashni o‘z zimmasiga oladi.</li>
                </ul>
            </div>

        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
    <div class="clearfix"></div>
    <div class="row">
        <h5 class="text-center"><b>IV. TOMONLARNING HUQUQLARI</b></h5>
        <div class="col-xs-12">

            <div class="no-shadow text-justify" style="margin-top: 2px;">
                <b>4.1. Ta’lim muassasasi huquqlari:</b>
                <ul>
                    <li>O‘quv jarayonini mustaqil ravishda amalga oshirish, Ta’lim oluvchining oraliq va yakuniy nazoratlarni topshirish, qayta topshirish tartibi hamda vaqtlarini belgilash.</li>
                    <li>O‘zbekiston Respublikasi qonunlari, Ta’lim muassasasi nizomi hamda mahalliy normativ-huquqiy hujjatlarga muvofiq Ta’lim oluvchiga rag‘batlantiruvchi yoki intizomiy choralarni qo‘llash.</li>
                    <li>Agar Ta’lim oluvchi o‘quv yili semestrlarida yakuniy nazoratlarni topshirish, qayta topshirish natijalariga ko‘ra akademik qarzdor bo‘lib qolsa uni kursdan-kursga qoldirish huquqiga ega.</li>
                    <li>Ta’lim muassasasi Ta’lim oluvchining darslarga sababsiz qatnashmaslik, intizomni buzish, Ta’lim muassasasining ichki tartib qoidalariga amal qilmaganda, respublikaning normativ-huquqiy hujjatlarida nazarda tutilgan boshqa sabablarga ko‘ra hamda o‘qitish uchun belgilangan to‘lov o‘z vaqtida amalga oshirmaganda Ta’lim oluvchini talabalar safidan chetlashtirish huquqiga ega.</li>
                    <li>Ta'lim muassasasi Ta’lim oluvchining hayotini sug‘urta qilish va uning shaxsiy mulki harajatlarini, qarindoshlarini O‘zbekiston Respublikasiga taklif qilish, kirish vizalarini berish majburiyatini, Ta’lim oluvchi vafot etgan taqdirda uning jasadi va shaxsiy narsalarini vataniga yuborish harajatlarini o‘z zimmasiga olmaydi.</li>
                </ul>
            </div>
            <div class="no-shadow text-justify" style="margin-top: 2px;">
                <b>4.2. Ta’lim oluvchining huquqlari:</b>
                <ul>
                    <li></li>O‘quv yili uchun kontrakt summasini semestrlarga bo‘lmasdan bir yo‘la to‘liqligicha to‘lash mumkin.</li>
                    <li>Ta’lim oluvchi mazkur kontrakt bo‘yicha naqd pul yoki bank plastik kartasi orqali to‘lovni amalga oshirishi mumkin.</li>
                    <li>Professor-o‘qituvchilarning o‘z funksional vazifalarini bajarishidan yoki ta’lim muassasasidagi shart-sharoitlardan norozi bo‘lgan taqdirda Ta’lim muassasasi rahbariyatiga yozma shaklda murojaat qilish.</li>
                </ul>
            </div>

        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
    <div class="clearfix"></div>
    <div class="row">
        <h5 class="text-center">
            <b>
                V. KONTRAKTNING AMAL QILISH MUDDATI, UNGA O‘ZGARTIRISH VA QO‘SHIMCHALAR KIRITISH HAMDA BEKOR QILISH TARTIBI
            </b>
        </h5>
        <div class="col-xs-12">

            <p class="no-shadow text-justify" style="margin-top: 2px;">
                5.1. Ushbu kontrakt tomonlar imzolagandan so‘ng kuchga kiradi hamda ta’lim xizmatlarini taqdim etish o‘quv yili tugagunga qadar amalda bo‘ladi.
            </p>
            <p class="no-shadow text-justify" style="margin-top: 2px;">
                5.2. Ushbu kontrakt shartlariga ikkala tomon kelishuviga asosan tuzatish, o‘zgartirish va qo‘shimchalar kiritilishi mumkin. Kontraktga tuzatish, o‘zgartirish va qo‘shimchalar faqat yozma ravishda “Kontraktga qo‘shimcha bitim” tarzida kiritiladi va imzolanadi. Bitim kontraktning ajralmas qismi hisoblanadi.
            </p>
            <div class="no-shadow text-justify" style="margin-top: 2px;">
                5.3. Kontrakt quyidagi hollarda bekor qilinishi mumkin:
                <ul>
                    <li>Tomonlarning o‘zaro kelishuviga binoan.</li>
                    <li>Ta’lim oluvchi talabalar safidan chetlashtirganda Ta’lim muassasasi tashabbusi bilan bir tomonlama bekor qilinishi mumkin.</li>
                    <li>Tomonlardan biri o‘z majburiyatlarini bajarmaganda yoki lozim darajada bajarmaganda.</li>
                    <li>Uzrli sabablar bilan, Ta’lim oluvchining tashabbusiga ko‘ra.</li>
                    <li>Ta’lim muassasasi tugatilganda, ta’lim oluvchi bilan o‘zaro qayta hisob-kitob qilinadi.</li>
                </ul>
            </div>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
    <div class="clearfix"></div>
    <div class="row">
        <h5 class="text-center">
            <b>
                VI. YAKUNIY QOIDALAR VA NIZOLARNI HAL QILISH TARTIBI
            </b>
        </h5>
        <div class="col-xs-12">

            <p class="no-shadow text-justify" style="margin-top: 2px;">
                6.1. Ushbu kontraktni bajarish jarayonida kelib chiqishi mumkin bo‘lgan nizo va ziddiyatlar tomonlar o‘rtasida muzokaralar olib borish yo‘li bilan hal etiladi. Muzokaralar olib borish yo‘li bilan nizoni hal etish imkoniyati bo‘lmagan taqdirda, tomonlar nizolarni hal etish uchun amaldagi qonunchilikka muvofiq iqtisodiy sudga murojaat etishlari mumkin.
            </p>
            <p class="no-shadow text-justify" style="margin-top: 2px;">
                6.2. Kontrakt 2 (ikki) nusxada, tomonlarning har biri uchun bir nusxadan tuzildi va ikkala nusxa ham bir xil huquqiy kuchga ega.
            </p>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->


    <div class="clearfix"></div>
    <div class="row invoice-info">
        <div class="col-xs-12">
            <h5 class="text-center">
                <b>
                    VII. TOMONLARNING REKVIZITLARI VA IMZOLARI
                </b>
            </h5>
            <table style="width:100%">
                <tr>
                    <td width="48%">
                        <div class="col-sm-9 invoice-col">
                            <b>7.1. Ta’lim muassasasi:</b>
                            <address>
                                <?= $univer->name;?>
                            </address>
                            <br>
                            <b>Pochta manzili: </b>
                            <address>
                                <pre class ="bank_detail" ><?= $selected->mailing_address;?></pre>
                            </address>
                            <br>
                            <b>Bank rekvizitlari: </b>
                            <address>
                                <pre class ="bank_detail" ><?= $selected->bank_details;?></pre>
                            </address>


                        </div>

                    </td>
                    <td width="2%">
                    </td>
                    <td style="vertical-align: top;">

                        <div class="col-sm-12 invoice-col">
                            <b>7.2. Ta’lim oluvchi: </b>

                            <div class="no-shadow text-justify" style="margin-top: 2px;">
                                F.I.Sh.: <?= $selected->student->fullName?> <br>
                                Yashash manzili: <?= @$selected->student->province->name?>, <?= @$selected->student->district->name?>, <?= $selected->student->home_address?> <br>
                                Pasport ma’lumotlari: <?= $selected->student->passport_number;?> <br>
                                Talaba kodi: <?= $selected->student->uzasbo_id_number;?>  <br>
                                Telefon raqami:  <?= $selected->student->phone;?><br>
                                <br>
                            </div>
                            Ta’lim oluvchining imzosi: ________________________

                        </div>

                    </td>
                </tr>
                <tr>
                    <td width="48%">
                        <div class="col-sm-12 invoice-col">
                            <br>
                            <b>Ta’lim muassasasi rahbari:</b> ___________________
                            <br>
                            <?= $selected->rector;?>
                        </div>
                    </td>
                    <td width="2%">
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

    </div>

    <br>
    <?php if(isset($_GET['ready-pdf'])):?>
        <div class="row">
            <div class="col-xs-12">
                <p style="text-align:right">
                    <?php
                    $qrCode = (new QrCode(
                        Yii::getAlias('@frontendUrl').'/api/info/contract?'.'param='. $selected->id
                    ))
                        ->setSize(100)
                        ->setMargin(2)
                        ->writeDataUri();
                    echo Html::img($qrCode);
                    ?>
                </p>
            </div>
        </div>
    <?php endif;?>
    <!-- /.row -->
    <!-- this row will not appear when printing -->
    <?php if(!isset($_GET['ready-pdf'])):?>
        <div class="row no-print">
            <div class="col-xs-12">

                <?=
                Html::a(__('Generate PDF'),
                    [
                        'finance/student-contract',
                        'code' => $selected->id,
                        'ready-pdf' => 1
                    ], ['data-pjax' => 0, 'class'=>'btn btn-primary pull-left', 'style'=>'margin: 0 5px']);
                ?>
                <? /*if ($selected->filename) { ?>
                <a class="download-item"
                   href="<?= Url::current(['download' => 1]) ?>">
                    <i class="fa fa-paperclip "></i> <?= $selected->filename['name']; ?>
                </a>
                <?php
            }*/
                ?>
            </div>
        </div>
    <?php endif;?>
</section>