<?php

use common\models\archive\EAcademicRecord;
use common\models\curriculum\GradeType;
use common\models\curriculum\MarkingSystem;
use yii\db\Migration;

/**
 * Class m210416_090401_migrate_e_academic_record_grade
 */
class m210416_090401_migrate_e_academic_record_grade extends Migration
{
    public function safeUp()
    {

        /**
         * @var $model EAcademicRecord
         */
        $records = EAcademicRecord::find()
            ->with(['performance', 'curriculum', 'subject', 'student', 'semester'])
            ->all();

        foreach ($records as $model) {
            if ($model->performance) {
                $five = GradeType::getGradeByCode($model->curriculum->_marking_system, GradeType::GRADE_TYPE_FIVE);
                $four = GradeType::getGradeByCode($model->curriculum->_marking_system, GradeType::GRADE_TYPE_FOUR);
                $three = GradeType::getGradeByCode($model->curriculum->_marking_system, GradeType::GRADE_TYPE_THREE);


                if ($model->curriculum->_marking_system == MarkingSystem::MARKING_SYSTEM_FIVE) {
                    $model->grade = $model->performance->grade;
                } else {
                    if ($model->performance->grade >= $five->min_border) {
                        $model->grade = $five->name;
                    } elseif ($model->performance->grade >= $four->min_border) {
                        $model->grade = $four->name;
                    } elseif ($model->performance->grade >= $three->min_border) {
                        $model->grade = $three->name;
                    }
                }
                if ($model->updateAttributes(['grade' => $model->grade])) {
                    echo sprintf("Academic record of %s for %s semester on %s subject updated\n",
                        $model->student->getFullName(),
                        $model->semester->name,
                        $model->subject->name
                    );
                }
            }
        }
    }

    public function safeDown()
    {

    }
}
