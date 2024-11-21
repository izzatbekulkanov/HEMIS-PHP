<?php

namespace common\models\system\classifier;

class PaymentForm extends _BaseClassifier
{
    const PAYMENT_FORM_BUDGET = '11';
    const PAYMENT_FORM_CONTRACT = '12';

    public static function tableName()
    {
        return 'h_payment_form';
    }
}