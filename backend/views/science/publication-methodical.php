<?php

use backend\widgets\checkbo\CheckBo;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use common\models\science\EPublicationAuthorMeta;
use backend\widgets\Select2Default;
use common\models\curriculum\EducationYear;

/* @var $this \backend\components\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'admin-grid', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

    <div class="box box-default ">
        <div class="box-header bg-gray">
            <div class="row" id="data-grid-filters">
                <?php $form = ActiveForm::begin(); ?>

                <div class="col col-md-4 pull left">
                    <div class="form-group pull left">
                        <?= $this->getResourceLink(
                            '<i class="fa fa-plus-circle"></i> ' . __('Add Methodical Publication Manually'),
                            ['science/publication-methodical-edit'],
                            ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                        ) ?>
                        <?= $this->getResourceLink(
                            '<i class="fa fa-plus-circle"></i> ' . __('Insert Methodical by Select'),
                            ['science/publication-methodical-list'],
                            ['class' => 'btn btn-flat btn-success ', 'data-pjax' => 0]
                        ) ?>

                    </div>
                </div>
                <div class="col col-md-2">
                    <?= $form->field($searchModel, '_education_year')->widget(Select2Default::classname(), [
                        'data' => EducationYear::getEducationYears(),
                        'allowClear' => true,
                        'hideSearch' => false,
                        'placeholder' => __('Education Year'),
                    ])->label(false); ?>

                </div>
                <div class="col col-md-6">
                    <?= $form->field($searchModel, 'search', ['labelOptions' => ['class' => 'invisible']])->textInput(['placeholder' => __('Search by Name')])->label(false) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <?= GridView::widget([
            'id' => 'data-grid',
            'toggleAttribute' => 'active',
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'publicationMethodical.name',
                    'format' => 'raw',
                    //'header' => __('Name of Methodical Publication'),
                    'value' => function ($data) {
                        if($data->is_checked_by_author){
                            return Html::a($data->publicationMethodical->name, ['science/publication-methodical-edit', 'id' => $data->_publication_methodical], ['data-pjax' => 0]);
                        }
                        else{
                            return Html::a($data->publicationMethodical->name, '#', [
                                'class' => 'showModalButton ',
                                'modal-class' => 'modal-lg',
                                'title' => $data->publicationMethodical->name,
                                'value' => Url::to(['science/publication-methodical',
                                    'id' => $data->_publication_methodical
                                ]),
                                'data-pjax' => 0
                            ]);
                        }

                    },
                ],
                [
                   // 'attribute' => 'authors',
                    'attribute' => 'publicationMethodical.authors',
                    //'header' => __('Authors'),

                    'value' => function ($data) {
                        return $data->publicationMethodical->authors;
                    },
                ],
                [
                    'attribute' => 'publicationMethodical.issue_year',
                    //'header' => __('Issue Year'),
                    'value' => function ($data) {
                        return $data->publicationMethodical->issue_year;
                    },
                ],
                [
                    'attribute' => 'publicationMethodical._methodical_publication_type',
                   // 'header' => __('Methodical Publication Type'),
                    'format' => 'raw',
                    'value' => function ($data) {
                        @$type = $data->publicationMethodical->methodicalPublicationType->name;
                        $filename = "";
                        if ($data->publicationMethodical->filename) {
                            @$filename  = Html::a(@$data->publicationMethodical->filename['name'], @$data->publicationMethodical->filename['base_url'] . '/' . @$data->publicationMethodical->filename['path'], ['target'=>'_blank', 'data-pjax'=>0]);
                        }
                        return sprintf("%s<p class='text-muted'>%s </p>", $type, @$filename);
                    },
                    //'header' => __('Structure Department'),
                ],
                [
                    'attribute' => '_employee',
                    'value' => function ($data) {
                    $res = "";
                        foreach ($data->publicationMethodical->publicationAuthors as $item) {
                            $res .= $item->employee->fullName. '; ';
                        }
                    return substr($res, 0,-2);
                    },
                ],
                [
                    'attribute' => 'is_checked_by_author',
                    'header' => __('Is Checked By Author'),
                    'format' => 'raw',
                    'value' => function (EPublicationAuthorMeta $data) {
                        $color = ($data->is_checked_by_author === EPublicationAuthorMeta::STATUS_ENABLE ? "green" : "red");
                        return '<span class="text text-'.$color.'">'.$data->aprovedAuthorOptions[$data->is_checked_by_author].'</span>';
                    },
                   // 'value' => 'employee.fullName',
                ],
                [
                    'attribute' => 'id',
                    'header' => __('Author Request'),
                    'format' => 'raw',
                    'value' => function (EPublicationAuthorMeta $data) {
                        $res = "";
                        if($data->is_main_author){
                            $res = EPublicationAuthorMeta::getAuthorRequest(EPublicationAuthorMeta::PUBLICATION_TYPE_METHODICAL, $data->_publication_methodical);
                            return ($res>0 ? Html::a('<span class="badge badge-warning">'.$res.'</span>', ['science/publication-methodical-edit', 'id' => $data->_publication_methodical], ['data-pjax' => 0, 'style'=>'color: white']) : '');
                        }
                        else{
                            return '';
                        }
                    },
                    // 'value' => 'employee.fullName',
                ],
                /* [
                     'attribute' => 'updated_at',
                     'format' => 'raw',
                     'value' => function ($data) {
                         return Yii::$app->formatter->asDatetime($data->updated_at->getTimestamp());
                     },
                 ],*/
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