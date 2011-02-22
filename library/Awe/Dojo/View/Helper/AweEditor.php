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

    protected $_extraPluginsModules = array(
        'prettyprint'             => 'PrettyPrint',           
        'pagebreak'               => 'PageBreak',             
        'showblocknodes'          => 'ShowBlockNodes',        
        'preview'                 => 'Preview',               
        'save'                    => 'Save',                  
        '||'                      => 'ToolbarLineBreak',      
        'toolbarlinebreak'        => 'ToolbarLineBreak',      
        'normalizeindentoutdent'  => 'NormalizeIndentOutdent',
        'breadcrumb'              => 'Breadcrumb',            
        'findreplace'             => 'FindReplace',           
        'pastefromword'           => 'PasteFromWord',         
        'insertanchor'            => 'InsertAnchor',          
        'collapsibletoolbar'      => 'CollapsibleToolbar',    
        'foreColor'               => 'TextColor',             
        'hiliteColor'             => 'TextColor',             
        'blockquote'              => 'Blockquote',            
        'insertTable'             => 'TablePlugins',
        'modifyTable'             => 'TablePlugins',
        'InsertTableRowBefore'    => 'TablePlugins',
        'InsertTableRowAfter'     => 'TablePlugins',
        'insertTableColumnBefore' => 'TablePlugins',
        'insertTableColumnAfter'  => 'TablePlugins',
        'deleteTableRow'          => 'TablePlugins',
        'deleteTableColumn'       => 'TablePlugins',
        'colorTableCell'          => 'TablePlugins',
        'tableContextMenu'        => 'TablePlugins'
    );

    protected function _getRequiredModules(array $plugins)
    {
        $modules = array();
        foreach ($plugins as $commandName) {
            if (isset($this->_pluginsModules[$commandName])) {
                $pluginName = $this->_pluginsModules[$commandName];
                $modules[] = 'dijit._editor.plugins.' . $pluginName;
            } else if (isset($this->_extraPluginsModules[$commandName])) {
                $pluginName = $this->_extraPluginsModules[$commandName];
                $modules[] = 'dojox.editor.plugins.' . $pluginName;
            }
        }

        return array_unique($modules);
    }

    public function aweEditor($id, $value = null, $params = array(), $attribs = array())
    {
        return $this->editor($id, $value, $params, $attribs);
    }
}
