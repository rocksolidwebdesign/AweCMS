<?php

/** Zend_Dojo_View_Helper_Dijit */
require_once 'Zend/Dojo/View/Helper/Editor.php';

/** Zend_Json */
require_once 'Zend/Json.php';

class Awe_Dojo_View_Helper_AweEditor extends Zend_Dojo_View_Helper_Editor
{
    protected $_pluginsModules = array(
        'createLink' => 'LinkDialog',
        'insertImage' => 'LinkDialog',
        'fontName' => 'FontChoice',
        'fontSize' => 'FontChoice',
        'formatBlock' => 'FontChoice',
        'foreColor' => 'TextColor',
        'hiliteColor' => 'TextColor',

        // custom
        'enterKeyHandling' => 'EnterKeyHandling',
        'fullScreen' => 'FullScreen',
        'newPage' => 'NewPage',
        'print' => 'Print',
        'tabIndent' => 'TabIndent',
        'toggleDir' => 'ToggleDir',
        'viewSource' => 'ViewSource'
    );

    public function aweEditor($id, $value = null, $params = array(), $attribs = array())
    {
        return $this->editor($id, $value, $params, $attribs);
    }
}
