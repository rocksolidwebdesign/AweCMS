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

/**
 * @class Awe_Resource_Doctrine
 * @description
 *     use one of the following to retrieve the EntityManager from a controller
 *         $em = $this->getFrontController()->getParam('bootstrap')->getResource('doctrine');
 *         $em = $this->getInvokeArg('bootstrap')->getResource('doctrine');
 *         $em = \Zend_Registry::get('doctrine');
 */
class Awe_Resource_Doctrine extends Zend_Application_Resource_ResourceAbstract
{
    private $_doctrine;

    public function init()
    {
        return $this->getDoctrine();
    }

    public function getDoctrine()
    {
        if (null === $this->_doctrine) {

            // Get Zend Application Config
            $zfConfArr = $this->getBootstrap()->getOptions();

            include_once(APPLICATION_PATH.'/doctrine/common.php');

            // Save Doctrine In ZF Registry
            // ****************************************************************
            \Zend_Registry::set('doctrine_entity_manager', $em);
            \Zend_Registry::set('doctrine_annotation_reader', $anno_reader);

            $this->_doctrine = $em;
        }

        return $this->_doctrine;
    }
}
