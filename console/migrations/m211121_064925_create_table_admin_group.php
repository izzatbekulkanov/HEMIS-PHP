<?php

use common\models\system\classifier\TeacherPositionType;
use yii\db\Migration;

/**
 * Class m211121_064925_create_table_admin_group
 */
class m211121_064925_create_table_admin_group extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('e_admin_group', [
            'id' => $this->primaryKey(),
            '_admin' => $this->integer(),
            '_group' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk_e_admin_group_admin',
            'e_admin_group',
            '_admin',
            'e_admin',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_e_admin_group_group',
            'e_admin_group',
            '_group',
            'e_group',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $pos = count(TeacherPositionType::getClassifierOptions()) + 1;

        if ($option = TeacherPositionType::importDataCols([
            'code' => '34',
            'version' => -1,
            'uz-UZ' => 'Tyutor',
            "en-US" => "Tutor",
            "oz-UZ" => "Тьютор",
            "ru-RU" > "Тьютор"
        ], $pos)) {
            echo "{$option->name} for TeacherPositionType created\n";
        };
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('e_admin_group');
    }
}
