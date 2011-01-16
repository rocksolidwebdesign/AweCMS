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

class Awe_Resource_Frontcontroller extends Zend_Application_Resource_Frontcontroller
{
    public function getFrontController()
    {
        if (null === $this->_front) {
            Zend_Controller_Action_HelperBroker::addPrefix('Awe_Controller_Action_Helper');
            $this->_front = Zend_Controller_Front::getInstance();
            $this->_front->registerPlugin(new Awe_Controller_Plugin_Theme());
            return $this->_front;
        }
    }
}
