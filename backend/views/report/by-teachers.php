<?php
use common\models\structure\EDepartment;
use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\ArrayHelper;use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use backend\widgets\Select2Default;
use common\models\system\SystemLog;
/* @var $this \backend\components\View */
/* @var $dataProviderReport yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <div class="box-header bg-gray">
        <div class="row" id="data-grid-filters">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col col-md-4">
                <?= $form->field($searchModel, '_faculty')->widget(
                    Select2Default::classname(),
                    [
                        'data' => EDepartment::getFaculties(),
                        'allowClear' => true,
                        'disabled' => $faculty,
                        'placeholder' => __('-Choose Faculty-'),
                    ]
                )->label(false); ?>
            </div>
            <div class="col col-md-4">
                <div class="form-group">
                    <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                        'data' => ArrayHelper::map(EDepartment::getDepartmentList($faculty), 'id', 'name'),
                        'allowClear' => true,
                        'hideSearch' => false,
                        'options'=>['placeholder' => __('Choose Department')],
                    ])->label(false); ?>
                </div>
            </div>
            <div class="col col-md-4">
                <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name / Passport / Employee ID')])->label(false) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'id' => 'data-grid',
        //'toggleAttribute' => 'active',
        'dataProvider' => $dataProvider,
        /*'rowOptions' => function ($data, $key, $index, $grid) {
            if($log = $data->employee->systemLog){
                $week = date('d.m.Y',strtotime("-7 days"));
                if(date('Y-m-d', strtotime($week)) >= date('Y-m-d', strtotime(Yii::$app->formatter->asDate($log->created_at)))){
                    return ['class' => 'danger'];
                }
                else
                    return ['class' => ''];
            }
            else
                return ['class' => 'danger'];


        },*/
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => '_employee',
                'format' => 'raw',
                'value' => function ($data) {
                    return sprintf("%s<p class='text-muted'> %s</p>", @$data->employee->fullName, @$data->staffPosition->name);
                }
            ],
            [
                'attribute' => '_department',
                'header' => __('Structure Faculty'),
                'value' => function ($data) {
                    return sprintf("%s", @$data->department->parentDepartment->name);
                }

            ],
            [
                'attribute' => '_department',
                'header' => __('Structure Department'),
                'value' => function ($data) {
                    return sprintf("%s", @$data->department->name);
                }
            ],

            [
                'attribute' => 'Ip',
                'header' => __('Ip'),
                'value' => function ($data) {
                    return $data->ip ? $data->ip : '';
                },
            ],
            [
                'attribute' => 'message',
                'format' => 'raw',
                'header' => __('Message'),
                'value' => function ($data) {
                    return $data->message ? \common\models\employee\EEmployeeMeta::shortTitle($data->message) : '';
                },

            ],
            [
                'attribute' => 'created_at',
                'header' => __('Last Entry Date'),
                'format'=>'raw',
                'value' => function ($data) {
                    if($data->created_at){
                        $week = date('d.m.Y',strtotime("-7 days"));
                        if(date('Y-m-d', strtotime($week)) >= date('Y-m-d', strtotime(Yii::$app->formatter->asDate($data->created_at)))){
                            return '<span style="color:red">'.date('d.m.Y H:i:s', strtotime(Yii::$app->formatter->asDatetime($data->created_at))).'</span>';
                        }
                        else
                            return date('d.m.Y H:i:s', strtotime(Yii::$app->formatter->asDatetime($data->created_at)));
                    }
                },
            ],
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
