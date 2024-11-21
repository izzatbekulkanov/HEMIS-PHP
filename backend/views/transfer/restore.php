<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\academic\EDecree;
use common\models\curriculum\ECurriculumSubject;
use common\models\performance\estudentrestore;
use common\models\student\EStudentRestoreMeta;
use common\models\system\classifier\DecreeType;
use common\models\curriculum\Semester;
use common\models\system\classifier\EducationForm;
use common\models\system\classifier\EducationType;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\student\EGroup;
use common\models\curriculum\ECurriculum;
use common\models\system\classifier\Course;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\student\EStudentRestoreMeta */
/* @var $restoreModel \common\models\student\EStudentRestoreMeta */
/* @var $restoreMetaModel \common\models\student\EStudentRestoreMeta */
/* @var $dataProvider yii\data\ActiveDataProvider */
$semester = -1;
$this->params['breadcrumbs'][] = $this->title;
$cols = [
    ['class' => 'yii\grid\SerialColumn'],
    [
        'attribute' => '_student',
        'format' => 'raw',
        'value' => function (EStudentRestoreMeta $data) {
            return sprintf('%s<p class="text-muted">%s</p>', $data->student->getFullName(), $data->student->student_id_number);
        },
    ],

    [
        'attribute' => '_education_type',
        'format' => 'raw',
        'value' => function (EStudentRestoreMeta $data) {
            return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
        },
    ],
    [
        'attribute' => '_group',
        'format' => 'raw',
        'value' => function (EStudentRestoreMeta $data) {
            return sprintf('%s<p class="text-muted">%s</p>', $data->group->name, $data->curriculum->name);
        },
    ],
    [
        'attribute' => '_level',
        'format' => 'raw',
        'value' => function (EStudentRestoreMeta $data) {

            return $data->semester ? sprintf('%s<p class="text-muted">%s</p>', $data->semester->level ? $data->semester->level->name : '', $data->semester->name) : '';
        },
    ],
    [
        'attribute' => '_student_status',
        'value' => 'studentStatus.name',
    ],
    [
        'attribute' => 'updated_at',
        'format' => 'raw',
        'value' => function (EStudentRestoreMeta $data) {
            return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
        },
    ],
];
if ($restoreModel == null) {
    array_push($cols, [
        'format' => 'raw',
        'value' => function (EStudentRestoreMeta $data) {
            return $data->canOperateRestore() ? Html::a(__('Restore'), linkTo(['transfer/restore', 'id' => $data->id]), ['data-pjax' => 0, 'class' => 'btn btn-default btn-block btn-flat']) : '';
        },
    ]);
}
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => true], 'enablePushState' => false]) ?>

<?php if ($restoreModel && $restoreModel->canOperateRestore()): ?>
    <div class="box box-default ">
        <div class="box-header bg-gray">
            <h4><?= __('Tanlangan talaba') ?></h4>
        </div>
        <?= GridView::widget([
            'id' => 'data-grid',
            'layout' => "<div class='box-body no-padding'>{items}</div>",
            'dataProvider' => new \yii\data\ArrayDataProvider(['models' => [$restoreModel]]),
            'columns' => $cols,
        ]); ?>
    </div>
    <?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'id' => 'restore-form']); ?>
    <div class="box box-default ">
        <div class="box-header bg-gray ">
            <h4><?= __('Tiklash parameterlari') ?></h4>
        </div>
        <div class="box-body">
            <div id="data-grid-filters">
                <div class="row">
                    <div class="col col-md-6">
                        <?= $form->field($restoreMetaModel, '_education_type')->widget(Select2Default::classname(), [
                            'data' => $restoreMetaModel->getEducationTypeItemsForRestore($restoreModel),
                            'allowClear' => false,
                            'disabled' => $disabled = false,
                        ]) ?>
                        <?= $form->field($restoreMetaModel, '_education_form')->widget(Select2Default::classname(), [
                            'data' => $restoreMetaModel->getEducationFormItemsForRestore($restoreModel),
                            'allowClear' => false,
                            'disabled' => $disabled = ($disabled || $restoreMetaModel->_education_type == null),
                        ]) ?>
                        <?= $form->field($restoreMetaModel, '_curriculum')->widget(Select2Default::classname(), [
                            'data' => $restoreMetaModel->getCurriculumItemsForRestore($restoreModel),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = ($disabled || $restoreMetaModel->_education_form == null),
                        ]) ?>
                    </div>
                    <div class="col col-md-6">
                        <?= $form->field($restoreMetaModel, '_semestr')->widget(Select2Default::classname(), [
                            'data' => $restoreMetaModel->getSemesterItemsForRestore($restoreModel),
                            'allowClear' => true,
                            'disabled' => $disabled = ($disabled || $restoreMetaModel->_curriculum == null),
                        ]) ?>
                        <?= $form->field($restoreMetaModel, '_group')->widget(Select2Default::classname(), [
                            'data' => $restoreMetaModel->getGroupItemsForRestore($restoreModel),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = ($disabled || $restoreMetaModel->_semestr == null),
                        ]) ?>
                        <?= $form->field($restoreMetaModel, '_decree')->widget(Select2Default::class, [
                            'data' => EDecree::getOptionsByCurriculum($this->_user(), $restoreMetaModel->_curriculum ? $restoreMetaModel->curriculum : null, DecreeType::TYPE_RESTORE)['options'],
                            'disabled' => $disabled = ($disabled || $restoreMetaModel->_group == null),
                            'hideSearch' => false,
                            'allowClear' => true,
                        ]); ?>
                        <?= $form->field($restoreMetaModel, 'subjects_map')->hiddenInput()->label(false) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <div class="row">
        <div class="col col-md-8">
            <div class="box box-default ">
                <div class="box-header bg-gray ">
                    <h4><?= __('Tiklanadigan o\'quv reja fanlari') ?></h4>
                </div>
                <div class="box-body">
                    <div style="margin: 0 -10px">

                        <?php if ($items = $restoreMetaModel->getCurriculumSemesterSubjects()): ?>
                            <div class="row">
                                <div class="col col-md-6" style="padding-right: 0">
                                    <div id="data-grid" class="grid-view-div">
                                        <div class="tr th">
                                            <div class="row">
                                                <div class="col col-sm-6">
                                                    <?= __('Subject') ?>
                                                </div>
                                                <div class="col col-sm-2">
                                                    <?= __('Type') ?>
                                                </div>
                                                <div class="col col-sm-2">
                                                    <?= __('Acload') ?>
                                                </div>
                                                <div class="col col-sm-2">
                                                    <?= __('Credit') ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        $semester = -1;
                                        $gg = -1;

                                        ?>
                                        <?php foreach ($items as $i => $item): ?>
                                            <?php if ($item->_semester != $semester): $semester = $item->_semester ?>
                                                <div class="tr">
                                                    <div class="row">
                                                        <div class="col col-sm-12 text-center text-bold">
                                                            <?= $item->semester->name ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <div class="tr">
                                                <div class="row">
                                                    <div class="col col-sm-6">
                                                        <?= ($i + 1) ?>. <?= $item->subject->name ?>
                                                    </div>
                                                    <div class="col col-sm-2">
                                                        <?= @mb_substr($item->subjectType->name, 0, 1) ?>
                                                    </div>
                                                    <div class="col col-sm-2">
                                                        <?= $item->total_acload ?>
                                                    </div>
                                                    <div class="col col-sm-2">
                                                        <?= $item->credit ?>
                                                    </div>

                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col col-md-6" style="padding-left: 0">
                                    <div id="data-grid" class="grid-view-div">
                                        <div class="tr th">
                                            <div class="row">
                                                <div class="col col-sm-6">
                                                    <?= __('Subject') ?>
                                                </div>
                                                <div class="col col-sm-2">
                                                    <?= __('Type') ?>
                                                </div>
                                                <div class="col col-sm-2">
                                                    <?= __('Acload') ?>
                                                </div>
                                                <div class="col col-sm-1">
                                                    <?= __('Credit') ?>
                                                </div>
                                                <div class="col col-sm-1">
                                                    <?= __('Grade') ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        $semester = -1;
                                        $groups = [];
                                        $g = 0;
                                        ?>
                                        <?php foreach ($items as $i => $item): ?>
                                            <?php
                                            if ($item->in_group) {
                                                if (!isset($groups[$item->in_group])) {
                                                    $groups[$item->in_group] = $item->in_group;
                                                } else {
                                                    $g++;
                                                }
                                            }
                                            ?>
                                            <?php if ($item->_semester != $semester): $semester = $item->_semester ?>
                                                <div class="tr">
                                                    <div class="row">
                                                        <div class="col col-sm-12 text-center text-bold">
                                                            <?= $item->semester->name ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <div class="tr droppable"
                                                 data-group="<?= $item->in_group ?>"
                                                 data-id="<?= $item->id ?>"
                                                 id="cs_id_<?= $item->id ?>"
                                                 ondrop="drop(event)"
                                                 ondragover="allowDrop(event)"
                                                 ondragleave="dragLive(event)">

                                            </div>
                                        <?php endforeach; ?>
                                        <div class="tr th">
                                            <div class="row">
                                                <div class="col col-sm-12 text-right" id="subjects_diff"
                                                     data-count="<?= ($i - $g + 1) ?>">
                                                    <?= __('Fanlar farqi: {b}{count}{/b} ta', ['count' => $i + 1]) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>
                            <p class="empty">
                                <?= __("Tiklanadigan o'quv rejasi va semestrni tanlang") ?>
                            </p>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-md-4">
            <div class="box box-default ">
                <div class="box-header bg-gray ">
                    <h4><?= __('Talabaning akademik yozuvi') ?></h4>
                </div>
                <div class="box-body">
                    <div style="margin: 0 -10px">
                        <div id="data-grid" class="grid-view-div">
                            <div class="tr th">
                                <div class="row">
                                    <div class="col col-sm-6">
                                        <?= __('Subject') ?>
                                    </div>
                                    <div class="col col-sm-2">
                                        <?= __('Type') ?>
                                    </div>
                                    <div class="col col-sm-2">
                                        <?= __('Acload') ?>
                                    </div>
                                    <div class="col col-sm-1">
                                        <?= __('Credit') ?>
                                    </div>
                                    <div class="col col-sm-1">
                                        <?= __('Grade') ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $semester = -1;
                            ?>
                            <?php foreach ($restoreModel->getSubjectsWithAcademicRecord()['data'] as $i => $item): ?>
                                <?php if ($item['_semester'] != $semester): ?>
                                    <div class="tr">
                                        <div class="row">
                                            <div class="col col-sm-12 text-center text-bold">
                                                <?php
                                                if ($model = \common\models\system\classifier\Semester::findOne($item['_semester'])) {
                                                    echo $model->name;
                                                }
                                                $semester = $item['_semester'];
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="tr  droppable"
                                     ondrop="drop(event)"
                                     ondragover="allowDrop(event)"
                                     ondragleave="dragLive(event)">
                                    <div class="row draggable"
                                         draggable="true"
                                         id="item_<?= $item['academic_record_id'] ?>"
                                         data-id="<?= $item['academic_record_id'] ?>"
                                         ondragstart="drag(event)">
                                        <div class="col col-sm-6">
                                            <?= ($i + 1) ?>.
                                            <?php
                                            if ($model = \common\models\curriculum\ESubject::findOne($item['_subject'])) {
                                                echo $model->name;
                                            }
                                            ?>
                                        </div>
                                        <div class="col col-sm-2">
                                            <?php
                                            if ($model = \common\models\system\classifier\SubjectType::findOne(['code' => $item['_subject_type']])) {
                                                echo mb_substr($model->name, 0, 1);
                                            }
                                            ?>
                                        </div>
                                        <div class="col col-sm-2">
                                            <?= $item['total_acload'] ?>
                                        </div>
                                        <div class="col col-sm-1">
                                            <?= $item['credit'] ?>
                                        </div>
                                        <div class="col col-sm-1">
                                            <?= $item['grade'] ?: '-' ?>
                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box box-default">
        <div class="box-header bg-gray">
            <h4><?= __('Tiklash natijasi') ?></h4>
        </div>
        <?php if ($restoreMetaModel->_decree != false): ?>
            <?= GridView::widget([
                'id' => 'data-grid',
                'layout' => "<div class='box-body no-padding'>{items}</div>",
                'dataProvider' => new \yii\data\ArrayDataProvider(['models' => [$restoreMetaModel]]),
                'columns' => $cols,
            ]); ?>
            <div class="box-footer text-right">
                <button onclick="return restoreStudent()"
                        class="btn btn-primary btn-flat">
                    <i class="fa fa-check"></i> <?= __('Restore Student') ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="box box-default ">
        <div class="box-header bg-gray">
            <?php $form = ActiveForm::begin(); ?>
            <div class="row">
                <div class="col col-md-12">
                    <div class="row" id="data-grid-filters">
                        <div class="col col-md-2">
                            <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                                'data' => $searchModel->getEducationTypeItems(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-2">
                            <?= $form->field($searchModel, '_education_form')->widget(Select2Default::classname(), [
                                'data' => $searchModel->getEducationFormItems(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-2">
                            <?= $form->field($searchModel, '_student_status')->widget(Select2Default::classname(), [
                                'data' => \common\models\system\classifier\StudentStatus::getRestoreStatusOptions(),
                                'allowClear' => true,
                                'hideSearch' => false,
                            ])->label(false); ?>
                        </div>
                        <div class="col col-md-6">
                            <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN')])->label(false); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <?= GridView::widget([
            'id' => 'data-grid',
            'dataProvider' => $dataProvider,
            'columns' => $cols,
        ]); ?>
    </div>
<?php endif; ?>
<script>
    let subjectDiffs = [];

    function restoreStudent() {
        if (confirm(<?=json_encode([__('Are you sure to restore the student?')])?>[0])) {
            $('#restore-form').submit();
        }

        return false;
    }

    function dragLive(ev) {
        $(ev.target).removeClass('dropping');
    }

    function allowDrop(ev) {
        ev.preventDefault();
        if ($(ev.target).hasClass('droppable')) {
            $(ev.target).addClass('dropping');
        }
    }

    function drag(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
        $(ev.target).addClass('dragging');
    }

    function drop(ev) {
        ev.preventDefault();
        if ($(ev.target).hasClass('droppable')) {
            console.log(ev);

            var id = ev.dataTransfer.getData("text");
            ev.target.appendChild(document.getElementById(id));
            var csId = $(ev.target).removeClass('dropping').data('id');
            var arId = $('#' + id).removeClass('dragging').data('id');

            subjectDiffs = subjectDiffs.filter(function (e) {
                let a = e.split(':');
                return a[1].localeCompare(arId) !== 0;
            })

            if (csId !== undefined) {
                subjectDiffs.push(csId + ':' + arId);
                $('#estudentrestoremeta-subjects_map').val(subjectDiffs.join(','));
            }
            $('#subjects_diff').html('<?=__('Fanlar farqi: {b}{count}{/b} ta')?>'.replace('{count}', parseInt($('#subjects_diff').data('count')) - subjectDiffs.length));
            console.log(subjectDiffs);
        }
    }
</script>
<?php Pjax::end() ?>
