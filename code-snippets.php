<?php
############################
#Tizimda kodlash namunalari#
############################
/**
 *
 *  1. HEMIS Ministry tizimi bilan avtosinxronizatsiya.
 *
 * Avtosinxronizatsiyaga modelni qo'shib qo'yish. Bunda model asosiy
 * koddan asinxron tarzda cron va queue yordamida API ga jo'natiladi.
 * @var $model common\models\student\EStudent
 */

//bir dona model bo'lgan holatda
$model->setAsShouldBeSynced();
// yoki bittadan ko'p model bo'lgan holatda
$studentIds = [];
// idsi shu massivdagi idlardan biriga teng bo'lgan modellarni update qilish
common\models\student\EStudent::updateAll(['_sync' => false], ['id' => $studentIds]);

/**
 * HemisApiSyncModel ning bola klasi bo'lgan barcha klasslar API bilan
 * sinxronizatsiya qilinadi. Ularning barchasiga yuqoridagi qoidani ishlatish
 * mumkin. Ularning ro'yxati:
 * - EUniversity
 * - EDepartment
 * - EStudent
 * - EEmployee
 * - EEmployeeMeta
 * - EDoctorateStudent
 * - EDissertationDefense
 * - EStudentDiploma
 * - EDiplomaBlank
 * - EScientificPlatformProfile
 * - EPublicationProperty
 * - EPublicationScientific
 * - EPublicationMethodical
 * - EProjectMeta
 * - EProjectExecutor
 * - EProject
 *
 * Demak shu classdagi qaysidir modellarda o'zgarish bo'ladigan bo'lsa ularni
 * sinxronizatsiyaga navbatga qo'yish shart bo'ladi.
 *
 */

/**
 * 2. Admin backendda joriy foydalanuvchu rolini aniqlash
 *
 * @var $user \common\models\system\Admin
 */

if ($user->role->isSuperAdminRole()) {
    //foydalanuvchi superadmin rolida
}
if ($user->role->isDeanRole()) {
    //foydalanuvchi dekan rolida
}
if ($user->role->isTeacherRole()) {
    //foydalanuvchi o'qutivchi rolida
}

/**
 * 3. Success yoki error xabar chiqarish hamda tizim jurnaliga qayd etish
 *
 * @var $this \backend\controllers\BackendController
 */
$message = __('Subject data updated successfully');
//ham logga yozadi ham notification chiqaradi
$this->addSuccess($message, true, true);
//logga yozadi lekin notification chiqarmaydi
$this->addError(__('Could not delete the subject data'), true, false);
//logga yozmaydi va notification chiqaradi
$this->addSuccess($message, false, true);

/**
 * 4. Klassifikatorlarni massivga $code=>$label sifatida olish. Masalan selectbox yaratishda ishlatiladi.
 * Tizimda 60 ga yaqin klassifikatorlar mavjud, barchasi _BaseClassifier clasidan extend bo'lgan va
 * common/models/system/classifier namespaceda joylashgan, masalan:
 * - AcademicDegree
 * - EducationType
 * - University
 * - Qualification
 * ...
 * - WeekGraphicType
 *
 * Ayrim klassifikatorlar parent-child ko'rinishida bog'langan, masalan Soato, MasterSpecialty, BachelorSpecialty
 */
//bu holatda klassifikator codi tartibida saralanadi
$options = \common\models\system\classifier\EducationType::getClassifierOptions();
//bu hoaltda klassifikator nomi tartibida saralanadi
$options = \common\models\system\classifier\EducationForm::getClassifierOptionsByName();
//faqatgina parent klassifikatorlarni olish
$viloyatlar = \common\models\system\classifier\Soato::getParentClassifierOptions();