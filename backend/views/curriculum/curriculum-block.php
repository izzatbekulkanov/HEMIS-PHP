<?php
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\system\classifier\SubjectBlock;
use common\models\curriculum\ECurriculum;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii2mod\chosen\ChosenSelect;
use kartik\depdrop\DepDrop;
use common\models\system\AdminRole;

//$this->title = $model->curriculum->name;
$this->params['breadcrumbs'][] = ['url' => ['curriculum/curriculum-block'], 'label' => __('List Curriculum Unit')];
//$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="row">

    <div class="col col-md-8 col-lg-8">
        <div class="box box-default ">

            <div class="box-header bg-gray">
                <div class="row" id="data-grid-filters">
                    <?php $form = ActiveForm::begin(); ?>

                    <div class="col col-md-8">
                        <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                            'data' => ECurriculum::getOptions($faculty),
                            'allowClear' => true,
                            'hideSearch' => false,
                        ])->label(false) ?>

                    </div>


                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="box-body no-padding">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'sortable' => true,
                    'sticky' => '#sidebar',
                    'toggleAttribute' => 'active',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'code',
                            'format' => 'raw',
                            'value' => function ($data) use ($model) {
                                return Html::a($data->code, ['curriculum/curriculum-block', 'code' => $data->id], []);
                            },
                        ],
                        [
                            'attribute'=>'_subject_block',
                            'value' => 'subjectBlock.name',
                        ],
                        [
                            'attribute'=>'_curriculum',
                            'value' => 'curriculum.name',
                        ],


                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <div class="col col-md-4" id="sidebar">
        <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['data-pjax' => 0]]); ?>
        <div class="box box-default ">
            <?php //echo $form->errorSummary($model)?>
            <div class="box-body">

                <?= $form->field($model, '_curriculum')->widget(Select2Default::classname(), [
                    'data' => ECurriculum::getOptions($faculty),
                    'allowClear' => true,
                    'hideSearch' => false,
                    'options' => [
                        'id'   => '_curriculum',
                    ],
                ]) ?>

                <?php
                $subject_block = array();
                if($model->_curriculum){
                    $curriculum = ECurriculum::findOne($model->_curriculum);
                    if ($curriculum === null) {
                        throw new NotFoundHttpException('The requested page does not exist.');
                    }
                    $subject_block = ArrayHelper::map(SubjectBlock::find()->where('_parent=:code AND code!=:id AND active=:active', [':code' => $curriculum->_education_type, ':id' => $curriculum->_education_type, ':active'=>true])->all(), 'code', 'name');
                }
                ?>
                <?= $form->field($model, '_subject_block')->widget(DepDrop::classname(), [
                    'data' => $subject_block,
                    'language' => 'en',
                    'type' => DepDrop::TYPE_SELECT2,
                    'pluginLoading' => false,
                    'select2Options'=>['pluginOptions'=>['allowClear'=>true, ], 'theme' => Select2::THEME_DEFAULT],
                    'options' => [
                        'placeholder' => __('-Choose-'),
                        'id' => '_subject_block',
                    ],
                    'pluginOptions' => [
                        //'initialize' => true,
                        'depends'=>['_curriculum'],
                        'placeholder' => __('-Choose-'),
                        'url'=>Url::to(['/ajax/get-subject-block']),
                    ],
                ])?>


                <?//= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label() ?>
            </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Cancel'), ['curriculum/curriculum-block'], ['class' => 'btn btn-default btn-flat']) ?>
                    <?= $this->getResourceLink(__('Delete'), ['curriculum/curriculum-block', 'code' => $model->id, 'delete' => 1], ['class' => 'btn btn-danger btn-flat btn-delete', 'data-pjax' => 0]) ?>
                <?php else: ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php Pjax::end() ?>
