<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%e_contract_price}}`.
 */
class m211108_125212_add_column_to_e_contract_price_table extends Migration
{
    public function safeUp()
    {
        $this->renameColumn(
            'e_contract_price',
            '_student_type',
            '_citizenship_type'
        );
        $this->addForeignKey(
            'fk_citizenship_type_e_contract_price_fkey',
            'e_contract_price',
            '_citizenship_type',
            'h_citizenship_type',
            'code',
            'SET NULL',
            'CASCADE'
        );

        $this->addColumn('e_contract_price', '_student_type', $this->string(64)->defaultValue('11'));
        $this->addForeignKey(
            'fk_h_student_type_e_contract_price_fkey',
            'e_contract_price',
            '_student_type',
            'h_student_type',
            'code',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('e_contract_price', '_student_type');
        $this->renameColumn(
            'e_contract_price',
            '_citizenship_type',
            '_student_type '
        );
    }
}
