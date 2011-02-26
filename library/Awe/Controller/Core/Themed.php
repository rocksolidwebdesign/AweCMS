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
    protected $controller_type;

    public function init()
    {
        parent::init();

        $config_options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();

        $module_name      = $this->getRequest()->getModuleName();
        $controller_name  = $this->getRequest()->getControllerName();
        $controller_type  = $this->controller_type;
        $theme_name       = $config_options['awe']['theme'][$this->controller_type];
        $namespace        = substr($controller_name, 0, strpos('_', $controller_name));
        $templates_folder = APPLICATION_PATH . '/templates';

        $paths = array();

        // first check admin theme
        // then check admin default
        // then frontend theme
        // then frontend default
        $paths[] = "/frontend/default/views/scripts/$module_name";
        if ($theme_name != 'default') {
            $paths[] = "/frontend/$theme_name/views/scripts/$module_name";
        }

        $skin_path = "/skin/frontend/$theme_name";

        if ($module_name == 'admin') {
            $paths[] = "/admin/default/views/scripts";
            if ($theme_name != 'default') {
                $paths[] = "/admin/$theme_name/views/scripts";
            }

            $skin_path = "/skin/admin/$theme_name";
        }

        $this->pview = new Zend_View();
        $this->pview->addScriptPath($templates_folder . "/frontend/$theme_name/layouts/widgets");

        \Zend_Registry::set('awe_theme_skin_path', $skin_path);
        foreach ($paths as $path) {
            $full_path = $templates_folder . $path;
            $this->view->addScriptPath($full_path);
        }
    }

    public function renderDynamicPlaceholder($name, $script, $vars)
    {
        $content = $this->renderDynamicTemplate($script, $vars);
        $this->view->placeholder($name)->append($content);
    }

    public function renderDynamicTemplate($script, $vars)
    {
        $this->pview->clearVars();

        foreach ($vars as $key => $value) {
            $this->pview->$key = $value;
        }

        $output = $this->pview->render($script);
        return $output;
    }
}
