<?php

namespace backend\widgets\filekit\events;

use yii\base\Event;

/**
 * Class StorageEvent
 * @package backend\widgets\filekit\events
 * @author Eugene Terentev <eugene@terentev.net>
 */
class StorageEvent extends Event
{
    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    public $filesystem;
    /**
     * @var string
     */
    public $path;
}
