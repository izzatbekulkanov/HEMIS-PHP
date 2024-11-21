<?php

use yii\db\Migration;

/**
 * Class m201118_124058_turn_off_debug
 */
class m201118_124058_turn_off_debug extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $config = "<?php
return [
    'bootstrap' => [],
    'params'    => ['system.enableConfig'=>true],
    'modules'   => [
        'debug' => [
            'class'           => 'yii\\debug\\Module',
            'enableDebugLogs' => false,
            'allowedIPs'      => [],
            'panels' => [
                'queue'   => [
                    'class' => 'yii\\queue\\debug\\Panel',
                ],
                'httpclient' => [
                    'class' => 'yii\\httpclient\\debug\\HttpClientPanel',
                ],
            ],
        ],
    ]
];
        ";
        file_put_contents(Yii::getAlias('@common' . DS . 'config' . DS . 'main-local.php'), $config);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
    }

}
