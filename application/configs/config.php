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

global $gANNOTATION_KEYS;

$gANNOTATION_KEYS['a_id']          = 'Doctrine\ORM\Mapping\Id';
$gANNOTATION_KEYS['a_col']         = 'Doctrine\ORM\Mapping\Column';
$gANNOTATION_KEYS['a_12m']         = 'Doctrine\ORM\Mapping\OneToMany';
$gANNOTATION_KEYS['a_m2m']         = 'Doctrine\ORM\Mapping\ManyToMany';
$gANNOTATION_KEYS['a_m21']         = 'Doctrine\ORM\Mapping\ManyToOne';
$gANNOTATION_KEYS['a_join_column'] = 'Doctrine\ORM\Mapping\JoinColumn';
$gANNOTATION_KEYS['a_join_table']  = 'Doctrine\ORM\Mapping\JoinTable';
$gANNOTATION_KEYS['a_awe']         = 'Awe\Annotations\AutoFormElement';

// initial settings
$zfConfArr['phpSettings']['time_zone'] = 'America/New_York';
$zfConfArr['includePaths']['library'] = APPLICATION_PATH . "/library";

$zfConfArr['bootstrap']['path'] = APPLICATION_PATH . "/modules/default/Bootstrap.php";
$zfConfArr['bootstrap']['class'] = "Bootstrap";

$zfConfArr['appnamespace'] = "Awe";

// for the library/Awe files
$zfConfArr['autoloaderNamespaces'][] = "Admin";
$zfConfArr['autoloaderNamespaces'][] = "Awe";

// configure default resources;
$zfConfArr['resources']['modules'][] = "";
$zfConfArr['resources']['frontController']['moduleDirectory'] = APPLICATION_PATH . "/modules";
$zfConfArr['resources']['frontController']['defaultModule'] = "default";
$zfConfArr['resources']['frontController']['defaultControllerName'] = "index";
$zfConfArr['resources']['frontController']['defaultAction'] = "index";

$zfConfArr['resources']['layout']['layout'] = "layout";
$zfConfArr['resources']['layout']['layoutPath'] = APPLICATION_PATH . "/layouts";

// load custom resources
$zfConfArr['resources']['view'][] = "";
$zfConfArr['resources']['doctrine'][] = "";
$zfConfArr['resources']['router'][] = "";

$zfConfArr['pluginPaths']['Awe_Resource'] = APPLICATION_PATH . "/resources";

// database connection
$zfConfArr['resources']['db']['adapter'] = "pdo_mysql";
$zfConfArr['resources']['db']['params']['dbname'] = "awecms";
$zfConfArr['resources']['db']['params']['username'] = "awecms";
$zfConfArr['resources']['db']['params']['password'] = "awecms";
$zfConfArr['resources']['db']['params']['host'] = "localhost";
$zfConfArr['resources']['db']['params']['isDefaultTableAdapter'] = true;

// MAMP Socket
if (file_exists('/Applications/MAMP/tmp/mysql/mysql.sock')) {
    $zfConfArr['doctrine']['connection']['unix_socket'] = '/Applications/MAMP/tmp/mysql/mysql.sock';
}
else {
    $zfConfArr['doctrine']['connection']['host']  = 'localhost';
}

$zfConfArr['doctrine']['connection']['driver']    = 'pdo_mysql';
$zfConfArr['doctrine']['connection']['dbname']    = 'awecms';
$zfConfArr['doctrine']['connection']['port']      = '3306';
$zfConfArr['doctrine']['connection']['user']      = 'awecms';
$zfConfArr['doctrine']['connection']['password']  = 'awecms';

$zfConfArr['doctrine']['settings']['entities_path'] = APPLICATION_PATH . "/doctrine";
$zfConfArr['doctrine']['settings']['proxies_path']  = APPLICATION_PATH . "/doctrine";
$zfConfArr['doctrine']['settings']['log_path']      = APPLICATION_PATH . "/../var";

switch (APPLICATION_ENV)
{
    case 'development':
    case 'testing':
        $zfConfArr['phpSettings']['display_startup_errors'] = 1;
        $zfConfArr['phpSettings']['display_errors'] = 1;
        $zfConfArr['resources']['frontController']['params']['displayExceptions'] = 1;
        break;

    case 'staging':
    case 'production':
    default:
        $zfConfArr['phpSettings']['display_startup_errors'] = 0;
        $zfConfArr['phpSettings']['display_errors'] = 0;
        $zfConfArr['resources']['frontController']['params']['displayExceptions'] = 0;
        break;
}
