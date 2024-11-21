<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 7/9/14
 * Time: 10:05 PM
 */

namespace backend\widgets;

class AceEditorWidget extends \trntv\aceeditor\AceEditor
{

    public function init()
    {
        parent::init();
        $editor_id  = $this->getId();
        $editor_var = 'aceeditor_' . $editor_id;

        $this->getView()->registerJs("
         {$editor_var}.setOptions({
                tabSize: 2,
                useSoftTabs: false,
        });
        ");
    }

}