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
        $theme_name = $config_options['awe']['theme'][$this->controller_type];

        $module_name = $this->getRequest()->getModuleName();

        $this->view->addScriptPath(
            APPLICATION_PATH . "/themes/$this->controller_type/$theme_name/views/scripts");

        $this->pview = new Zend_View();
        $this->pview->setScriptPath(
            APPLICATION_PATH . '/modules/core/access/views/scripts');
        $this->pview->addScriptPath(
            APPLICATION_PATH . "/themes/$this->controller_type/$theme_name/views/scripts");
    }

    public function renderDynamicPlaceholder($name, $script, $vars)
    {
        $this->pview->clearVars();

        foreach ($vars as $key => $value) {
            $this->pview->$key = $value;
        }

        $this->view->placeholder($name)->append($this->pview->render($script));
    }
}
