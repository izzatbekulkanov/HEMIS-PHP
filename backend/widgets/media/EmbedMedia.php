<?php

namespace backend\widgets\media;


use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

class EmbedMedia extends Widget
{
    const TYPE_AUDIO = 'audio';
    const TYPE_VIDEO = 'video';
    protected $allowTypes = [
        'audio',
        'video',
    ];
    public    $width;
    public    $height;
    public    $file;
    public    $type       = self::TYPE_AUDIO;
    public    $modal      = false;
    public    $autoplay   = false;
    public    $title      = false;

    public $options;

    public $playerOptions = [];

    private $_mediaAsset;
    private $defaultOptions = [
        'defaultVideoWidth'        => 760,
        'defaultVideoHeight'       => 400,
        'videoWidth'               => -1,
        'videoHeight'              => -1,
        'audioWidth'               => 760,
        'audioHeight'              => 30,
        'startVolume'              => 0.8,
        'loop'                     => false,
        'enableAutosize'           => false,
        'features'                 => ['playpause', 'progress', 'current', 'duration', 'tracks', 'volume', 'fullscreen'],
        'alwaysShowControls'       => false,
        'iPadUseNativeControls'    => false,
        'iPhoneUseNativeControls'  => false,
        'AndroidUseNativeControls' => false,
        'alwaysShowHours'          => false,
        'showTimecodeFrameCount'   => false,
        'framesPerSecond'          => 25,
        'enableKeyboard'           => true,
        'pauseOtherPlayers'        => true,
        'keyActions'               => []
    ];

    public function init()
    {

        $view = $this->getView();

        $this->registerAssets();
        $view->registerJs($this->getJs(), View::POS_READY);


        $this->playerOptions = array_merge($this->playerOptions, $this->defaultOptions);

        $this->options['id']       = $this->id;
        $this->options['title']    = (isset($this->title)) ? $this->title : false;
        $this->options['src']      = (isset($this->file)) ? $this->file : $this->_mediaAsset->baseUrl . '/media/other.mp4';
        $this->options['controls'] = 'controls';
        $this->options['preload']  = 'none';

        $this->options['width']  = (isset($this->width)) ? $this->width : 760;
        $this->options['height'] = (isset($this->height)) ? $this->height : 400;
        parent::init();

    }

    public function run()
    {

        if ($this->modal) {
            $output = self::modal_view();
        } else {
            switch ($this->type) {
                case self::TYPE_VIDEO :
                    $output = Html::tag('video', '', $this->options);
                    break;
                case self::TYPE_AUDIO :
                    $output = Html::tag('audio', '', $this->options);
                    break;
                default :
                    $output = '';
            }
        }

        return $output;
    }

    public function modal_view()
    {

        $modal_id = "modal_" . $this->id;

        $modal_btn = Html::a($this->settings['title'], null, [
            'class'       => "btn btn-default btn",
            'data-toggle' => "modal",
            'data-target' => "#" . $modal_id,
            'type'        => "button",
        ]);


        $output = '<div class="modal fade" id="' . $modal_id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';

        $output .= ($this->options['title']) ? '<h4 class="modal-title" id="myModalLabel">' . $this->title . '</h4>' : ' ';
        $output .= '            </div>
                                <div class="modal-body" id="mediaModal' . $this->id . '">';
        $output .= $this->view();
        $output .= '            </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">Закрыть</button>
                                </div>
                            </div>
                        </div>
                    </div>';

        return ($this->settings['title']) ? $modal_btn . $output : $output;
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $view              = $this->getView();
        $this->_mediaAsset = EmbedMediaAsset::register($view);
    }

    private function getJs()
    {
        $js = [];

//        $this->playerOptions[
//                'defaultVideoWidth' => 480,
//                'defaultVideoHeight' => 270,
//            ]

//        $js [] = '$('#mediaModalw0').width()';
        $js [] = 'var player = new MediaElementPlayer("#' . $this->id . '", ' . Json::encode($this->defaultOptions) . ');';
//        $js [] = '$("video,audio").mediaelementplayer();';
//        $js [] = 'player.pause();';
//        $js [] = 'player.setSrc("mynewfile.mp4");';
//        $js [] = 'player.play();';

//        $js [] = 'var v = document.getElementsByTagName("video")[0];
//        $js [] = 'new MediaElement(v, {success: function(media) {';
//        $js [] = 'media.stop();';
//        $js [] = '}});';

        return implode("\n", $js);
    }

}