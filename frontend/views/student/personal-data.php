<?php
use yii\widgets\DetailView;
$this->title = __('Student Personal Data');
?>
<div class="row">
    <div class="col col-md-4 col-lg-8">
        <div class="box box-primary ">
            <div class="box-header with-border">
                <h3 class="box-title"> <?= __('Passport Information'); ?></h3>
            </div>

            <div class="box-body no-padding">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => '_citizenship',
                            'contentOptions' => ['style' => 'width:60%; white-space: normal;'],
                            'value' => function ($data) {
                                return $data->citizenship ? $data->citizenship->name : '';
                            }
                        ],
                        [
                            'attribute' => 'passport_number',
                            'value' => function ($data) {
                                return $data->passport_number;
                            }
                        ],

                        [
                            'attribute' => 'passport_pin',
                            'value' => function ($data) {
                                return $data->passport_pin;
                            }
                        ],
                        [
                            'attribute' => 'second_name',
                            'label'=>__('Second Name'),
                            'value' => function ($data) {
                                return $data->second_name ? $data->second_name : '';
                            }
                        ],
                        [
                            'attribute' => 'first_name',
                            'label'=>__('First Name'),
                            'value' => function ($data) {
                                return $data->first_name ? $data->first_name : '';
                            }
                        ],
                        [
                            'attribute' => 'third_name',
                            'value' => function ($data) {
                                return $data->third_name ? $data->third_name : '';
                            }
                        ],
                        [
                            'attribute' => 'birth_date',
                            'value' => function ($data) {
                                return Yii::$app->formatter->asDate($data->birth_date, 'dd-MM-Y');
                            }
                        ],
                        [
                            'attribute' => '_gender',
                            'value' => function ($data) {
                                return $data->gender ? $data->gender->name : '';
                            }
                        ],
                        [
                            'attribute' => '_nationality',
                            'value' => function ($data) {
                                return $data->nationality ? $data->nationality->name : '';
                            }
                        ],

                    ],
                ]) ?>
            </div>

        <div class="box box-primary ">
            <div class="box-header with-border">
                <h3 class="box-title"> <?= __("Qo'shimcha ma'lumotlar"); ?></h3>
            </div>

            <div class="box-body no-padding">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => '_social_category',
                            'contentOptions' => ['style' => 'width:60%; white-space: normal;'],
                            'value' => function ($data) {
                                return $data->socialCategory ? $data->socialCategory->name : '';
                            }
                        ],
                        [
                            'attribute' => '_student_type',
                            'value' => function ($data) {
                                return $data->studentType ? $data->studentType->name : '';
                            }
                        ],
                        [
                            'attribute' => 'other',
                            'value' => function ($data) {
                                return $data->other ? $data->other : '';
                            }
                        ],

                    ],
                ]) ?>
            </div>

            <br/>

        <div class="box box-primary ">
            <div class="box-header with-border">
                <h3 class="box-title"> <?= __('Address Information'); ?></h3>
            </div>

            <div class="box-body no-padding">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => '_country',
                            'contentOptions' => ['style' => 'width:60%; white-space: normal;'],
                            'value' => function ($data) {
                                return $data->country ? $data->country->name : '';
                            }
                        ],
                        [
                            'attribute' => '_province',
                            'value' => function ($data) {
                                return $data->province ? $data->province->name : '';
                            }
                        ],
                        [
                            'attribute' => '_district',
                            'value' => function ($data) {
                                return $data->district ? $data->district->name : '';
                            }
                        ],
                        [
                            'attribute' => 'home_address',
                            'value' => function ($data) {
                                return $data->home_address ? $data->home_address : '';
                            }
                        ],

                    ],
                ]) ?>
            </div>

            <br/>

         <div class="box box-primary ">
            <div class="box-header with-border">
                <h3 class="box-title"> <?= __('Current Address Information'); ?></h3>
            </div>

            <div class="box-body no-padding">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => '_accommodation',
                            'contentOptions' => ['style' => 'width:60%; white-space: normal;'],
                            'value' => function ($data) {
                                return $data->accommodation ? $data->accommodation->name : '';
                            }
                        ],
                        [
                            'attribute' => '_current_province',
                            'value' => function ($data) {
                                return $data->currentProvince ? $data->currentProvince->name : '';
                            }
                        ],
                        [
                            'attribute' => '_current_district',
                            'value' => function ($data) {
                                return $data->currentDistrict ? $data->currentDistrict->name : '';
                            }
                        ],
                        [
                            'attribute' => 'current_address',
                            'value' => function ($data) {
                                return $data->current_address ? $data->current_address : '';
                            }
                        ],
                        [
                            'attribute' => 'roommate_count',
                            'value' => function ($data) {
                                return $data->roommate_count ? $data->roommate_count : '';
                            }
                        ],
                        [
                            'attribute' => '_student_roommate_type',
                            'value' => function ($data) {
                                return $data->studentRoommateType ? $data->studentRoommateType->name : '';
                            }
                        ],
                        [
                            'attribute' => '_student_living_status',
                            'value' => function ($data) {
                                return $data->studentLivingStatus ? $data->studentLivingStatus->name : '';
                            }
                        ],
                        [
                            'attribute' => 'geo_location',
                            'value' => function ($data) {
                                return $data->geo_location ? $data->geo_location : '';
                            }
                        ],
                        [
                            'attribute' => 'email',
                            'value' => function ($data) {
                                return $data->email ? $data->email : '';
                            }
                        ],
                        [
                            'attribute' => 'phone',
                            'value' => function ($data) {
                                return $data->phone ? $data->phone : '';
                            }
                        ],
                        [
                            'attribute' => 'parent_phone',
                            'value' => function ($data) {
                                return $data->parent_phone ? $data->parent_phone : '';
                            }
                        ],
                        [
                            'attribute' => 'person_phone',
                            'value' => function ($data) {
                                return $data->person_phone ? $data->person_phone : '';
                            }
                        ],

                    ],
                ]) ?>
            </div>

             <br>

             <div class="box box-primary ">
                 <div class="box-header with-border">
                     <h3 class="box-title"> <?= __('Education Info'); ?></h3>
                 </div>

                 <div class="box-body no-padding">
                     <?= DetailView::widget([
                         'model' => $model,
                         'attributes' => [
                             [
                                 'attribute' => '_specialty_id',
                                 'label'=>__('Specialty'),
                                 'contentOptions' => ['style' => 'width:60%; white-space: normal;'],
                                 'value' => function ($data) {
                                     return $data->meta->specialty ? $data->meta->specialty->name : '';
                                 }
                             ],
                             [
                                 'attribute' => '_department',
                                 'label'=>__('Faculty'),
                                 'value' => function ($data) {
                                     return $data->meta->department ? $data->meta->department->name : '';
                                 }
                             ],
                             [
                                 'attribute' => '_level',
                                 'label'=>__('Level'),
                                 'value' => function ($data) {
                                     return $data->meta->level ? $data->meta->level->name : '';
                                 }
                             ],
                             [
                                 'attribute' => '_group',
                                 'label'=>__('Group'),
                                 'value' => function ($data) {
                                     return $data->meta->group ? $data->meta->group->name : '';
                                 }
                             ],
                             [
                                 'attribute' => '_payment_form',
                                 'label'=>__('Payment Form'),
                                 'value' => function ($data) {
                                     return $data->meta->paymentForm ? $data->meta->paymentForm->name : '';
                                 }
                             ],
                             [
                                 'attribute' => '_education_type',
                                 'label'=>__('Education Type'),
                                 'value' => function ($data) {
                                     return $data->meta->educationType ? $data->meta->educationType->name : '';
                                 }
                             ],
                             [
                                 'attribute' => '_education_form',
                                 'label'=>__('Education Form'),
                                 'value' => function ($data) {
                                     return $data->meta->educationForm ? $data->meta->educationForm->name : '';
                                 }
                             ],
                             [
                                 'attribute' => '_education_year',
                                 'label'=>__('Education Year'),
                                 'value' => function ($data) {
                                     return $data->meta->educationYear ? $data->meta->educationYear->name : '';
                                 }
                             ],
                             [
                                 'attribute' => '_semestr',
                                 'label'=>__('Semester'),
                                 'value' => function ($data) {
                                     return @$data->meta->semester ? @$data->meta->semester->name : '';
                                 }
                             ],

                         ],
                     ]) ?>
                 </div>


             </div>
    </div>
</div>


