<pre>
<?php
/* @var $this \backend\components\View */
$this->title = Yii::$app->name;

print_r($this->_user()->getAttributes());
print_r($this->_user()->role->getAttributes());
foreach ($this->_user()->role->resources as $resource) {
    //print_r($resource->getAttributes());
}
?>
    </pre>
