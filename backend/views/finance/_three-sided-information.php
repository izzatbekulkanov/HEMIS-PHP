<?php
use Da\QrCode\QrCode;
use yii\helpers\Html;
use yii\helpers\Url;
?>
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
<section class="invoice">
    <!-- title row -->
    <div class="row">
        <div class="col-xs-12">
            <h4 class="text-center">
                To‘lov-kontrakt (uch tomonlama) asosida mutaxassis tayyorlashga
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
                O‘zbekiston Respublikasi Prezidentining 2019 yil 17 iyundagi PQ-4359 son Qarori, Vazirlar Mahkamasining
                2017 yil 20 iyundagi 393-son Qarori hamda unga
                2019 yil 27 apreldagi 360-son Qaror bilan kiritilgan o‘zgartirish va qo‘shimchalarga, Oliy va o‘rta
                maxsus ta’lim vazirligining 2012 yil 28 dekabrdagi 508-son buyrug‘i bilan tasdiqlangan,
                Adliya vazirligida 2013 yil 26 fevralda 2431-son bilan davlat ro‘yxatidan o‘tkazilgan
                “Oliy va o‘rta maxsus, kasb-hunar ta’limi muassasalarida o‘qitishning to‘lov-kontrakt shakli va
                undan tushgan mablag‘larni taqsimlash tartibi to‘g‘risida”gi Nizomga muvofiq,
                <b><?= strtoupper($univer->name);?></b> (keyingi o‘rinlarda “Ta’lim muassasasi”) nomidan rektor (direktor)
                <b><?= $selected->rector;?></b> bir tomondan,
                <h5 style="text-align: center;border-top:1px solid #ccc; margin:18px 0 0 !important; ">(buyurtmachi)</h5>
                <h5 style="line-height: normal;text-align: justify">
                (keyingi o‘rinlarda “Buyurtmachi”) ikkinchi tomondan, <b><?= $selected->student->fullName;?></b>
                (keyingi o‘rinlarda “Ta’lim oluvchi”) uchinchi tomondan, birgalikda “Tomonlar” deb ataladigan shaxslar mazkur kontraktni quyidagicha tuzdilar:
                </h5>
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
                1.1. “Ta’lim muassasasi” ta’lim xizmatini ko‘rsatishni, “Buyurtmachi” o‘qish uchun belgilangan to‘lovni o‘z vaqtida amalga oshirishni, “Ta’lim oluvchi” tasdiqlangan o‘quv reja bo‘yicha darslarga to‘liq qatnashish va ta’lim olishni o‘z zimmalariga oladi:
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
            <div class="no-shadow text-justify" style="margin-top: 2px;">
                1.2. “Ta’lim muassasasi”ga o‘qishga qabul qilingan “Ta’lim oluvchi”lar O‘zbekiston Respublikasining “Ta’lim to‘g‘risida”gi Qonuni va davlat ta’lim standartlarga muvofiq ishlab chiqilgan o‘quv rejalar va fan dasturlari asosida ta’lim oladilar.
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
                2.1. “Ta’lim muassasasi”da o‘qish davrida ta’lim xizmatini ko‘rsatish narxi Respublikada belgilangan Mehnatga haq to‘lashning eng kam miqdoriga bog‘liq holda hisoblanadi. O‘qitishning kunduzgi ta’lim shaklida ta’lim xizmati ko‘rsatish narxining “stipendiyali” va “stipendiyasiz” shakllari mavjud bo‘lib, ta’lim oluvchi ushbu imkoniyatni tanlash uchun o‘quv yili boshida rektor nomiga yozma ariza bilan murojaat etadi.
            </div>
            <div class="no-shadow text-justify" style="margin-top: 2px;">
                2.2. Ushbu kontrakt bo‘yicha ta’lim oluvchini bir yillik o‘qitish uchun to‘lov <b><u><?= Yii::$app->formatter->asCurrency($selected->summa)?></u></b>ni (<b><?= $selected->contractSummaType->name;?></b>) tashkil etadi va quyidagi muddatlarda amalga oshiriladi:
                <div class="title">Semestrlarga bo‘lib to‘langanda quyidagi muddatlarda:</div>
                <ul>
                    <li>
                        kuzgi semestr (birinchi yarim yillik): talabalikka tavsiya etilgan abiturientlar uchun <?= Yii::$app->formatter->asDate($selected->start_date, 'php:d');?> <?= Yii::$app->formatter->asDate($selected->start_date, 'php:F');?>gacha yoki Davlat komissiyasi tomonidan belgilangan muddatgacha, ikkinchi va undan yuqori bosqich talabalari uchun <?= Yii::$app->formatter->asDate($selected->end_date, 'php:d');?> <?= Yii::$app->formatter->asDate($selected->end_date, 'php:F');?>gacha;
                    </li>
                    <li>
                        bahorgi semestr (ikkinchi yarim yillik): barcha talabalar uchun 1 martgacha (oyma-oy to‘langan hollarda ham).
                    </li>
                </ul>

                <div class="title">Choraklarga bo‘lib to‘langanda quyidagi muddatlarda:</div>
                <ul>
                    <li>
                        belgilangan to‘lov miqdorining kamida 25 foizini talabalikka tavsiya etilgan abiturientlar uchun <?= Yii::$app->formatter->asDate($selected->start_date, 'php:d');?> <?= Yii::$app->formatter->asDate($selected->start_date, 'php:F');?>gacha yoki Davlat komissiyasi tomonidan belgilangan muddatgacha, ikkinchi va undan yuqori bosqich talabalar uchun <?= Yii::$app->formatter->asDate($selected->end_date, 'php:d');?> <?= Yii::$app->formatter->asDate($selected->end_date, 'php:F');?>gacha;
                    </li>
                    <li>
                        belgilangan to‘lov miqdorining kamida 50 foizini 1 yanvargacha, 75 foizini 1 aprelgacha va 100 foizini 1 iyulgacha.
                    </li>
                </ul>

            </div>
            <div class="no-shadow text-justify" style="margin-top: 2px;">
                2.3. Ushbu kontraktning 2.2. bandida ko‘zda tutilgan to‘lov muddatlarini o‘zgartirish, talabalikka tavsiya etilgan abiturientlar uchun - Davlat komissiyasining qarori asosida, ikkinchi va undan yuqori bosqich talabalar uchun - “Buyurtmachi”ning asosli sabablari ko‘rsatilgan holdagi yozma murojaatiga ko‘ra “Ta’lim muassasasi” kengashining qarori asosida qabul qilinadi va bir semestr uchun amal qiladi.
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
                    <li>O‘qitish uchun belgilangan to‘lov o‘z vaqtida amalga oshirgandan so‘ng, “Ta’lim oluvchi”ni buyruq asosida talabalikka qabul qilish.</li>
                    <li>Ta’lim oluvchiga o‘qishi uchun O‘zbekiston Respublikasining “Ta’lim to‘g‘risida”gi Qonuni va “Ta’lim muassasasi” Ustavida nazarda tutilgan zarur shart-sharoitlarga muvofiq sharoitlarni yaratib berish.</li>
                    <li>Ta’lim oluvchining huquq va erkinliklari, qonuniy manfaatlari hamda ta’lim muassasasi Ustaviga muvofiq professor-o‘qituvchilar tomonidan o‘zlarining funksional vazifalarini to‘laqonli bajarishini ta’minlash.</li>
                    <li>Ta’lim oluvchini tahsil olayotgan ta’lim yo‘nalishi (mutaxassisligi) bo‘yicha tasdiqlangan o‘quv rejasi va dasturlariga muvofiq davlat ta’lim standarti talablari darajasida tayyorlash.</li>
                    <li>O‘quv yili boshlanishida ta’lim oluvchini yangi o‘quv yili uchun belgilangan to‘lov miqdori to‘g‘risida o‘quv jarayoni boshlanishidan oldin xabardor qilish.</li>
                    <li>Respublikada belgilangan Mehnatga haq to‘lashning eng kam miqdori yoki tariflar o‘zgarishi natijasida o‘qitish uchun belgilangan to‘lov miqdori o‘zgargan taqdirda ta’lim oluvchiga ta’limning qolgan muddati uchun to‘lov miqdori haqida xabar berish.</li>
                </ul>
            </div>
            <div class="no-shadow text-justify" style="margin-top: 2px;">
                <b>3.2. Buyurtmachi majburiyatlari:</b>
                <ul>
                    <li>Kontraktning 2.2. bandida belgilangan to‘lov summasini shu bandda ko‘rsatilgan muddatlarda to‘lab borish.</li>
                    <li>Respublikada belgilangan Mehnatga haq to‘lashning eng kam miqdori yoki tariflar o‘zgarishi natijasida o‘qitish uchun belgilangan to‘lov miqdori o‘zgargan taqdirda, o‘qishning qolgan muddati uchun ta’lim muassasasiga haq to‘lash bo‘yicha bir oy muddat ichida kontraktga qo‘shimcha bitim rasmiylashtirish va to‘lov farqini to‘lash.</li>
                </ul>
            </div>

            <div class="no-shadow text-justify" style="margin-top: 2px;">
                <b>3.3. Ta’lim oluvchining majburiyatlari:</b>
                <ul>
                    <li>Ta’lim oluvchi o‘qitish uchun belgilangan to‘lov miqdori “Buyurtmachi” tomonidan to‘laganlik to‘g‘risidagi bank tasdiqnomasi va kontraktning bir nusxasini o‘z vaqtida hujjatlarni rasmiylashtirish uchun ta’lim muassasasiga topshirish.</li>
                    <li>Tahsil olayotgan ta’lim yo‘nalishining (mutaxassisligining) tegishli malaka tavsifnomasiga muvofiq kelajakda mustaqil faoliyat yuritishga zarur bo‘lgan barcha bilimlarni egallash, dars va mashg‘ulotlarga to‘liq qatnashish.</li>
                    <li>Ta’lim muassasasi va talabalar turar joyining ichki nizomlariga qa’tiy rioya qilish, professor-o‘qituvchilar va xodimlarga hurmat bilan qarash, “Ta’lim muassasasi” obro‘siga putur yetkazadigan harakatlar qilmaslik, moddiy bazasini asrash, ziyon keltirmaslik, ziyon keltirganda o‘rnini qoplash.</li>
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
                    <li>O‘quv jarayonini mustaqil ravishda amalga oshirish, “Ta’lim oluvchi”ning oraliq va yakuniy nazoratlarni topshirish, qayta topshirish tartibi hamda vaqtlarini belgilash.</li>
                    <li>O‘zbekiston Respublikasi qonunlari, “Ta’lim muassasasi” nizomi hamda mahalliy normativ-huquqiy hujjatlarga muvofiq “Ta’lim oluvchi”ga rag‘batlantiruvchi yoki intizomiy choralarni qo‘llash.</li>
                    <li>Agar “Ta’lim oluvchi” o‘quv yili semestrlarida yakuniy nazoratlarni topshirish, qayta topshirish natijalariga ko‘ra akademik qarzdor bo‘lib qolsa uni kursdan-kursga qoldirish huquqiga ega.</li>
                    <li>“Ta’lim muassasasi” “Ta’lim oluvchi”ning qobiliyati, darslarga sababsiz qatnashmaslik, intizomni buzish, “Ta’lim muassasasi”ning ichki tartib qoidalariga amal qilmaganda, respublikaning normativ-huquqiy hujjatlarida nazarda tutilgan boshqa sabablarga ko‘ra hamda “Buyurtmachi” tomonidan o‘qitish uchun belgilangan to‘lov o‘z vaqtida amalga oshirilmaganda “Ta’lim oluvchi”ni talabalar safidan chetlashtirish huquqiga ega.</li>
                </ul>
            </div>
            <div class="no-shadow text-justify" style="margin-top: 2px;">
                <b>4.2. Buyurtmachi huquqlari:</b>
                <ul>
                    <li>Ta’lim muassasasining o‘quv rejasi va dasturlari bilan tanishish hamda ularni ishlab chiqarish talablariga muvofiqlashtirish yuzasidan o‘z takliflarini berish.</li>
                    <li>O‘quv yili uchun kontrakt summasini semestrlarga yoki choraklarga bo‘lmasdan bir yo‘la to‘liqligicha to‘lash.</li>
                    <li>Buyurtmachi mazkur kontrakt bo‘yicha pul ko‘chirish, naqd pul, bank plastik kartasi, bankdagi omonat hisob raqami orqali, ish joyidan arizasiga asosan oylik maoshini o‘tkazishi yoki banklardan ta’lim krediti olish orqali to‘lovni amalga oshirishi mumkin.</li>
                </ul>
            </div>
            <div class="no-shadow text-justify" style="margin-top: 2px;">
                <b>4.3. Ta’lim oluvchining huquqlari:</b>
                <ul>
                    <li>Professor-o‘qituvchilarning o‘z funksional vazifalarini bajarishidan yoki ta’lim muassasasidagi shart-sharoitlardan norozi bo‘lgan taqdirda ta’lim muassasasi rahbariyatiga yozma shaklda murojaat qilish.</li>
                    <li>“Ta’lim oluvchi” o‘quv rejasidagi amaliyotlarni o‘tkazish hamda bitirgandan so‘ng ishga joylashish masalalarini buyurtmachi bilan o‘zaro kelishgan holda hal qilish huquqiga ega.</li>
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
                V. KONTRAKTNING AMAL QILISH MUDDATI, UNGA O‘ZGARTIRISH VA QO‘SHIMCHALAR KIRITISH HAMDA BEKOR QILISh TARTIBI
            </b>
        </h5>
        <div class="col-xs-12">

            <p class="no-shadow text-justify" style="margin-top: 2px;">
                5.1. Ushbu kontrakt uch tomonlama imzolangandan so‘ng kuchga kiradi hamda ta’lim xizmatlarini taqdim etish o‘quv yili tugagunga qadar amalda bo‘ladi.
            </p>
            <p class="no-shadow text-justify" style="margin-top: 2px;">
                5.2. Ushbu kontrakt shartlariga tomonlar kelishuviga asosan tuzatish, o‘zgartirish va qo‘shimchalar kiritilishi mumkin.
            </p>
            <p class="no-shadow text-justify" style="margin-top: 2px;">
                5.3. Kontraktga tuzatish, o‘zgartirish va qo‘shimchalar faqat yozma ravishda “Kontraktga qo‘shimcha bitim” tarzida kiritiladi va imzolanadi.
            </p>
            <div class="no-shadow text-justify" style="margin-top: 2px;">
                5.4. Kontrakt quyidagi hollarda bekor qilinishi mumkin:
            <ul>
                <li>Tomonlarning o‘zaro kelishuviga binoan.</li>
                <li>“Ta’lim oluvchi” talabalar safidan chetlashtirganda “Ta’lim muassasasi” tashabbusi bilan bir tomonlama bekor qilinishi mumkin.</li>
                <li>Tomonlardan biri o‘z majburiyatlarini bajarmaganda yoki lozim darajada bajarmaganda.</li>
                <li>Uzrli sabablar bilan, “Ta’lim oluvchi”ning tashabbusiga ko‘ra.</li>
                <li>Ta’lim muassasasi tugatilganda, buyurtmachi bilan o‘zaro qayta hisob-kitob qilinadi.</li>
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
                6.1. Ushbu kontraktni bajarish jarayonida kelib chiqishi mumkin bo‘lgan nizo va ziddiyatlar tomonlar o‘rtasida muzokaralar olib borish yo‘li bilan hal etiladi.
            </p>
            <p class="no-shadow text-justify" style="margin-top: 2px;">
                6.2. Muzokaralar olib borish yo‘li bilan nizoni hal etish imkoniyati bo‘lmagan taqdirda, tomonlar nizolarni hal etish uchun amaldagi qonunchilikka muvofiq iqtisodiy sudga murojaat etishlari mumkin.
            </p>
            <p class="no-shadow text-justify" style="margin-top: 2px;">
                6.3. “Ta’lim muassasasi” axborotlar va xabarnomalarni internetdagi veb-saytida, axborot tizimida yoki e’lonlar taxtasida e’lon joylashtirish orqali xabar berishi mumkin.
            </p>
            <p class="no-shadow text-justify" style="margin-top: 2px;">
                6.4. Kontrakt 3 (uch) nusxada, tomonlarning har biri uchun bir nusxadan tuzildi va uchala nusxa ham bir xil huquqiy kuchga ega.
            </p>
            <p class="no-shadow text-justify" style="margin-top: 2px;">
                6.5. Ushbu kontraktga qo‘shimcha bitim kiritilgan taqdirda ushbu barcha kiritilgan qo‘shimcha bitimlar kontraktning ajralmas qismi hisoblanadi.
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
                        <div class="col-sm-6 invoice-col">
                            <b>7.2. Buyurtmachi:</b>
                            <address>
                                ___________________________________________________
                                ___________________________________________________
                                ___________________________________________________
                                ___________________________________________________
                                ___________________________________________________
                                ___________________________________________________
                                ___________________________________________________
                                ___________________________________________________
                                ___________________________________________________
                            </address>
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
                            <br>
                            ___________________________________________________
                        <br>           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;            (imzo)
                        </div>
                    </td>
                </tr>
            </table>


            <!-- /.col -->


        </div>
        <!-- /.col -->
        <div class="col-xs-12">

            <br>
        </div>






        <!-- /.col -->
    </div>
    <div class="row invoice-info">
        <div class="col-xs-12">

            <table>
                <tr>
                    <td width="100%">
                        <div class="col-sm-12 invoice-col">
                            <p>
                                <b>
                                    7.3. Ta’lim oluvchi:
                                </b>
                            </p>
                            <address>
                                F.I.SH.: <?= $selected->student->fullName?> <br>
                                Yashash manzili: <?= @$selected->student->province->name?>, <?= @$selected->student->district->name?>, <?= $selected->student->home_address?> <br>
                                Pasport ma’lumotlari: <?= $selected->student->passport_number;?> <br>
                                Talaba kodi: <?= $selected->student->uzasbo_id_number;?>  <br>
                                Telefon raqami:  <?= $selected->student->phone;?><br>
                                <br>

                                Ta’lim oluvchining imzosi: ____________________

                            </address>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

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
                <?/* if ($selected->filename) { ?>
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