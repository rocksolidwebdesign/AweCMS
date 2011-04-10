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
    $cacheType = '\Doctrine\Common\Cache\ArrayCache';
} else {
    $cacheType = $zfConfigArray['doctrine']['settings']['cacheType'];
}
$logPath       = $zfConfigArray['doctrine']['settings']['logPath'];
$proxiesPath   = $zfConfigArray['doctrine']['settings']['proxiesPath'];
$entitiesPath  = $zfConfigArray['doctrine']['settings']['entitiesPath'];
$entitiesPath  = is_array($entitiesPath) ? $entitiesPath : array($entitiesPath);
$dbLogins      = $zfConfigArray['doctrine']['connection'];

// Setup Autloading
// ****************************************************************
$requiredLibs = array(
    'Awe'      => '',
    'Doctrine' => '',
    'Symfony'  => 'Doctrine',
    'Proxies'  => $proxiesPath,
);

require_once 'Doctrine/Common/ClassLoader.php';
foreach ($requiredLibs as $name => $path) {
    if ($path) {
        $classLoader = new \Doctrine\Common\ClassLoader($name, $path);
    } else {
        $classLoader = new \Doctrine\Common\ClassLoader($name);
    }
    $classLoader->register();
}

foreach ($entitiesPath as $name => $path) {
    $classLoader = new \Doctrine\Common\ClassLoader($name, $path);
    $classLoader->register();
}

// Setup Entity Manager
// ****************************************************************
$ormConfig = new \Doctrine\ORM\Configuration();

// custom zend form annotations
$annoReader = new \Doctrine\Common\Annotations\AnnotationReader();
$annoReader->setAutoloadAnnotations(true);
$annoReader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
$annoReader->setAnnotationNamespaceAlias('Awe\Annotations\\', 'awe');
$annoDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($annoReader, $entitiesPath);
$ormConfig->setMetadataDriverImpl($annoDriver);

// logging
$ormConfig->setSQLLogger(new \Doctrine\DBAL\Logging\FileSQLLogger($logPath));

// caching
if (is_array($cacheType)) {
    $ormConfig->setQueryCacheImpl(new $cacheType['query']);
    $ormConfig->setMetadataCacheImpl(new $cacheType['metadata']);
} else {
    $ormConfig->setQueryCacheImpl(new $cacheType);
    $ormConfig->setMetadataCacheImpl(new $cacheType);
}

// proxies
$ormConfig->setAutoGenerateProxyClasses(true);
$ormConfig->setProxyDir($proxiesPath . '/Proxies');
$ormConfig->setProxyNamespace('Proxies');

// Start Doctrine
$em = \Doctrine\ORM\EntityManager::create($dbLogins, $ormConfig);
