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
 * @package    AweCMS_Access
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Access_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function _initAutoload()
    {
        $loader = new Zend_Application_Module_Autoloader(
            array(
                'namespace' => 'Access_',
                'basePath' => dirname(__FILE__)
            )
        );

        return $loader;
    }
}
