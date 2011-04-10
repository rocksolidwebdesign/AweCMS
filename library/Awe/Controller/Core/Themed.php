<?php
/**
 * AweCMS
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt
 *
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 *
 * @category   Awe
 * @package    AweCMS
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Awe_Controller_Core_Themed extends Zend_Controller_Action
{
    protected $controllerType;

    public function init()
    {
        parent::init();

        $config_options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();

        $moduleName       = $this->getRequest()->getModuleName();
        $controllerName   = $this->getRequest()->getControllerName();
        $controllerType   = $this->controllerType;
        $themeName        = $config_options['awe']['theme'][$this->controllerType];
        $namespace        = substr($controllerName, 0, strpos('_', $controllerName));
        $templatesFolder = APPLICATION_PATH . '/templates';

        $paths = array();

        // first check admin theme
        // then check admin default
        // then frontend theme
        // then frontend default
        $paths[] = "/frontend/default/views/scripts/$moduleName";
        if ($themeName != 'default') {
            $paths[] = "/frontend/$themeName/views/scripts/$moduleName";
        }

        $skinPath = "/skin/frontend/$themeName";

        if ($moduleName == 'admin') {
            $paths[] = "/admin/default/views/scripts";
            if ($themeName != 'default') {
                $paths[] = "/admin/$themeName/views/scripts";
            }

            $skinPath = "/skin/admin/$themeName";
        }

        $this->pView = new Zend_View();
        $this->pView->addScriptPath($templatesFolder . "/frontend/$themeName/layouts/widgets");

        \Zend_Registry::set('awe_theme_skinPath', $skinPath);
        foreach ($paths as $path) {
            $fullPath = $templatesFolder . $path;
            $this->view->addScriptPath($fullPath);
        }
    }

    public function renderDynamicPlaceholder($name, $script, $vars)
    {
        $content = $this->renderDynamicTemplate($script, $vars);
        $this->view->placeholder($name)->append($content);
    }

    public function renderDynamicTemplate($script, $vars)
    {
        $this->pView->clearVars();

        foreach ($vars as $key => $value) {
            $this->pView->$key = $value;
        }

        $output = $this->pView->render($script);
        return $output;
    }
}
