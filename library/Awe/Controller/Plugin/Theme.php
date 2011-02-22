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

class Awe_Controller_Plugin_Theme extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(
        Zend_Controller_Request_Abstract $request)
    {
        $config_options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();

        $layout = Zend_Layout::getMvcInstance();
        $module_name = $request->getModuleName();

        if ($module_name == 'admin')
        {
            $theme_name = $config_options['awe']['theme']['admin'];
            $path = APPLICATION_PATH
                . '/templates/admin/' . $theme_name . '/layouts/';
        }
        else
        {
            $theme_name = $config_options['awe']['theme']['frontend'];
            $path = APPLICATION_PATH
                . '/templates/frontend/' . $theme_name . '/layouts/';
        }

        if (file_exists($path))
        {
            $layout->setLayoutPath($path);
        }

        $layout_template = isset($config_options['awe']['default_layout_template'][$module_name])
            ? $config_options['awe']['default_layout_template'][$module_name]
            : '';

        if ($layout_template) {
            $layout->setLayout($layout_template);
        }

    }
}
