<?php

namespace backend\widgets;

use yii\base\Widget;
use Yii;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Class HeatMap
 * @property array options
 * @package backend\widgets
 *
 * Usage example:
 * ~~~
 *
 *     HeatMap::widget([
 *                         'options'       => [
 *                             'id' => 'heatmap',
 *                         ],
 *                         'clientOptions' => [
 *                             'data'                 => $data,
 *                             'domain'               => 'month',
 *                             'tooltip'              => true,
 *                             'itemName'             => "student",
 *                             'subDomainTitleFormat' => new JsExpression('{
 *                                    empty: "on {date} no students",
 *                                    filled: "{count} {name} arrival on {date}"
 *                                }'),
 *                             'legend'               => [5, 10, 15, 20],
 *                             'onClick' => new JsExpression('function (date, nb) {
 *                                    console.log(nb);
 *                              }'),
 *                          ],
 *                      ]);
 * ~~~
 */
class HeatMap extends Widget
{

    /**
     * @var array client options
     */
    public $options       = [];
    public $clientOptions = [];

    /**
     * Init widget, configure client options
     *
     * @return void
     */
    public function init()
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        //$this->configureClientOptions();
        parent::init();
    }

    /**
     * Render calendar map
     *
     * @return string
     */
    public function run()
    {
        $this->registerAssets();
        return Html::tag('div', '', $this->options);
    }

    /**
     * Register client assets
     *
     * return @void
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        //HeatMapAsset::register($view);
        $this->clientOptions['itemSelector'] = "#" . $this->options['id'];
        $clientOptions                       = Json::encode($this->clientOptions);
        $js[]                                = "var cal = new CalHeatMap();";
        $js[]                                = "cal.init($clientOptions);";
        $view->registerJs(implode("\n", $js));
    }

}
