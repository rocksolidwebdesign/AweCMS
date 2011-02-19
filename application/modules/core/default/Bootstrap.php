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
 * @category   AweCMS
 * @package    AweCMS_Default
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initModuleAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(
            array(
                'namespace' => '',
                'basePath'  => dirname(__FILE__),
            )
        );
        return $autoloader;
    }
}
