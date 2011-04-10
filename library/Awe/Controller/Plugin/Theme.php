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
        $zfConfigArray = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();

        $layout = Zend_Layout::getMvcInstance();
        $moduleName = $request->getModuleName();

        if ($moduleName == 'admin')
        {
            $themeName = $zfConfigArray['awe']['theme']['admin'];
            $path = APPLICATION_PATH
                . '/templates/admin/' . $themeName . '/layouts/';
        }
        else
        {
            $themeName = $zfConfigArray['awe']['theme']['frontend'];
            $path = APPLICATION_PATH
                . '/templates/frontend/' . $themeName . '/layouts/';
        }

        if (file_exists($path))
        {
            $layout->setLayoutPath($path);
        }

        $layoutTemplate = isset($zfConfigArray['awe']['defaultLayoutTemplate'][$moduleName])
            ? $zfConfigArray['awe']['defaultLayoutTemplate'][$moduleName]
            : '';

        if ($layoutTemplate) {
            $layout->setLayout($layoutTemplate);
        }

    }
}
