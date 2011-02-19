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
 * @package    AweCMS_Resource
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Awe_Resource_Router extends Zend_Application_Resource_ResourceAbstract
{
    private $_router;

    public function init()
    {
        return $this->getRouter();
    }

    public function getRouter()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();

        // Add default home route for page root
        $router->addRoute("/hello_world",
            new Zend_Controller_Router_Route("/hello_world",
                array(
                    'module'        => 'default',
                    'controller'    => 'index',
                    'action'        => 'hello',
                )
            )
        );
    }
}
