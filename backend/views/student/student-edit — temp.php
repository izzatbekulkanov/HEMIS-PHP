<?php
use common\components\Config;
use common\models\system\classifier\EducationYear;
use common\models\system\classifier\PaymentForm;
use common\models\system\classifier\Nationality;
use common\models\system\classifier\CitizenshipType;
use common\models\system\classifier\Country;
use common\models\system\classifier\Soato;
use common\models\system\classifier\Gender;
use common\models\system\classifier\Course;
use common\models\system\classifier\Accommodation;
use common\models\system\classifier\SocialCategory;
use common\models\student\ESpecialty;
use common\models\student\EGroup;
use common\models\curriculum\Semester;
use kartik\select2\Select2;
use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii2mod\chosen\ChosenSelect;
use yii\widgets\MaskedInput;
use kartik\date\DatePicker;
use kartik\depdrop\DepDrop;


/* @var $this \backend\components\View */
/* @var $model common\models\system\Admin */

$this->title = $model->isNewRecord ? __('Create Student') : $model->fullName;
$this->params['breadcrumbs'][] = ['url' => ['student/student'], 'label' => __('Student')];
$this->params['breadcrumbs'][] = $this->title;
$user = $this->context->_user();
?>

<style type="text/css">
    fieldset.scheduler-border {
    border: 1px groove #ddd !important;
    padding: 0 1.4em 1.4em 1.4em !important;
    margin: 0 0 1.5em 0 !important;
    -webkit-box-shadow:  0px 0px 0px 0px #000;
            box-shadow:  0px 0px 0px 0px #000;
}

    legend.scheduler-border {
        font-size: 1.2em !important;
        font-weight: bold !important;
        text-align: left !important;
        width:auto;
        padding:0 10px;
        border-bottom:none;
    }
</style>
<?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>

<div class="row">
    <div class="col col-md-12">
        <div class="box box-default ">

            <div class="box-body">
                <div class="row">
                    
				<fieldset class="scheduler-border">
                <legend class="scheduler-border"><?php echo Yii::t('app','Ta\'lim ma\'lumotlari');?></legend>
					<div class="row">
						
						<div class="col-md-2">
							<?= $form->field($contingent, '_education_year')->widget(Select2::classname(), [
								'data' => ArrayHelper::map(EducationYear::find()->all(), 'code', 'name'),
								'language' => 'en',
								'options' => [
									'placeholder' => __('-Choose-'),
									'id' => '_education_year'
								],
								'pluginOptions' => [
									'allowClear' => true
								],
							]) ?>
							
						</div>
						<div class="col-md-8">
							<?= $form->field($contingent, '_specialty')->widget(Select2::classname(), [
								'data' => ArrayHelper::map(ESpecialty::find()->where(['in', '_education_type', array('11','12')])->all(), 'code', 'fullName'),
								'language' => 'en',
								'options' => [
									'placeholder' => __('-Choose-'),
									'id' => '_specialty'
								],
								'pluginOptions' => [
									'allowClear' => true
								],
							]) ?>
							
						</div>
						<div class="col-md-2">    
						 <?= $form->field($contingent, '_payment_form')->widget(Select2::classname(), [
							'data' => ArrayHelper::map(PaymentForm::find()->all(), 'code', 'name'),
							'language' => 'en',
							'options' => [
								'placeholder' => __('-Choose-'),
								'id' => '_payment_form',
							],
							'pluginOptions' => [
								'allowClear' => true
							],
						]) ?>
						</div>
					   <?php
						 if($contingent->_group!=""){
					   ?>
							<div class="col-md-4">
								<?= $form->field($contingent, '_group')->widget(Select2::classname(), [
									'data' => ArrayHelper::map(EGroup::find()->all(), 'id', 'name'),
									'language' => 'en',
									'options' => [
										'placeholder' => __('-Choose-'),
										'id' => '_group'
									],
									'pluginOptions' => [
										'allowClear' => true
									],
								]) ?>
							</div>
							<div class="col-md-4">    
								<?= $form->field($contingent, '_level')->widget(Select2::classname(), [
									'data' => ArrayHelper::map(Course::find()->all(), 'code', 'name'),
									'language' => 'en',
									'options' => [
										'placeholder' => __('-Choose-'),
										'id' => '_level',
									],
									'pluginOptions' => [
										'allowClear' => true
									],
								]) ?>
						   </div>
						   <div class="col-md-4">
								<?= $form->field($contingent, '_semestr')->widget(Select2::classname(), [
									'data' => ArrayHelper::map(Semester::find()->all(), 'code', 'name'),
									'language' => 'en',
									'options' => [
										'placeholder' => __('-Choose-'),
										'id' => '_semestr'
									],
									'pluginOptions' => [
										'allowClear' => true
									],
								]) ?>
								
							</div>
					   <?php } ?>
					 </div>   
            
           
				</fieldset>
				
				<fieldset class="scheduler-border">
            <legend class="scheduler-border"><?php echo Yii::t('app','Asosiy ma\'lumotlari');?></legend>
            <div class="row">
                 <div class="col col-md-9">
                     <div class="row">
                         <div class="col-md-4">
                             <?= $form->field($model, '_citizenship')->widget(Select2::classname(), [
                                 'data' => ArrayHelper::map(CitizenshipType::find()->all(), 'code', 'name'),
                                 'language' => 'en',
                                 'options' => ['placeholder' => __('-Choose-')],
                                 'pluginOptions' => [
                                     'allowClear' => true
                                 ],
                             ]) ?>
                         </div>
                         <div class="col-md-4">
                             <?= $form->field($model, 'passport_number')->widget(MaskedInput::className(), [
                                 'name' => 'input-5',
                                 'mask' => ['AA9999999'],
                                 'options' => [
                                     'id' => 'passport_number',
                                     'class' => 'form-control'
                                 ],
                                 'clientOptions' => [
                                     'clearIncomplete' => true,
                                     'greedy' => true
                                 ]
                             ]) ?>
                         </div>
                         <div class="col-md-4">
                             <?= $form->field($model, 'passport_pin')->widget(MaskedInput::className(), [
                                 'name' => 'input-5',
                                 'mask' => ['99999999999999'],
                                 'options' => [
                                     'id' => 'passport_pin',
                                     'class' => 'form-control'
                                 ],
                                 'clientOptions' => [
                                     'clearIncomplete' => true,
                                     'greedy' => true
                                 ]
                             ]) ?>
                             <?//= $form->field($model, 'passport_pin')->textInput(['maxlength' => 14, 'minlength' =>14]); ?>
                         </div>
                     </div>
                     <div class="row">
                         <div class="col-md-4">
                                <?= $form->field($model, 'second_name')->textInput(['maxlength' => true, 'id'=>'second_name']) ?>
                         </div>
                         <div class="col-md-4">
                            <?= $form->field($model, 'first_name')->textInput(['maxlength' => true, 'id'=>'first_name']) ?>
                         </div>
                         <div class="col-md-4">
                            <?= $form->field($model, 'third_name')->textInput(['maxlength' => true, 'id'=>'third_name']) ?>
                         </div>
                         
                     </div>

                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'birth_date')->widget(DatePicker::classname(), [
                                'options' => [
                                        'placeholder' => 'Enter birth date',
                                        'id' => 'birth_date'
                                ],
                                'pluginOptions' => [
                                    'autoclose'=>true,
                                    'format' => 'yyyy-mm-dd'
                                ]
                            ]); ?>

                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, '_gender')->widget(Select2::classname(), [
                                'data' => ArrayHelper::map(Gender::find()->all(), 'code', 'name'),
                                'language' => 'en',
                                'options' => [
                                        'placeholder' => __('-Choose-'),
                                        'id' => '_gender'
                                    ],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                             ]) ?>
                        </div>
                        <?= $form->field($model, '_gender')->hiddenInput(['id'=>'hidden_gender'])->label(false); ?>

                        <div class="col-md-4">
                            <?= $form->field($model, '_nationality')->widget(Select2::classname(), [
                                'data' => ArrayHelper::map(Nationality::find()->all(), 'code', 'name'),
                                'language' => 'en',
                                'options' => ['placeholder' => __('-Choose-')],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                             ]) ?>
                        </div>
                    </div>


                   </div>

                    <div class="col col-md-3">
                        <?= $form->field($model, 'image')
                            ->widget(Upload::className(), [
                                'url' => ['dashboard/file-upload', 'type' => 'profile'],
                                'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                                'sortable' => true,
                                'maxFileSize' => \common\components\Config::getUploadMaxSize(), // 10 MiB
                                'maxNumberOfFiles' => 1,
                                'multiple' => false,
                                'clientOptions' => [],
                            ]); ?>
                    </div>
		

		</div>
        <div class="row">
           
             <div class="col-md-3">
                    <?= $form->field($model, '_country')->widget(Select2::classname(), [
                        'data' => ArrayHelper::map(Country::find()->all(), 'code', 'name'),
                        'language' => 'en',
                        'options' => [
                            'placeholder' => __('-Choose-'),
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]) ?>
              </div>
                <div class="col-md-3">
                    <?= $form->field($model, '_province')->widget(Select2::classname(), [
                        'data' => ArrayHelper::map(Soato::find()->where(['_parent'=>null])->all(), 'code', 'name'),
                        'language' => 'en',
                        'options' => [
                            'placeholder' => __('-Choose-'),
                            'id' => '_province',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]) ?>
                </div>
                <?php 
                    $regions = Soato::find()
                      //  ->where(['parent_code'=>$model->_province])
                        //->andWhere(['in', 'type', array(1,2)])
                        ->all();
                ?>
                <div class="col-md-3">
                     <?= $form->field($model, '_district')->widget(DepDrop::classname(), [
                            'data' =>  ArrayHelper::map($regions, 'code','name'),
                            'language' => 'en',
                            'type' => DepDrop::TYPE_SELECT2,
                            'select2Options'=>['pluginOptions'=>['allowClear'=>true]],  
                            'options' => [
                                'placeholder' => __('-Choose-'),
                                'id' => '_district',
                            ],
                            'pluginOptions' => [
                                'depends'=>['_province'],
                                'placeholder' => __('-Choose-'),
                                'url'=>Url::to(['/ajax/get_region'])
                            ],
                    ])?>
                </div>
                 <div class="col-md-3">
                    <?= $form->field($model, 'home_address')->textInput(['maxlength' => true, 'id'=>'home_address']) ?>
                 </div>
                          
            </div>
        
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, '_social_category')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(SocialCategory::find()->all(), 'code', 'name'),
                    'language' => 'en',
                    'options' => [
                        'placeholder' => __('-Choose-'),
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]) ?>
            </div>
            <?php
                for($i=date('Y'); $i > (date('Y')-7);$i--)
                    $years [$i] = $i;
            ?>
            <div class="col-md-3">
                <?= $form->field($model, 'year_of_enter')->widget(Select2::classname(), [
                    'data' => $years,
                    'language' => 'en',
                    'options' => [
                        'placeholder' => __('-Choose-'),
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]) ?>
            </div>

            <div class="col-md-3">
                <?= $form->field($model, '_accommodation')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(Accommodation::find()->all(), 'code', 'name'),
                    'language' => 'en',
                    'options' => [
                        'placeholder' => __('-Choose-'),
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'current_address')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'other')->textArea(['maxlength' => true]) ?>
			</div>	
        </div>    

        </fieldset>
         </div>
            <div class="box-footer text-right">
                <?php if (!$model->isNewRecord): ?>
                    <?= $this->getResourceLink(__('Delete'), ['student/student-delete', 'id' => $model->id], ['class' => 'btn btn-danger btn-flat btn-delete']) ?>
                <?php endif; ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . __('Save'), ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
    <script>
        var base_url = '<?= \Yii::$app->request->hostInfo; ?>';
    </script>
    <?php
    $script = <<< JS
    $('#passport_number').trigger('keyup');
    $('#passport_pin').trigger('keyup');
    $('#passport_number').keyup(function(){
        var num = $(this).val();
        var pin =  $('#passport_pin').val();
        if (num.length === 9 && pin.length === 14) {
            console.log(num.search('_'));
            if (num.search('_') == -1) {
            $.ajax({
                url: base_url + '/ajax/edu-student-info',
                type:"POST",            
                data: {passport: num, pin: pin },
                dataType:"json",
                success:function(data){                
                    $("#first_name").val(data.first_name);
                    $("#second_name").val(data.second_name);
                    $("#third_name").val(data.third_name);
                    //$('#birth_date').datepicker('setDate', data.birth_date);
                    $('#birth_date').val(data.birth_date);
                   // $("#birth_date").val(data.birth_date);
                    //$("#birth_date").html(data.birth_date);
                    $("#_gender").html(data.gender);
			        $("#hidden_gender").val(data.hidden_gender);

                    $("#home_address").val(data.home_address);
                   
                }
            });
         }
        }
    });

    $('#passport_pin').keyup(function(){
        var pin = $(this).val();
        var num =  $('#passport_number').val();
        if (num.length === 9 && pin.length === 14) {
            console.log(num.search('_'));
            if (pin.search('_') == -1) {
            $.ajax({
                url: base_url + '/ajax/edu-student-info',
                type:"POST",            
                data: {passport: num, pin: pin },
                dataType:"json",
                success:function(data){            
                    $("#first_name").val(data.first_name);
                    $("#second_name").val(data.second_name);
                    $("#third_name").val(data.third_name);
                    //$('#birth_date').datepicker('setDate', data.birth_date);
                    $('#birth_date').val(data.birth_date);
                    
//                    $("#birth_date").val(data.birth_date);
                   // $("#birth_date").html(data.birth_date);
                     $("#_gender").html(data.gender);
                     $("#hidden_gender").val(data.hidden_gender);
                     
                     
                    $("#home_address").val(data.home_address);
                }
            });
         }
        }
    });

JS;
    $this->registerJs($script);
    ?>
