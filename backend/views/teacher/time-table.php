<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\curriculum\EducationYear;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Subject Schedule');
$this->params['breadcrumbs'][] = $this->title;

\kartik\select2\Select2Asset::registerBundle($this, '3.x');

?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin([
                'action' => ['time-table'],
                'method' => 'get',
                'options' => [
                    'data-pjax' => false
                ],
            ]) ?>
            <div class="col col-md-3">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                    'data' => EducationYear::getEducationYears(),
                    'options' => [
                        'id' => '_education_year_search',
                        'required' => true
                    ]
                ])->label(false); ?>
            </div>



            <div class="col col-md-2">

                <div class="form-group">
                    <?= Html::submitButton('<i class="fa fa-content"></i> ' . __('OK'), ['class' => 'btn btn-primary btn-flat', 'name' => 'ok']) ?>
                </div>
            </div>
            <div class="hidden">

            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="box-body no-padding">
    <?= yii2fullcalendar\yii2fullcalendar::widget(array(
        'events'=> $time_tables,
        'id' => 'calendar',

        'clientOptions' => [
            'editable' => false,
            'draggable' => false,
            'header'=> [
                'left'=> 'prev,next',
                'center'=> 'title',
                'right'=> ' '
            ],
            //  'lazyFetching'=>true,
            'monthNames' => [
                'Январ', 'Феврал', 'Март', 'Апрел','Май','Июн','Июл','Август','Сентябр','Октябр','Ноябр','Декабр'
            ],
            'monthNamesShort' => ['Янв.','Фев.','Март','Апр.','Май','Июнь','Июль','Авг.','Сент.','Окт.','Ноя.','Дек.'],
            'dayNames' => ["Якшанба","Душанба","Сешанба","Чоршанба","Пайшанба","Жума","Шанба"],
            'dayNamesShort' => ["Якш","Душанба","Сешанба","Чоршанба","Пайшанба","Жума","Шанба"],
            'buttonText' => [
                //'prev'=> "&nbsp;&#9668;&nbsp;",
                //'next'=> "&nbsp;&#9658;&nbsp;",
                'prevYear'=> "&nbsp;<<&nbsp;",
                'nextYear'=> "&nbsp;>>&nbsp;",
                'today'=> "Бугун",
                'month'=> "Ой",
                'week'=> "Ҳафта",
                'day'=> "Кун"
            ],
            'hiddenDays'=> [0]  // hide Tuesdays and Thursdays
        ],
        'eventRender'=> "function(event, element) {
                        element.find('.fc-title').append('<br> ' + event.nonstandard); 
                    }",
        'options' => [
            'lang' => 'ru',
        ],
    ));
    ?>
</div>
</div>
<?php
$this->registerJs('
    $("#sidebar").theiaStickySidebar({
        additionalMarginTop: 20,
        additionalMarginBottom: 20
    });
')
?>


<?php
$script = <<< JS
        $(document).on('click', '.fc-next-button,.fc-prev-button', function () {
             localStorage.setItem('savedMonth',$('#calendar').fullCalendar('getView').intervalStart._d);
        });
        if(localStorage.getItem('savedMonth')!=null){
            $('#calendar').fullCalendar('gotoDate',localStorage.getItem('savedMonth'));
        }

JS;

$this->registerJs($script);
?>
<style type="text/css">
    .fc-day-grid-event .fc-content {
        white-space: normal !important;
        margin: 2px  !important;

    }
    .fc-day-grid-event{
        background-color: #fff;
        color: #0B2C5F;

    }
    .fc-day-grid-event:hover{
        background-color: #fff;
        color: #0d6aad;

    }
    .fc-day-top {
        background: #3c8dbc !important;
        color:#ffffff;
        text-align: center !important;
        float: none !important;
    }
    .fc-day-number{
        float: none !important;
    }
    .fc .fc-row .fc-content-skeleton td{
        border: 1px solid #cccccc !important;

    }

</style>
<?php Pjax::end() ?>
