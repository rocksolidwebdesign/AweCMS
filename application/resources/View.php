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

class Awe_Resource_View extends Zend_Application_Resource_ResourceAbstract
{
    protected $_view;

    public function init()
    {
        // Return view so bootstrap will store it in the registry
        return $this->getView();
    }

    public function getView()
    {
        if (null === $this->_view) {
            $options = $this->getOptions();
            $title   = '';
            if (array_key_exists('title', $options)) {
                $title = $options['title'];
                unset($options['title']);
            }

            $view = new Zend_View($options);
            $view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
            $view->addHelperPath('Awe/Dojo/View/Helper/', 'Awe_Dojo_View_Helper');
            $view->addHelperPath('ZendX/JQuery/View/Helper', 'ZendX_JQuery_View_Helper');

            $viewRenderer =
            Zend_Controller_Action_HelperBroker::getStaticHelper(
                'ViewRenderer'
            );
            $viewRenderer->setView($view);

            $this->_view = $view;
        }
        return $this->_view;
    }
}
