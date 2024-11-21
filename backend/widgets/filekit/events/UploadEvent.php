<?php

namespace backend\widgets\filekit\events;
use yii\base\Event;

/**
 * Class UploadEvent
 * @package backend\widgets\filekit\events
 * @author Eugene Terentev <eugene@terentev.net>
 */
class UploadEvent extends Event
{
    /**
     * @var mixed
     */
    public $filesystem;
    /**
     * @var string
     */
    public $path;
    /**
     * @var \League\Flysystem\File|null
     */
    public $file;
}
