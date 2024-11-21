<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use backend\widgets\MaskedInputDefault;
use backend\widgets\Select2Default;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\Semester;
use common\models\student\EStudentMeta;
use kartik\date\DatePickerAsset;
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
DatePickerAsset::registerBundle($this, '3.x');
?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>


<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin([
                        'action' => ['schedule'],
                        'method' => 'get',
                        'options' => [
                            'data-pjax' => false
                        ],
                    ]) ?>
                    <div class="col col-md-4">
                        <?php $faculty = $this->_user()->role->code == AdminRole::CODE_DEAN ? Yii::$app->user->identity->employee->deanFaculties->id : ""; ?>
                        <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::class, [
                            'data' => ECurriculum::getOptions($faculty),
                            'hideSearch' => false,
                            'options' => [
                                'id' => '_curriculum_search',
                            ]
                        ])->label(false); ?>
                    </div>

                    <div class="col col-md-2">
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                            'data' => EducationYear::getEducationYears(),
                            'options' => [
                                'id' => '_education_year_search',
                                'required' => true
                            ]
                        ])->label(false); ?>
                    </div>

                    <div class="col col-md-2">
                        <?php
                        $semesters = array();
                        if ($searchModel->_curriculum && $searchModel->_education_year) {
                            $semesters = Semester::getByCurriculumYear($searchModel->_curriculum, $searchModel->_education_year);
                        }
                        ?>
                        <?= $form->field($searchModel, '_semester')->widget(DepDrop::classname(), [
                            'data' => ArrayHelper::map($semesters, 'code', 'name'),
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options' => ['pluginOptions' => ['allowClear' => true,], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'id' => '_semester_search',
                                'placeholder' => __('-Choose-'),
                                'required' => true
                            ],
                            'pluginOptions' => [
                                'depends' => ['_curriculum_search', '_education_year_search'],
                                'url' => Url::to(['/ajax/get-semester-years']),
                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">
                        <?php
                        $groups = array();
                        if ($searchModel->_curriculum && $searchModel->_education_year && $searchModel->_semester) {
                            $groups = EStudentMeta::getContingentByCurriculumSemester($searchModel->_curriculum, $searchModel->_education_year, $searchModel->_semester);
                        }
                        ?>
                        <?= $form->field($searchModel, '_group')->widget(DepDrop::classname(), [
                            'data' => ArrayHelper::map($groups, '_group', 'group.name'),
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options' => ['pluginOptions' => ['allowClear' => true], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'id' => '_group_search',
                                'placeholder' => __('-Choose-'),
                                'required' => true
                            ],

                            'pluginOptions' => [
                                'depends' => ['_curriculum_search', '_education_year_search', '_semester_search'],
                                'url' => Url::to(['/ajax/get-group-semesters']),

                            ],
                        ])->label(false); ?>
                    </div>
                    <div class="col col-md-2">

                        <div class="form-group">
                            <?= Html::submitButton('<i class="fa fa-content"></i> ' . __('OK'), ['class' => 'btn btn-primary btn-flat', 'name' => 'ok']) ?>
                            <?php if ($searchModel->_group > 0 && count($time_tables) > 0) { ?>
                                <?= $this->getResourceLink(__('Generate'), ['curriculum/schedule-generate-weekly', 'curriculum' => $searchModel->_curriculum, 'semester' => $searchModel->_semester, 'group' => $searchModel->_group], ['class' => 'btn btn-success btn-flat', 'data-pjax' => '0',]) ?>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="hidden">

                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php
                if ($searchModel->_curriculum > 0 && $searchModel->_group > 0) {
                    ?>
                    <?= yii2fullcalendar\yii2fullcalendar::widget(array(
                        'events' => $time_tables,
                        'id' => 'calendar',

                        'clientOptions' => [
                            'editable' => false,
                            'draggable' => false,
                            'header' => [
                                'left' => 'prev,next',
                                'center' => 'title',
                                'right' => ' '
                            ],
                            //  'lazyFetching'=>true,
                            'monthNames' => [
                                'Январ', 'Феврал', 'Март', 'Апрел', 'Май', 'Июн', 'Июл', 'Август', 'Сентябр', 'Октябр', 'Ноябр', 'Декабр'
                            ],
                            'monthNamesShort' => ['Янв.', 'Фев.', 'Март', 'Апр.', 'Май', 'Июнь', 'Июль', 'Авг.', 'Сент.', 'Окт.', 'Ноя.', 'Дек.'],
                            'dayNames' => ["Якшанба", "Душанба", "Сешанба", "Чоршанба", "Пайшанба", "Жума", "Шанба"],
                            'dayNamesShort' => ["Якш", "Душанба", "Сешанба", "Чоршанба", "Пайшанба", "Жума", "Шанба"],
                            'buttonText' => [
                                //'prev'=> "&nbsp;&#9668;&nbsp;",
                                //'next'=> "&nbsp;&#9658;&nbsp;",
                                'prevYear' => "&nbsp;<<&nbsp;",
                                'nextYear' => "&nbsp;>>&nbsp;",
                                'today' => "Бугун",
                                'month' => "Ой",
                                'week' => "Ҳафта",
                                'day' => "Кун"
                            ],
                            'hiddenDays' => [0]  // hide Tuesdays and Thursdays
                        ],
                        'eventRender' => "function(event, element) {
                        element.find('.fc-title').append('<br> ' + event.nonstandard); 
                    }",
                        'eventClick' => "function(calEvent, jsEvent, view) {
                            $(this).css('border-color', 'red');
                            $.get('schedule-edit',{'id':calEvent.id}, function(data){
                                $('#modal').modal('show')
                                .find('#modalContent')
                                .html(data);
                            })
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
    $(function() {
        $(document).on('click','.fc-day-top', function(){
			var date = $(this).attr('data-date');
			var curriculum = $("#_curriculum_search").val();
			var education_year = $("#_education_year_search").val();
			var semester = $("#_semester_search").val();
			var group = $("#_group_search").val();
			
			$.get('schedule-create',{'date':date, 'curriculum':curriculum, 'education_year':education_year, 'semester':semester, 'group':group}, function(data){
				$('#modal').modal('show')
				.find('#modalContent')
				.html(data);
			});
			
		});
	
    });

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
        margin: 2px !important;

    }

    .fc-day-grid-event {
        background-color: #fff;
        color: #0B2C5F;

    }

    .fc-day-grid-event:hover {
        background-color: #fff;
        color: #0d6aad;

    }

    .fc-day-top {
        background: #3c8dbc !important;
        color: #ffffff;
        text-align: center !important;
        float: none !important;
        cursor: pointer;
    }

    .fc-day-number {
        float: none !important;
    }

    .fc .fc-row .fc-content-skeleton td {
        border: 1px solid #cccccc !important;

    }

    .fc-day-number:before {
        content: " + ";
        padding: 0 4px;
        float: left;
        font-weight: bold;

    }

</style>

<?php Pjax::end() ?>
