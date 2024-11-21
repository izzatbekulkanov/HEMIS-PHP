<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\ECurriculumSubjectDetail;
use common\models\system\classifier\EducationWeekType;
use common\models\system\classifier\Course;
use common\models\system\classifier\SubjectGroup;
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

$this->params['breadcrumbs'][] = ['url' => ['teacher/subject-resources'], 'label' => $this->title];
$this->params['breadcrumbs'][] = $subject->curriculum->name;
$this->params['breadcrumbs'][] = $subject->semester->name;
$this->params['breadcrumbs'][] = $subject->subject->name;

?>
<div style="margin: -15px -15px -35px">
    <?php Pjax::begin(['id' => 'resources-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>
    <div class='box-body no-padding'>
        <div>
            <?= GridView::widget([
                'id' => 'data-grid',
                //'layout' => '{items}',
               // 'toggleAttribute' => 'active',
                'dataProvider' => $dataProviderResources,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'name',
                        'format' => 'text',
                        /*'value' => function ($data) use ($group) {
                            return Html::a($data->name, ['teacher/subject-topic-resource',
                                'semester' => $data->_semester,
                                'group' => $group->id,
                                'subject' => $data->_subject,
                                'training_type' => \common\models\system\classifier\TrainingType::TRAINING_TYPE_LECTURE,
                                'code' => $data->id
                            ], ['data-pjax' => 0]);
                        },*/

                    ],
                    [
                        'attribute' => 'comment',
                        'format' => 'text',
                    ],
                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'headerOptions' => [
                                'style' => 'width:15%',
                        ],
                        'header' => __('Edit Resource'),
                        'value' => function ($data) {
                            return Html::a('<i class="fa fa-edit"></i> &nbsp;'.__('Edit'), ['teacher/subject-topic-resource-edit', 'education_lang' => $data->_language, 'code' => $data->_subject_topic, 'id' => $data->id,], ['data-pjax' => 0]);
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>
    <div class='box-footer '>
        <div class="col-md-8">
        </div>
        <div class="col-md-4  text-right">
            <button type="button" class="btn btn-flat btn-default"
                    data-dismiss="modal"><?= __('Close') ?></button>
        </div>
    </div>
    <?php Pjax::end() ?>
</div>

