<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\components\hemis\HemisApiSyncModel;
use common\models\academic\EDecreeStudent;
use common\models\student\EStudent;
use common\models\system\classifier\StudentStatus;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use common\models\student\ESpecialty;
use common\models\system\classifier\PaymentForm;
use common\models\curriculum\EducationYear;
use common\models\structure\EDepartment;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use kartik\depdrop\DepDrop;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
<div style="margin: 0 -15px">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'student.first_name',
                'format' => 'raw',
                'value' => function (EDecreeStudent $data) {
                    return sprintf(
                        "%s <p class='text-muted'>%s / %s / %s / %s</p>",
                        $data->student->getFullName(),
                        $data->student->student_id_number,
                        @$data->studentMeta->educationType->name,
                        @$data->studentMeta->educationForm->name,
                        @$data->studentMeta->specialty->code
                    );
                },
            ],

            [
                'format' => 'raw',
                'header' => __('Semester'),
                'value' => function (EDecreeStudent $data) {
                    return @$data->studentMeta->semester->name;
                },
            ],

            [
                'format' => 'raw',
                'header' => __('Group'),
                'value' => function (EDecreeStudent $data) {
                    return @$data->studentMeta->group->name;
                },
            ],
            [
                'format' => 'raw',
                'header' => __('Apply Date'),
                'value' => function ($data) {
                    return Yii::$app->formatter->asDatetime($data->created_at->getTimestamp());
                },
            ]
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>
