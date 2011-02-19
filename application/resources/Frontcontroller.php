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
            //Zend_Controller_Action_HelperBroker::addPrefix('Awe_Controller_Action_Helper');
            $this->_front = Zend_Controller_Front::getInstance();
            $this->_front->registerPlugin(new Awe_Controller_Plugin_Theme());
            return $this->_front;
        }
    }

    public function init()
    {
        $front = $this->getFrontController();

        foreach ($this->getOptions() as $key => $value) {
            switch (strtolower($key)) {
                case 'controllerdirectory':
                    if (is_string($value)) {
                        $front->setControllerDirectory($value);
                    } elseif (is_array($value)) {
                        foreach ($value as $module => $directory) {
                            $front->addControllerDirectory($directory, $module);
                        }
                    }
                    break;

                case 'modulecontrollerdirectoryname':
                    $front->setModuleControllerDirectoryName($value);
                    break;

                case 'moduledirectory':
                    /* BEGIN MOD */
                    $value = is_array($value) ? $value : array($value);
                    foreach ($value as $module_directory) {
                        $front->addModuleDirectory($module_directory);
                    }
                    /* END MOD */
                    break;

                case 'defaultcontrollername':
                    $front->setDefaultControllerName($value);
                    break;

                case 'defaultaction':
                    $front->setDefaultAction($value);
                    break;

                case 'defaultmodule':
                    $front->setDefaultModule($value);
                    break;

                case 'baseurl':
                    if (!empty($value)) {
                        $front->setBaseUrl($value);
                    }
                    break;

                case 'params':
                    $front->setParams($value);
                    break;

                case 'plugins':
                    foreach ((array) $value as $pluginClass) {
                    	$stackIndex = null;
                    	if(is_array($pluginClass)) {
                    	    $pluginClass = array_change_key_case($pluginClass, CASE_LOWER);
                            if(isset($pluginClass['class']))
                            {
                                if(isset($pluginClass['stackindex'])) {
                                    $stackIndex = $pluginClass['stackindex'];
                                }

                                $pluginClass = $pluginClass['class'];
                            }
                        }

                        $plugin = new $pluginClass();
                        $front->registerPlugin($plugin, $stackIndex);
                    }
                    break;

                case 'returnresponse':
                    $front->returnResponse((bool) $value);
                    break;

                case 'throwexceptions':
                    $front->throwExceptions((bool) $value);
                    break;

                case 'actionhelperpaths':
                    if (is_array($value)) {
                        foreach ($value as $helperPrefix => $helperPath) {
                            Zend_Controller_Action_HelperBroker::addPath($helperPath, $helperPrefix);
                        }
                    }
                    break;

                default:
                    $front->setParam($key, $value);
                    break;
            }
        }

        if (null !== ($bootstrap = $this->getBootstrap())) {
            $this->getBootstrap()->frontController = $front;
        }

        return $front;
    }

}
