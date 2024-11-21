<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use common\models\academic\EDecree;
use common\models\student\EStudentTransferGroupMeta;
use common\models\system\classifier\DecreeType;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use common\models\student\ESpecialty;
use common\models\student\EGroup;
use common\models\student\EStudentMeta;
use common\models\curriculum\Semester;
use common\models\curriculum\EducationYear;
use common\models\curriculum\ECurriculum;
use common\models\curriculum\EStudentSubject;
use common\models\system\classifier\SubjectType;
use common\models\system\classifier\Course;
use kartik\select2\Select2;
use backend\widgets\Select2Default;
use common\models\system\AdminRole;
use kartik\depdrop\DepDrop;
use backend\widgets\DatePickerDefault;

/* @var $this \backend\components\View */
/* @var $searchModel \common\models\student\EStudentTransferGroupMeta */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false,], 'enablePushState' => false]) ?>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => false, 'validateOnSubmit' => false, 'options' => ['id' => 'transfer-form']]); ?>
<div class="row">
    <div class="col col-md-3 col-sm-push-9" style="padding-left: 0">
        <div class="box box-default " id="data-grid-filters">
            <div class="box-body">
                <div class="row">
                    <div class="col col-md-12">
                        <?= $form->field($searchModel, '_department')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getDepartmentItems(),
                            'allowClear' => $searchModel->_education_type == null,
                            'disabled' => $faculty != null,
                            'hideSearch' => false,
                            'options' => [
                                'onchange' => '$("#estudenttransfergroupmeta-_education_form").val("")'
                            ],
                        ])->label(false); ?>
                        <?= $form->field($searchModel, '_education_type')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationTypeItems(),
                            'allowClear' => $searchModel->_education_form == null,
                            'disabled' => $disabled = $searchModel->_department == null,
                            'hideSearch' => false,
                            'options' => [
                                'onchange' => '$("#estudenttransfergroupmeta-_education_form").val("")'
                            ],
                        ])->label(false); ?>
                        <?= $form->field($searchModel, '_education_form')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationFormItems(),
                            'allowClear' => $searchModel->_curriculum == null,
                            'hideSearch' => false,
                            'disabled' => $disabled = $searchModel->_education_type == null,
                            'options' => [
                                'onchange' => '$("#estudenttransfergroupmeta-_curriculum").val("")'
                            ],
                        ])->label(false); ?>

                        <?= $form->field($searchModel, '_curriculum')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getCurriculumItems(),
                            'allowClear' => $searchModel->_education_year == null,
                            'hideSearch' => false,
                            'disabled' => $disabled = $disabled || $searchModel->_education_form == null,
                            'options' => [
                                'onchange' => '$("#estudenttransfergroupmeta-_education_year").val("")'
                            ],
                        ])->label(false); ?>
                        <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getEducationYearItems(),
                            'allowClear' => $searchModel->_semestr == null,
                            'hideSearch' => false,
                            'disabled' => $disabled = $disabled || $searchModel->_curriculum == null,
                            'options' => [
                                'onchange' => '$("#estudenttransfergroupmeta-_semestr").val("")'
                            ],
                        ])->label(false); ?>
                        <?= $form->field($searchModel, '_semestr')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getSemesterItems(),
                            'allowClear' => $searchModel->_group == null,
                            'hideSearch' => false,
                            'disabled' => $disabled = $disabled || $searchModel->_education_year == null,
                            'options' => [
                                'onchange' => '$("#estudenttransfergroupmeta-_group").val("")'
                            ],
                        ])->label(false); ?>
                        <?= $form->field($searchModel, '_group')->widget(Select2Default::classname(), [
                            'data' => $searchModel->getGroupItems(),
                            'allowClear' => true,
                            'hideSearch' => false,
                            'disabled' => $disabled = ($disabled || $searchModel->_semestr == null),
                        ])->label(false); ?>
                        <?= $form->field($searchModel, 'search')->textInput(['placeholder' => __('Search by Name / Student ID / Passport / PIN')])->label(false); ?>
                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?= Html::a('<i class="fa fa-close"></i> ' . __('Clear Filter'), ['student-group', 'clear-filter' => 1], [
                    'class' => 'btn btn-default btn-flat',
                    'data-pjax' => 0
                ]) ?>
            </div>
            <div class="box-header bg-gray"></div>
            <div class="box-body">
                <?= $form->field($searchModel, 'nextDepartment')->widget(Select2Default::classname(), [
                    'data' => $options = $searchModel->getNextDepartmentOptions($faculty),
                    'allowClear' => false,
                    'hideSearch' => false,
                    'disabled' => $searchModel->_group == null || $faculty != null,
                ])->label(); ?>
                <?= $form->field($searchModel, 'nextEducationForm')->widget(Select2Default::classname(), [
                    'data' => $options = $searchModel->getNextEducationFormOptions(),
                    'allowClear' => false,
                    'hideSearch' => true,
                    'disabled' => $searchModel->nextDepartment == null,
                ])->label(); ?>
                <?= $form->field($searchModel, 'nextCurriculum')->widget(Select2Default::classname(), [
                    'data' => $options = $searchModel->getNextCurriculumOptions(),
                    'allowClear' => false,
                    'hideSearch' => false,
                    'disabled' => $searchModel->nextEducationForm == null,
                ])->label(); ?>

                <?= $form->field($searchModel, 'nextGroup')->widget(Select2Default::classname(), [
                    'data' => $options = $searchModel->getNextGroupOptions(),
                    'allowClear' => false,
                    'hideSearch' => false,
                    'disabled' => $searchModel->nextCurriculum == null,
                ])->label(); ?>
                <?= $form->field($searchModel, 'nextSemester')->widget(Select2Default::classname(), [
                    'data' => $options = $searchModel->getNextSemesterOptions(),
                    'allowClear' => false,
                    'hideSearch' => false,
                    'disabled' => $searchModel->nextGroup == null,
                ])->label(); ?>
                <?= $form->field($searchModel, '_decree')->widget(Select2Default::class, [
                    'data' => $options = $searchModel->getDecreeOptions(),
                    'hideSearch' => false,
                    'allowClear' => false,
                    'disabled' => $searchModel->nextSemester == null,
                ]); ?>
                <?= $form->field($searchModel, 'selectedStudents')->textInput(['readonly' => true])->label(); ?>
                <?= $form->field($searchModel, 'subjectsMap')->hiddenInput()->label(false) ?>
            </div>
            <div class="box-footer text-right">
                <?= Html::button('<i class="fa fa-check"></i> ' . __('Transfer to Course'), [
                    'class' => 'btn btn-primary btn-flat',
                    'disabled' => $searchModel->_decree == null,
                    'onclick' => 'return confirmTransfer()'
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col col-md-9 col-sm-pull-3">
        <div class="box box-default">
            <div style="max-height: 1006px;overflow-y: auto">
                <?= GridView::widget([
                    'id' => 'data-grid',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'class' => 'yii\grid\CheckboxColumn',
                            'checkboxOptions' => function (EStudentTransferGroupMeta $model, $key, $index, $column) use ($searchModel) {
                                return [
                                    'disabled' => !$model->canTransferToGroup($searchModel->nextGroupItem)
                                ];
                            }
                        ],
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => '_student',
                            'format' => 'raw',
                            'value' => function (EStudentTransferGroupMeta $data) {
                                return sprintf('%s<p class="text-muted">%s</p>', $data->student->getFullName(), $data->student->student_id_number);
                            },
                        ],
                        [
                            'attribute' => '_education_type',
                            'format' => 'raw',
                            'value' => function (EStudentTransferGroupMeta $data) {
                                return sprintf('%s<p class="text-muted">%s</p>', $data->educationType->name, $data->educationForm->name);
                            },
                        ],
                        [
                            'attribute' => '_level',
                            'format' => 'raw',
                            'value' => function (EStudentTransferGroupMeta $data) {
                                $semester = "";
                                if(Semester::getByCurriculumSemester($data->_curriculum, $data->_semestr) != null)
                                    $semester  = Semester::getByCurriculumSemester($data->_curriculum, $data->_semestr)->name;
                                elseif($data->semester)
                                    $semester =  $data->semester->name;
                                else
                                    $semester = \common\models\system\classifier\Semester::findOne($data->_semestr)->name;
                                return sprintf('%s<p class="text-muted">%s</p>', $data->level->name, $semester);
                            },
                        ],
                        [
                            'attribute' => '_group',
                            'format' => 'raw',
                            'value' => function (EStudentTransferGroupMeta $data) {
                                return sprintf('%s<p class="text-muted">%s</p>', $data->group->name, $data->educationYear->name);
                            },
                        ]
                    ],
                ]); ?>
            </div>
        </div>

        <div class="row " style="margin-top: 50px">
            <div class="col col-md-12">
                <div class="box box-default ">
                    <div class="box-header bg-gray ">
                        <div class="row">
                            <div class="col-md-8">
                                <h4><?= __("Perevod o'quv reja fanlari") ?></h4>
                            </div>
                            <div class="col-md-4">
                                <h4><?= __("Joriy o'quv reja fanlari") ?></h4>
                            </div>

                        </div>
                    </div>
                    <div class="box-body">
                        <div style="margin: 0 -10px">
                            <?php if ($thisCurriculumItems = $searchModel->getCurriculumSemesterSubjects($searchModel->_curriculum, $searchModel->_semestr)): ?>
                                <div class="row">

                                    <?php if ($searchModel->nextCurriculum != $searchModel->_curriculum): ?>
                                        <?php
                                        $items = $searchModel->getCurriculumSemesterSubjects($searchModel->nextCurriculum, $searchModel->_semestr);
                                        ?>
                                        <?php if ($searchModel->decree && $searchModel->nextGroup): ?>
                                            <?php if (count($items)): ?>
                                                <div class="col col-md-4" style="padding-right: 0; ">
                                                    <div id="data-grid" class="grid-view-div">
                                                        <div class="tr th">
                                                            <div class="row">
                                                                <div class="col col-sm-7">
                                                                    <?= __('Subject') ?>
                                                                </div>
                                                                <div class="col col-sm-5 text-right">
                                                                    <?= __('Info') ?>
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
                                                            <div class="tr <?= $item->in_group ? 'in_group_' . count($groups) : '' ?>">
                                                                <div class="row">
                                                                    <div class="col col-sm-7">
                                                                        <?= ($i - $g + 1) ?>.
                                                                        <?= $item->subject->name ?>
                                                                    </div>
                                                                    <div class="col col-sm-5 text-right">
                                                                        <?= $item->getShortInfo() ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <div class="col col-md-4" style="padding-left: 0;padding-right: 0">
                                                    <div id="data-grid" class="grid-view-div">
                                                        <div class="tr th">
                                                            <div class="row">
                                                                <div class="col col-sm-7">
                                                                    <?= __('Subject') ?>
                                                                </div>
                                                                <div class="col col-sm-5 text-right">
                                                                    <?= __('Info') ?>
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
                                                            $withGroup = false;
                                                            if ($item->in_group) {
                                                                if (!isset($groups[$item->in_group])) {
                                                                    $groups[$item->in_group] = $item->in_group;
                                                                    $withGroup = true;
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

                                                            <div class="tr droppable "
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
                                                                    <?= __('Fanlar farqi: {b}{count}{/b} ta', ['count' => $i - $g + 1]) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="col col-md-8" style="padding-right: 0; ">
                                                    <p class="empty">
                                                        <?= __("Ushbu o'quv rejaga fanlar biriktirilmagan") ?>
                                                    </p>
                                                </div>
                                            <?php endif ?>
                                        <?php else: ?>
                                            <div class="col col-md-8" style="padding-right: 0; ">
                                                <p class="empty">
                                                    <?= __("Perevod sozlamalarini tanlang") ?>
                                                </p>
                                            </div>
                                        <?php endif ?>
                                    <?php else: ?>
                                        <div class="col col-md-12">
                                            <p class="empty">
                                                <?= __("Bitta o'quv reja bo'lganligi sababli fanlarni moslashtirish amalga oshirilmaydi") ?>
                                            </p>
                                        </div>
                                    <?php endif ?>
                                    <?php if ($searchModel->nextCurriculum != $searchModel->_curriculum): ?>
                                        <div class="col col-md-4" style="padding-left: 0">
                                            <div id="data-grid" class="grid-view-div">
                                                <div class="tr th">
                                                    <div class="row">
                                                        <div class="col col-sm-7">
                                                            <?= __('Subject') ?>
                                                        </div>
                                                        <div class="col col-sm-5 text-right">
                                                            <?= __('Info') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                $semester = -1;
                                                $gg = -1;
                                                ?>
                                                <?php foreach ($thisCurriculumItems as $i => $item): ?>
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
                                                         ondrop="drop(event)"
                                                         ondragover="allowDrop(event)"
                                                         ondragleave="dragLive(event)">
                                                        <div class="row draggable"
                                                             draggable="true"
                                                             id="item_<?= $item->id ?>"
                                                             data-id="<?= $item->id ?>"
                                                             ondragstart="drag(event)">
                                                            <div class="">
                                                                <div class="col col-sm-7">
                                                                    <?= ($i + 1) ?>. <?= $item->subject->name ?>
                                                                </div>
                                                                <div class="col col-sm-5 text-right">
                                                                    <?= $item->getShortInfo() ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif ?>
                                </div>
                            <?php else: ?>
                                <p class="empty">
                                    <?= __("Joriy o'quv reja va semestrni tanlang") ?>
                                </p>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<script>
    function updateSelectedStudents() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        $('#estudenttransfergroupmeta-selectedstudents').val(keys.length)
    }

    function confirmTransfer() {
        var keys = $('#data-grid').yiiGridView('getSelectedRows');
        if (keys.length === 0) {
            alert(<?=json_encode([__('Please, select students')])?>[0])
        } else {
            if (confirm(<?=json_encode([__('Are you sure to transfer {count} students to {group} group?')])?>[0].replace('{count}', keys.length).replace('{group}', '<?=$searchModel->nextGroupItem ? $searchModel->nextGroupItem->name : ''?>'))) {
                $('#transfer-form').submit();
            }
        }

        return false;
    }
</script>
<?php
$this->registerJs("$('#data-grid input[type=\"checkbox\"]').on('change',function(){updateSelectedStudents()})")
?>
<?php Pjax::end() ?>
<script>

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

    let subjectDiffs = [];

    function drop(ev) {
        ev.preventDefault();
        if ($(ev.target).hasClass('droppable')) {
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
                $('#estudenttransfergroupmeta-subjectsmap').val(subjectDiffs.join(','));
            }
            $('#subjects_diff').html('<?=__('Fanlar farqi: {b}{count}{/b} ta')?>'.replace('{count}', parseInt($('#subjects_diff').data('count')) - subjectDiffs.length));
            console.log(subjectDiffs);
        }
    }
</script>



