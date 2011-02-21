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
            $app_config    = $this->getBootstrap()->getOptions();
            $cache_type    = $app_config['doctrine']['settings']['cache_type'];
            $entities_path = $app_config['doctrine']['settings']['entities_path'];
            $entities_path = is_array($entities_path) ? $entities_path : array($entities_path);
            $proxies_path  = $app_config['doctrine']['settings']['proxies_path'];
            $log_path      = $app_config['doctrine']['settings']['log_path'];

            // Setup Autloading
            // ****************************************************************
            $required_libs = array(
                'Awe'      => '',
                'Doctrine' => '',
                'Symfony'  => 'Doctrine',
                'Proxies'  => $proxies_path,
            );

            require_once 'Doctrine/Common/ClassLoader.php';
            foreach ($required_libs as $name => $path) {
                if ($path) {
                    $classLoader = new \Doctrine\Common\ClassLoader($name, $path);
                } else {
                    $classLoader = new \Doctrine\Common\ClassLoader($name);
                }
                $classLoader->register();
            }

            foreach ($entities_path as $name => $path) {
                $classLoader = new \Doctrine\Common\ClassLoader($name, $path);
                $classLoader->register();
            }

            // Setup Entity Manager
            // ****************************************************************
            $orm_config = new \Doctrine\ORM\Configuration();

            // custom zend form annotations
            $reader = new \Doctrine\Common\Annotations\AnnotationReader();
            $reader->setAutoloadAnnotations(true);
            $reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
            $reader->setAnnotationNamespaceAlias('Awe\Annotations\\', 'awe');
            $ann_driv = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($reader, $entities_path);
            $orm_config->setMetadataDriverImpl($ann_driv);

            // logging
            $orm_config->setSQLLogger(new Doctrine\DBAL\Logging\FileSQLLogger($log_path));


            // caching
            if (is_array($cache_type)) {
                $orm_config->setQueryCacheImpl(new $cache_type['query']);
                $orm_config->setMetadataCacheImpl(new $cache_type['metadata']);
            } else {
                $orm_config->setQueryCacheImpl(new $cache_type);
                $orm_config->setMetadataCacheImpl(new $cache_type);
            }

            // proxies
            $orm_config->setAutoGenerateProxyClasses(true);
            $orm_config->setProxyDir($proxies_path . '/Proxies');
            $orm_config->setProxyNamespace('Proxies');

            // Start Doctrine
            $em = \Doctrine\ORM\EntityManager::create($app_config['doctrine']['connection'], $orm_config);

            // Save Doctrine In ZF Registry
            // ****************************************************************
            \Zend_Registry::set('doctrine_entity_manager', $em);
            \Zend_Registry::set('doctrine_annotation_reader', $reader);

            $this->_doctrine = $em;
        }

        return $this->_doctrine;
    }
}
