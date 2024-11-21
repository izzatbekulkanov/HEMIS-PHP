<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use backend\widgets\Select2Default;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\Semester;
use common\models\student\EStudentMeta;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;
use common\models\system\AdminRole;
/**
 * @var $this \backend\components\View
 * @var $model \common\models\curriculum\LessonPair
 * @var $university \common\models\curriculum\EducationYear
 */
$this->params['breadcrumbs'][] = $this->title;
\kartik\date\DatePickerAsset::registerBundle($this, '3.x');
?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>


<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">


            </div>
            <div class="box-body no-padding">
                <?php
                if($searchModel->_curriculum > 0 && $searchModel->_group > 0) {
                    ?>
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
                <?php } ?>
            </div>
        </div>
    </div>
</div>

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
        cursor: pointer;
    }
    .fc-day-number{
        float: none !important;
    }
    .fc .fc-row .fc-content-skeleton td{
        border: 1px solid #cccccc !important;
    }
    .fc-day-number:before{
        padding: 0 4px;
        float: left;
        font-weight: bold;

    }

</style>

<?php Pjax::end() ?>
