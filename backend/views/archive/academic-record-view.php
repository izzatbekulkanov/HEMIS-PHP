<?php

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\archive\EAcademicRecord;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\Semester;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $this \backend\components\View
 * @var $model \common\models\structure\EDepartment
 * @var $university \common\models\structure\EUniversity
 */
$title = $this->title;
$this->title = __('View Records');
$this->params['breadcrumbs'][] = ['url' => ['archive/academic-record'], 'label' => $title];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(
    ['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]
) ?>

<div class="row">
    <div class="col col-md-12 col-lg-12">
        <div class="box box-default ">
            <div class="box-header bg-gray">
                <?php if ($this->_user()->role->code !== "teacher") { ?>
                    <div class="row" id="data-grid-filters">
                        <?php $form = ActiveForm::begin(); ?>
                        <div class="col col-md-2">
                            <?= $form->field($searchModel, '_education_year')->widget(
                                Select2Default::classname(),
                                [
                                    //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_education_year', 'educationYear.name'),
                                    'data' => ArrayHelper::map(
                                        EAcademicRecord::find()->select(['_education_year'])->distinct(true)->orderBy('_education_year')->all(),
                                        '_education_year',
                                        'educationYear.name'
                                    ),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?php
                            $curriculums = $searchModel->_education_year ? EAcademicRecord::find()->where(
                                ['e_academic_record._education_year' => $searchModel->_education_year]
                            )->select(['e_academic_record._education_year', 'e_academic_record._curriculum', 'e_curriculum.name'])->distinct(true)->joinWith('curriculum')->orderBy('e_curriculum.name')->all()
                                : [];
                            ?>
                            <?= $form->field($searchModel, '_curriculum')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => ArrayHelper::map(
                                        $curriculums,
                                        '_curriculum',
                                        'curriculum.name'
                                    ),
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                    'disabled' => empty($curriculums)
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-2">
                            <?php

                            ?>
                            <?= $form->field($searchModel, '_group')->widget(
                                Select2Default::classname(),
                                [
                                    'data' => $searchModel->_curriculum ? \common\models\student\EGroup::getOptions($searchModel->_curriculum) : [],
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                    'disabled' => !$searchModel->_curriculum
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col col-md-4">
                            <?php
                            $students = EAcademicRecord::find()->joinWith('student.meta')->select(
                                [
                                    'e_academic_record.id',
                                    'e_academic_record._curriculum',
                                    'e_academic_record._student',
                                    'e_student_meta._student',
                                    'e_student_meta._group',
                                    'e_academic_record._education_year',
                                    'e_student.second_name',
                                    'e_student.first_name',
                                    'e_student.third_name'
                                ]
                            )->distinct()->orderBy('e_student.second_name');
                            if ($searchModel->_education_year) {
                                $students->andWhere(
                                    ['e_academic_record._education_year' => $searchModel->_education_year]
                                );
                            }
                            if ($searchModel->_curriculum) {
                                $students->andWhere(['e_academic_record._curriculum' => $searchModel->_curriculum]);
                            }
                            if ($searchModel->_group) {
                                $students->andWhere(['e_student_meta._group' => $searchModel->_group]);
                            }
                            if (!$searchModel->_group) {
                                $studentOptions = [];
                            } else {
                                $studentOptions = ArrayHelper::map($students->all(), '_student', 'student.fullName');
                            }
                            ?>
                            <?= $form->field($searchModel, '_student')->widget(
                                Select2Default::classname(),
                                [
                                    //'data' => ArrayHelper::map(EPerformance::find()->where(['>=', '_final_exam_type', FinalExamType::FINAL_EXAM_TYPE_FIRST])->all(), '_education_year', 'educationYear.name'),
                                    'data' => $studentOptions,
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                    'disabled' => empty($studentOptions)
                                ]
                            )->label(false); ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                <?php } ?>
            </div>
            <div class="box-body no-padding">
                <?= GridView::widget(
                    [
                        'id' => 'data-grid',
                        'sticky' => '#sidebar',
                        'toggleAttribute' => 'active',
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            [
                                'class' => 'yii\grid\CheckboxColumn',
                            ],
                            /*[
                                'attribute' => '_student',
                                'value' => 'student.fullName',
                                // 'enableSorting' => true,
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a($data->student->fullName, ['archive/diploma', 'id' => $data->_student], ['data-pjax' => 0]);
                                },
                            ],*/
                            [
                                'attribute' => '_student',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return sprintf(
                                        '%s <p class="text-muted">%s</p>',
                                        $data->student->fullName,
                                        $data->curriculum->name
                                    );
                                },
                            ],
                            [
                                'attribute' => '_education_year',
                                'value' => 'educationYear.name',
                            ],
                            [
                                'attribute' => '_semester',
                                'value' => function ($data) {
                                    return @Semester::getByCurriculumSemester(
                                        @$data->_curriculum,
                                        @$data->_semester
                                    )->name;
                                },
                            ],
                            [
                                'attribute' => '_subject',
                                'value' => 'subject.name',
                            ],
                            [
                                'attribute' => 'total_acload',
                            ],
                            [
                                'attribute' => 'total_point',
                            ],
                            [
                                'attribute' => 'grade',
                            ],
                            /*                        [
                                                        'attribute'=>'_subject',
                                                        'value' => function (EPerformance $data) {
                                                            return $data->grade;
                                                        //return $data->group ? '<b>'.$data->group->curriculum->markingSystem->name.'</b>' : '';
                                                        }

                                                    ],*/

                        ],
                    ]
                ); ?>
            </div>
        </div>
    </div>


</div>


<script>
    var base_url = '<?= \Yii::$app->request->hostInfo; ?>';
</script>
<?php
$script = <<< JS
	$("#assign").click(function(){
		var keys = $('#data-grid').yiiGridView('getSelectedRows');
		$.post({
           url:  '/archive/to-record',
           data: {selection: keys },
           dataType:"json",
        });
	});
JS;
$this->registerJs($script);
?>

<?php Pjax::end() ?>
