<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\system\classifier\EducationWeekType;
use common\models\system\classifier\Course;
use common\models\system\classifier\SubjectGroup;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\Semester;
use kartik\select2\Select2;
use backend\widgets\Select2Default;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;

$this->title = $curriculum->name;
$this->params['breadcrumbs'][] = ['url' => ['statistical/by-resource'], 'label' => __('List Curriculum')];
$this->params['breadcrumbs'][] = $this->title;


?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            
			<div class="box-header bg-gray">
				<div class="row" id="data-grid-filters">
					<?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-3">
                        <?= $form->field($searchModel, '_semester')->widget(Select2Default::class, [
                            'data' => ArrayHelper::map(Semester::getSemesterByCurriculum($curriculum->id), 'code', 'name'),
                            'placeholder' => __('-Choose Semester-'),
                            'options' => [
                                'id' => '_search_semester',
                            ]
                        ])->label(false); ?>
                    </div>

                    <div class="col col-md-3">
                        <?php
                        $subjects = array();
                        if($searchModel->_semester){
                            $subjects = ArrayHelper::map(ECurriculumSubject::find()->where('_curriculum=:curriculum AND _semester=:semester AND active=:active', [':curriculum' => $curriculum->id, ':semester' => $searchModel->_semester, ':active'=>true])->all(), '_subject', 'subject.name');
                        }
                        ?>
                        <?= $form->field($searchModel, '_subject')->widget(DepDrop::classname(), [
                            'data' => $subjects,
                            'language' => 'en',
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options'=>['pluginOptions'=>['allowClear'=>true, ], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'placeholder' => __('-Choose Subject-'),
                                'id' => '_search_subject',
                            ],
                            'pluginOptions' => [
                                //'initialize' => true,
                                'depends'=>['_curriculum', '_search_semester'],
                                'placeholder' => __('-Choose Subject-'),
                                'url'=>Url::to(['/ajax/get-semester-subject']),
                            ],
                        ])->label(false)?>
					</div>
                    <div class="col col-md-3">
                        <?php
                        $trainings = array();
                        if($searchModel->_subject){
                            $trainings = ArrayHelper::map(ECurriculumSubjectDetail::find()->where('_curriculum=:curriculum AND _semester=:semester AND _subject=:subject AND active=:active', [':curriculum' => $curriculum->id, ':semester' => $searchModel->_semester, ':subject' => $searchModel->_subject, ':active'=>true])->all(), '_training_type', 'trainingType.name');
                        }
                        ?>
                        <?= $form->field($searchModel, '_training_type')->widget(DepDrop::classname(), [
                            'data' => $trainings,
                            'language' => 'en',
                            'type' => DepDrop::TYPE_SELECT2,
                            'pluginLoading' => false,
                            'select2Options'=>['pluginOptions'=>['allowClear'=>true, ], 'theme' => Select2::THEME_DEFAULT],
                            'options' => [
                                'placeholder' => __('-Choose Training Type-'),
                                'id' => '_search_training_type',
                            ],
                            'pluginOptions' => [
                                //'initialize' => true,
                                'depends'=>['_curriculum', '_search_semester', '_search_subject'],
                                'placeholder' => __('-Choose Training Type-'),
                                'url'=>Url::to(['/ajax/get-subject-training']),
                            ],
                        ])->label(false)?>
                        <?/*= $form->field($searchModel, '_training_type')->widget(Select2Default::class, [
                            'data' => $trainings,
                            'placeholder' => __('-Choose Employee-'),
                            'options' => [
                                'id' => '_search_training_type',
                            ]
                        ])->label(false); */?>
                    </div>

                    <div class="col col-md-3">
                        <?php
                        $employees = array();
                        if($searchModel->_training_type && $searchModel->_subject){
                            $employees = ArrayHelper::map(ESubjectSchedule::getTeachersByCurriculumSemesterSubjectTraining($curriculum->id, $searchModel->_semester, $searchModel->_subject, $searchModel->_training_type), '_employee', 'employee.fullName');
                        }
                        ?>
                        <?= $form->field($searchModel, '_employee')->widget(Select2Default::class, [
                            'data' => $employees,
                            'placeholder' => __('-Choose Employee-'),
                            'options' => [
                                'id' => '_search_employee',
                            ]
                        ])->label(false); ?>
                    </div>

					<?php ActiveForm::end(); ?>
				</div>
			</div>
	
            <div class="box-body no-padding">
               <?= GridView::widget([
                    'id' => 'data-grid',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        /*[
                            'attribute' => 'position',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a($data->position, ['curriculum/subject-topic', 'id' => $data->_curriculum, 'code'=>$data->id], ['data-pjax' => 0]);
                            },
                        ],*/
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($data) {
                                @$link = "";
                                if (@$data->filename){
                                    if(is_array(@$data->filename)){
                                        foreach (@$data->filename as $file) {
                                            $link .= Html::a (@$file['name']. ' ('.Yii::$app->formatter->asShortSize(@$file['size']).')', @$file['base_url'] . '/' . @$file['path'], ['data-pjax' => 0]). '; ';
                                        }
                                    }

                                }
                                //return Html::a($data->name . $size, ['curriculum/subject-topic', 'id' => $data->_curriculum, 'code'=>$data->id], ['data-pjax' => 0]);
                                return @$data->name . '<br>'.@$link;
                            },
                        ],
                        [
                            'attribute'=>'_semester',
                            'value' => 'semester.name',
                        ],
                        [
                            'attribute'=>'_subject',
                            'value' => 'subject.name',
                        ],
                        [
                            'attribute'=>'_training_type',
                            'value' => 'trainingType.name',
                        ],
                        [
                            'attribute'=>'_language',
                            'value' => 'language.name',
                        ],
                        [
                            'attribute'=>'_employee',
                            'value' => 'employee.fullName',
                        ],

                    ],
                ]); ?>
            </div>
        </div>
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

<?php Pjax::end() ?>

