<?php

use common\models\finance\EStudentContractType;
use common\models\finance\EStudentContract;
use common\models\curriculum\EducationYear;
use backend\widgets\GridView;
use backend\widgets\Select2Default;
use frontend\models\academic\StudentDiploma;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$this->title = __('Student Diploma');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'exam-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

<div class="box box-default ">
    <?= GridView::widget(
        [
            'id' => 'data-grid',
            'sticky' => '#sidebar',
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => '_student',
                    // 'enableSorting' => true,
                    'format' => 'raw',
                    'value' => function ($data) {
                        return sprintf("%s<p class='text-muted'>%s, %s</p>", $data->student->fullName, $data->education_type_name, $data->education_form_name);
                    },
                ],
                [
                    'attribute' => 'specialty_name',
                    'format' => 'raw',
                    'value' => function (StudentDiploma $data) {
                        return sprintf("%s<p class='text-muted'>%s</p>", $data->specialty_name, $data->specialty_code);
                    },
                ],
                [
                    'attribute' => 'diploma_number',
                    'header' => __('Diploma Number'),
                    'format' => 'raw',
                    'value' => function (StudentDiploma $data) {
                        return sprintf(
                            "%s<p class='text-muted'>%s</p>",
                            $data->diploma_number,
                            $data->getCategoryLabel()
                        );
                    },
                ],
                [
                    'attribute' => 'register_number',
                    'header' => __('Register Number'),
                    'format' => 'raw',
                    'value' => function (StudentDiploma $data) {
                        return sprintf(
                            "%s &nbsp;&nbsp;(%s)",
                            $data->register_number,
                            $data->register_date->format('Y-m-d')
                        );
                    },
                ],

                [
                    'attribute' => 'pdf',
                    'header' => __('Files'),
                    'format' => 'raw',
                    'value' => function (StudentDiploma $data) {
                        $html = '';
                        if (file_exists($data->getDiplomaFilePath())) {
                            $html .= Html::a(
                                "<i class='fa fa-download'></i> " .__('Diploma'),
                                ['diploma', 'diploma' => $data->id],
                                ['data-pjax' => 0, 'class' => 'btn btn-default']
                            );
                        }
                        if (file_exists($data->getSupplementFilePath())) {
                            $html .= Html::a(
                                "<i class='fa fa-download'></i> " . __('Supplement'),
                                ['diploma', 'supplement' => $data->id],
                                ['data-pjax' => 0, 'class' => 'btn btn-default']
                            );
                        }
                        return $html;
                    }
                ]
            ],
        ]
    ); ?>
</div>
<?php Pjax::end() ?>
