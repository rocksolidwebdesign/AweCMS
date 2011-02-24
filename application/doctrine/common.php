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
 * @package    AweCMS_Core
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

// always use array cache on the CLI
if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
    $cache_type = '\Doctrine\Common\Cache\ArrayCache';
} else {
    $cache_type = $zfConfArr['doctrine']['settings']['cache_type'];
}
$log_path       = $zfConfArr['doctrine']['settings']['log_path'];
$proxies_path   = $zfConfArr['doctrine']['settings']['proxies_path'];
$entities_path  = $zfConfArr['doctrine']['settings']['entities_path'];
$entities_path  = is_array($entities_path) ? $entities_path : array($entities_path);
$db_logins      = $zfConfArr['doctrine']['connection'];

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
$anno_reader = new \Doctrine\Common\Annotations\AnnotationReader();
$anno_reader->setAutoloadAnnotations(true);
$anno_reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
$anno_reader->setAnnotationNamespaceAlias('Awe\Annotations\\', 'awe');
$anno_driver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($anno_reader, $entities_path);
$orm_config->setMetadataDriverImpl($anno_driver);

// logging
$orm_config->setSQLLogger(new \Doctrine\DBAL\Logging\FileSQLLogger($log_path));

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
$em = \Doctrine\ORM\EntityManager::create($db_logins, $orm_config);
