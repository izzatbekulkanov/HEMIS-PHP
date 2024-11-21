<?php

use common\models\archive\EStudentDiploma;
use common\models\employee\EEmployee;
use common\models\employee\EEmployeeMeta;
use common\models\structure\EDepartment;
use common\models\student\EStudent;
use common\models\system\SystemClassifier;
use yii\db\Migration;

/**
 * Class m200908_183827_alter_table_sync_tables
 */
class m200908_183827_alter_table_sync_tables extends Migration
{

    public function safeUp()
    {
        $this->addColumn(SystemClassifier::tableName(), '_qid', $this->bigInteger());
        $this->addColumn(EStudent::tableName(), '_qid', $this->bigInteger());
        $this->addColumn(EStudentDiploma::tableName(), '_qid', $this->bigInteger());
        $this->addColumn(EEmployee::tableName(), '_qid', $this->bigInteger());
        $this->addColumn(EEmployeeMeta::tableName(), '_qid', $this->bigInteger());
        $this->addColumn(EDepartment::tableName(), '_qid', $this->bigInteger());
    }

    public function safeDown()
    {
        $this->dropColumn(SystemClassifier::tableName(), '_qid');
        $this->dropColumn(EStudent::tableName(), '_qid');
        $this->dropColumn(EStudentDiploma::tableName(), '_qid');
        $this->dropColumn(EEmployee::tableName(), '_qid');
        $this->dropColumn(EEmployeeMeta::tableName(), '_qid');
        $this->dropColumn(EDepartment::tableName(), '_qid');
    }
}
