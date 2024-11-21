<?php

namespace frontend\controllers\api;

use common\models\archive\EStudentDiploma;
use common\models\finance\EStudentContract;
use common\models\finance\EStudentContractInvoice;
use common\models\structure\EUniversity;
use frontend\models\archive\StudentReference;
use Yii;
use yii\web\Response;
use yii\db\Expression;

class InfoController extends \yii\rest\Controller
{
    public $layout = 'main';

    public function actionHello($param)
    {
        return ['name' => $param];
    }

    public function actionHtml()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;

        return $this->render('html');
    }

    public function actionContract()
    {
        $this->layout = 'empty';
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;

        if ($code = $this->get('param')) {
            if ($selected = EStudentContract::findOne(['id' => $code])) {
                $univer = EUniversity::findCurrentUniversity();
            }
        }

        return $this->render('contract', [
            'selected' => $selected,
            'univer' => $univer,
        ]);
    }

    public function actionDiploma()
    {
        $this->layout = 'empty';
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;

        if ($code = $this->get('param')) {
            if ($selected = EStudentDiploma::findOne(['hash' => $code])) {

            }
        }

        return $this->render('diploma', [
            'selected' => $selected,
        ]);
    }

    public function actionInvoice()
    {
        $this->layout = 'empty';
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;

        if ($code = $this->get('param')) {
            if ($selected = EStudentContractInvoice::findOne(['hash' => $code])) {
                $univer = EUniversity::findCurrentUniversity();
            }
        }

        return $this->render('invoice', [
            'selected' => $selected,
            'univer' => $univer,
        ]);
    }

    public function actionReference()
    {
        $this->layout = 'empty';
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;

        if ($code = $this->get('param')) {
            if ($selected = StudentReference::findOne(['hash' => $code])) {
                $univer = EUniversity::findCurrentUniversity();
            }
        }

        return $this->render('reference', [
            'model' => $selected,
            'univer' => $univer,
        ]);
    }

    protected function get($name = null, $default = null)
    {
        return Yii::$app->request->get($name, $default);
    }
}