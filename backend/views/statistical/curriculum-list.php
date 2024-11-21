<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use common\models\structure\EDepartment;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use common\models\curriculum\Semester;
use common\models\curriculum\ESubjectResource;
use common\models\curriculum\EducationYear;
/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = __('Curriculum Resources');
$this->params['breadcrumbs'][] = $this->title;


?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>

            <?php if ($this->_user()->role->code != AdminRole::CODE_DEAN) { ?>
            <div class="col col-md-4">
                <?= $form->field($searchModel, '_department')->widget(Select2Default::class, [
                    'data' => EDepartment::getFaculties(),
                    'placeholder' => __('-Choose faculty-'),
                    'hideSearch' => false,
                ])->label(false) ?>
            </div>
            <?php } ?>
            <div class="col col-md-4">
                <?= $form->field($searchModel, '_education_year')->widget(Select2Default::class, [
                    'data' => EducationYear::getEducationYears(),
                    //'placeholder' => __('-Choose faculty-'),
                    'hideSearch' => false,
                ])->label(false) ?>
            </div>
            <div class="col col-md-4">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by curriculum Name / Code')])->label(false) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
	<?= GridView::widget([
        'id' => 'data-grid',
		'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->name, ['statistical/resource-info', 'id' => $data->id], ['data-pjax' => 0]);
                },
            ],
            [
                'attribute'=>'_specialty_id',
                'value' => 'specialty.name',
            ],
			[
				'attribute'=>'_department',
				'value' => 'department.name',
                'visible' => ($this->_user()->role->code != AdminRole::CODE_DEAN)
			],
            [
                'attribute'=>'_education_year',
                'value' => 'educationYear.name',
            ],
			[
				'attribute'=>'_education_type',
				'value' => 'educationType.name',
			],
            [
                'attribute'=>'_education_form',
                'value' => 'educationForm.name',
            ],
            [
                'attribute'=>'count_lesson',
                'header' => __('Semesters [resources]'),
                'format' => 'raw',
                'value' => function ($data) {
                    $result="";
                    $curriculum_semesters = Semester::getSemesterByCurriculum($data->id);
                    foreach ($curriculum_semesters as $key=>$item){
                        $lessons = ESubjectResource::getResourceBySemester($data->id, $item->code);
                        if($lessons > 0)
                            $result .= '<span class="badge bg-green"> №'. ($item->name). ' ['.$lessons.'] '. '  '."</span>";
                        else
                            $result .= '<span class="badge bg-red"> №'. ($item->name). ' ['.$lessons.'] '. '  '."</span>";
                    }
                    return $result;
                },

            ],

        ],
    ]); ?>
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
