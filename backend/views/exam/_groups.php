<?php
/**
 * @var $searchModel EGroup
 * @var $model \common\models\curriculum\EExam
 * @var $this \backend\components\View
 */

use backend\widgets\GridView;
use backend\widgets\Select2Default;
use common\models\OptionProvider;
use common\models\structure\EDepartment;
use common\models\student\EGroup;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;

$user = $this->_user();
$type = Yii::$app->request->get('type');
$dataProvider = $model->getGroupsProvider(Yii::$app->request->get(), $this->_user());
$departments = EDepartment::getFaculties();
$showDepartment = !($user->role->isDeanRole() || $user->role->isTeacherRole());
?>
<div style="margin: -15px -15px -35px">
    <?php Pjax::begin(['id' => 'items-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <div class="row">
        <div class="col col-md-12 col-lg-12">
            <div class="box no-border ">
                <div class="box-header ">
                    <div class="row" id="data-grid-items-filters">
                        <?php $form = ActiveForm::begin(); ?>
                        <?php if ($showDepartment): ?>
                            <div class="col col-sm-6">
                                <?= $form->field($model, '_department')->widget(Select2Default::classname(), [
                                    'data' => $departments,
                                    'allowClear' => true,
                                    'hideSearch' => false,
                                    'placeholder' => __('Choose Faculty'),
                                ])->label(false) ?>
                            </div>
                        <?php endif; ?>
                        <div class="col col-sm-<?= $showDepartment ? 6 : 12 ?>">
                            <?= $form->field($model, 'search',
                                [
                                    'labelOptions' => ['class' => 'invisible'],
                                ]
                            )->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>

                <?= GridView::widget([
                    'id' => 'data-grid-items',
                    'dataProvider' => $dataProvider,
                    'layout' => $this->render('@backend/views/exam/_groups_layout.php'),
                    'columns' => [
                        [
                            'class' => 'yii\grid\CheckboxColumn',
                            'checkboxOptions' => function ($model, $key, $index, $grid) {
                                return [
                                    'data-text' => $model->name,
                                    'class' => 'item',
                                ];
                            }
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function (EGroup $data) {
                                return $data->name;
                            },
                        ],
                        [
                            'attribute' => '_department',
                            'format' => 'raw',
                            'value' => function (EGroup $data) {
                                return $data->department->name;
                            },
                        ],
                        [
                            'attribute' => '_education_type',
                            'value' => 'educationType.name',
                        ],

                        [
                            'attribute' => '_education_lang',
                            'value' => 'educationLang.name',
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
    <?php Pjax::end() ?>
</div>
<script type="text/javascript">
    function addSelected() {
        var selected = [];
        var options = [];
        $('#data-grid-items input.item[type="checkbox"]:checked').each(function (index, element) {
            selected.push(element.value);
            $(element).parent().parent().addClass('selected-item');
        })

        if (selected.length) {
            $.get('<?=linkTo(['exam/edit', 'id' => $model->id, 'add' => 1])?>&items=' + selected.join(','), function (data) {
                $.pjax.reload('#group-list');
            });
        }
    }
</script>
