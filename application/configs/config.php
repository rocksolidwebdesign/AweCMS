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

// USER SETTINGS
$zfConfArr['phpSettings']['time_zone']      = 'America/New_York';

$zfConfArr['awe']['theme']['admin']         = 'default';
$zfConfArr['awe']['theme']['frontend']      = 'default';

$zfConfArr['awe']['doctrine']['cache_type'] = '\Doctrine\Common\Cache\ApcCache';

// SYSTEM SETTINGS {{{
// bootstrap and autoloader {{{
$zfConfArr['autoloaderNamespaces'][] = "Awe";
$zfConfArr['includePaths']['library'] = APPLICATION_PATH . "/library";
$zfConfArr['bootstrap']['path']       = APPLICATION_PATH . "/modules/core/default/Bootstrap.php";
// }}}

// zend resources and plugins {{{
$zfConfArr['resources']['layout'][]     =  '';
$zfConfArr['resources']['frontController']['moduleDirectory'][]      =  APPLICATION_PATH . '/modules/core';
$zfConfArr['resources']['frontController']['moduleDirectory'][]      =  APPLICATION_PATH . '/modules/other';
$zfConfArr['resources']['frontController']['defaultModule']          =  'default';
$zfConfArr['resources']['frontController']['defaultControllerName']  =  'index';
$zfConfArr['resources']['frontController']['defaultAction']          =  'index';
$zfConfArr['resources']['modules'][]    =  '';
$zfConfArr['resources']['view'][]       =  '';
$zfConfArr['resources']['doctrine'][]   =  '';
$zfConfArr['resources']['router'][]     =  '';

$zfConfArr['pluginPaths']['Awe_Resource'] = APPLICATION_PATH . "/resources";
// }}}

// database connection {{{
$db_login_file = APPLICATION_PATH . '/configs/database/'.APPLICATION_ENV.'.ini';
if (!file_exists($db_login_file)) {
    $db_login_file = APPLICATION_PATH . '/configs/database.ini';
    if (!file_exists($db_login_file)) {
        die("Could not find database login file: $db_login_file");
    }
}
$db_ini = parse_ini_file($db_login_file);

// uncomment this for production to avoid using parsing another ini file
//$db_ini['db_name'] = '';
//$db_ini['db_user'] = '';
//$db_ini['db_pass'] = '';
//$db_ini['db_host'] = '';

$zfConfArr['resources']['db']['adapter']                          =  "pdo_mysql";
$zfConfArr['resources']['db']['params']['dbname']                 =  $db_ini['db_name'];
$zfConfArr['resources']['db']['params']['username']               =  $db_ini['db_user'];
$zfConfArr['resources']['db']['params']['password']               =  $db_ini['db_pass'];
$zfConfArr['resources']['db']['params']['host']                   =  $db_ini['db_host'];
$zfConfArr['resources']['db']['params']['isDefaultTableAdapter']  =  true;

// MAMP Socket
if (file_exists('/Applications/MAMP/tmp/mysql/mysql.sock')) {
    $zfConfArr['doctrine']['connection']['unix_socket'] = '/Applications/MAMP/tmp/mysql/mysql.sock';
    $zfConfArr['db']['params']['unix_socket'] = '/Applications/MAMP/tmp/mysql/mysql.sock';
}
else {
    $zfConfArr['doctrine']['connection']['host']  = $db_ini['db_host'];
}

$zfConfArr['doctrine']['connection']['driver']    = 'pdo_mysql';
$zfConfArr['doctrine']['connection']['port']      = '3306';
$zfConfArr['doctrine']['connection']['dbname']    = $db_ini['db_name'];
$zfConfArr['doctrine']['connection']['user']      = $db_ini['db_user'];
$zfConfArr['doctrine']['connection']['password']  = $db_ini['db_pass'];
// }}}

// doctrine specific configuration {{{
$zfConfArr['doctrine']['settings']['entities_path']['Entities\Core\Premium']  =  APPLICATION_PATH . "/modules/core/premium/doctrine";
$zfConfArr['doctrine']['settings']['entities_path']['Entities\Core\Access']   =  APPLICATION_PATH . "/modules/core/access/doctrine";
$zfConfArr['doctrine']['settings']['entities_path']['Entities\Core\Blog']     =  APPLICATION_PATH . "/modules/core/blog/doctrine";
$zfConfArr['doctrine']['settings']['entities_path']['Entities\Core\Cms']      =  APPLICATION_PATH . "/modules/core/cms/doctrine";
$zfConfArr['doctrine']['settings']['entities_path']['Entities\Core']          =  APPLICATION_PATH . "/doctrine";
$zfConfArr['doctrine']['settings']['entities_path']['Entities\Other']         =  APPLICATION_PATH . "/doctrine";
$zfConfArr['doctrine']['settings']['proxies_path']  = APPLICATION_PATH . "/doctrine";
$zfConfArr['doctrine']['settings']['log_path']      = APPLICATION_PATH . "/doctrine/log";

$GLOBALS['gANNOTATION_KEYS']['a_id']          = 'Doctrine\ORM\Mapping\Id';
$GLOBALS['gANNOTATION_KEYS']['a_col']         = 'Doctrine\ORM\Mapping\Column';
$GLOBALS['gANNOTATION_KEYS']['a_12m']         = 'Doctrine\ORM\Mapping\OneToMany';
$GLOBALS['gANNOTATION_KEYS']['a_m2m']         = 'Doctrine\ORM\Mapping\ManyToMany';
$GLOBALS['gANNOTATION_KEYS']['a_m21']         = 'Doctrine\ORM\Mapping\ManyToOne';
$GLOBALS['gANNOTATION_KEYS']['a_join_column'] = 'Doctrine\ORM\Mapping\JoinColumn';
$GLOBALS['gANNOTATION_KEYS']['a_join_table']  = 'Doctrine\ORM\Mapping\JoinTable';
$GLOBALS['gANNOTATION_KEYS']['a_awe']         = 'Awe\Annotations\AutoFormElement';
// }}}

// environment specific settings {{{
switch (APPLICATION_ENV)
{
    case 'development':
    case 'testing':
        // standard system settings
        $zfConfArr['phpSettings']['display_startup_errors'] = 1;
        $zfConfArr['phpSettings']['display_errors'] = 1;
        $zfConfArr['resources']['frontController']['params']['displayExceptions'] = 1;

        // database settings
        $zfConfArr['doctrine']['settings']['cache_type'] = '\Doctrine\Common\Cache\ArrayCache';
        break;

    case 'staging':
    case 'production':
    default:
        // standard system settings
        $zfConfArr['phpSettings']['display_startup_errors'] = 0;
        $zfConfArr['phpSettings']['display_errors'] = 0;
        $zfConfArr['resources']['frontController']['params']['displayExceptions'] = 0;

        // database settings
        $zfConfArr['doctrine']['settings']['cache_type'] = $zfConfArr['awe']['doctrine']['cache_type'];
        break;
}
// }}}
// }}}
