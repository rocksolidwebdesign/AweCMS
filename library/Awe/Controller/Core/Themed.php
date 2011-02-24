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

        $module_name    = $this->getRequest()->getModuleName();

        $config_options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
        $theme_name     = $config_options['awe']['theme'][$this->controller_type];

        //$namespace = substr($this->controller_type, 0, strpos('_', $this->controller_type));
        //$template_path = "/$this->controller_type/$theme_name/views/scripts/$namespace/$module_name";

        $theme_path    = "$this->controller_type/$theme_name";
        $template_path = "/templates/$theme_path/views/scripts";
        $skin_path     = "/skin/$theme_path";

        \Zend_Registry::set('awe_theme_skin_path', $skin_path);

        //$this->view->addScriptPath(APPLICATION_PATH . '/templates' . $template_path);
        //$this->pview = new Zend_View();
        //$this->pview->setScriptPath(APPLICATION_PATH . '/modules/core/access/views/scripts');
        //$this->pview->addScriptPath(APPLICATION_PATH . '/templates' . $template_path);
        $this->view->addScriptPath(APPLICATION_PATH . $template_path);

        $this->pview = new Zend_View();
        $this->pview->setScriptPath(APPLICATION_PATH . '/modules/core/access/views/scripts');
        $this->pview->addScriptPath(APPLICATION_PATH . $template_path);
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
