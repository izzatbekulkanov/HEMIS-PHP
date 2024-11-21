<?php

use common\models\archive\EStudentDiploma;
use common\models\curriculum\ECurriculum;
use common\models\student\EGroup;
use common\models\student\ESpecialty;
use common\models\student\EStudentMeta;
use common\models\system\_BaseModel;
use yii\db\ActiveRecord;
use yii\db\Migration;

/**
 * Class m200930_183317_alter_relation_speciality
 */
class m200930_183317_alter_relation_speciality extends Migration
{
    public function safeUp()
    {
        //Adds new column
        $this->addColumn('e_curriculum', '_specialty_id', $this->integer()->null());
        $this->addColumn('e_group', '_specialty_id', $this->integer()->null());
        $this->addColumn('e_student_meta', '_specialty_id', $this->integer()->null());
        $this->addColumn('e_student_diploma', '_specialty_id', $this->integer()->null());

        //Adds foreign keys
        $this->addForeignKey(
            'fk_e_curriculum_specialty_id',
            'e_curriculum',
            '_specialty_id',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_group_specialty_id',
            'e_group',
            '_specialty_id',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_meta_specialty_id',
            'e_student_meta',
            '_specialty_id',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_student_diploma_specialty_id',
            'e_student_diploma',
            '_specialty_id',
            'e_specialty',
            'id',
            'RESTRICT',
            'CASCADE'
        );


        //Link specialty IDs
        /**
         * @var $object _BaseModel
         * @var $class ActiveRecord
         * @var $specialty ESpecialty
         */
        $classes = [
            EGroup::class,
            ECurriculum::class,
            EStudentMeta::class,
            EStudentDiploma::class,
        ];

        foreach (ESpecialty::find()->all() as $specialty) {
            foreach ($classes as $class) {
                $count = $class::updateAll(['_specialty_id' => $specialty->id], ['_specialty' => $specialty->code, '_department' => $specialty->_department]);

                if ($count) {
                    echo "$count of $class objects updated successfully\n";
                }
            }
        }

        //Drop old columns
        $this->dropColumn('e_curriculum', '_specialty');
        $this->dropColumn('e_group', '_specialty');
        $this->dropColumn('e_student_meta', '_specialty');
        $this->dropColumn('e_student_diploma', '_specialty');
    }

    public function safeDown()
    {
        $this->addColumn('e_curriculum', '_specialty', $this->string(64)->null());
        $this->addColumn('e_group', '_specialty', $this->string(64)->null());
        $this->addColumn('e_student_meta', '_specialty', $this->string(64)->null());
        $this->addColumn('e_student_diploma', '_specialty', $this->string(64)->null());


        //Link specialty codes
        /**
         * @var $object _BaseModel
         * @var $class ActiveRecord
         * @var $specialty ESpecialty
         */
        $classes = [
            EGroup::class,
            ECurriculum::class,
            EStudentMeta::class,
            EStudentDiploma::class,
        ];

        foreach (ESpecialty::find()->all() as $specialty) {
            foreach ($classes as $class) {
                $count = $class::updateAll(['_specialty' => $specialty->code], ['_specialty_id' => $specialty->id]);

                if ($count) {
                    echo "$count of $class objects reverted successfully\n";
                }
            }
        }

        $this->dropForeignKey('fk_e_curriculum_specialty_id', 'e_curriculum');
        $this->dropForeignKey('fk_e_student_meta_specialty_id', 'e_student_meta');
        $this->dropForeignKey('fk_e_student_diploma_specialty_id', 'e_student_diploma');
        $this->dropForeignKey('fk_e_group_specialty_id', 'e_group');

        $this->dropColumn('e_curriculum', '_specialty_id');
        $this->dropColumn('e_group', '_specialty_id');
        $this->dropColumn('e_student_meta', '_specialty_id');
        $this->dropColumn('e_student_diploma', '_specialty_id');
    }
}
