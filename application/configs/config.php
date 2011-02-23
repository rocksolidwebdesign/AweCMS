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

/* things that the user probably wants to configure */
$db_host = 'localhost';
$db_name = 'awecms';
$db_user = 'awecms';
$db_pass = 'awecms';
$zfConfArr['phpSettings']['time_zone'] = 'America/New_York';

$zfConfArr['awe']['theme']['admin']    = 'default';
$zfConfArr['awe']['theme']['frontend'] = 'default';

$zfConfArr['awe']['default_layout_template']['admin']  = 'layout_2col_wide';
$zfConfArr['awe']['default_layout_template']['cms']    = 'layout_3col';
$zfConfArr['awe']['default_layout_template']['blog']   = 'layout_2col_left';

$zfConfArr['doctrine']['settings']['cache_type']    = '\Doctrine\Common\Cache\ArrayCache';

/* things that should stay the same */
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
$zfConfArr['includePaths']['library'] = APPLICATION_PATH . "/library";
$zfConfArr['bootstrap']['path'] = APPLICATION_PATH . "/modules/core/default/Bootstrap.php";

// for the library/Awe files
$zfConfArr['autoloaderNamespaces'][] = "Awe";

// configure default resources;
$zfConfArr['resources']['layout'][] = 'layout_1col';

// load resource plugins
$zfConfArr['resources']['frontController']['moduleDirectory'][]      =  APPLICATION_PATH . "/modules/core";
$zfConfArr['resources']['frontController']['moduleDirectory'][]      =  APPLICATION_PATH . "/modules/other";
$zfConfArr['resources']['frontController']['defaultModule']          =  "default";
$zfConfArr['resources']['frontController']['defaultControllerName']  =  "index";
$zfConfArr['resources']['frontController']['defaultAction']          =  "index";
$zfConfArr['resources']['modules'][]    =  "";
$zfConfArr['resources']['view'][]       =  "";
$zfConfArr['resources']['doctrine'][]   =  "";
$zfConfArr['resources']['router'][]     =  "";

$zfConfArr['pluginPaths']['Awe_Resource'] = APPLICATION_PATH . "/resources";

// database connection
$zfConfArr['resources']['db']['adapter']                          =  "pdo_mysql";
$zfConfArr['resources']['db']['params']['dbname']                 =  $db_name;
$zfConfArr['resources']['db']['params']['username']               =  $db_user;
$zfConfArr['resources']['db']['params']['password']               =  $db_pass;
$zfConfArr['resources']['db']['params']['host']                   =  $db_host;
$zfConfArr['resources']['db']['params']['isDefaultTableAdapter']  =  true;

// MAMP Socket
if (file_exists('/Applications/MAMP/tmp/mysql/mysql.sock')) {
    $zfConfArr['doctrine']['connection']['unix_socket'] = '/Applications/MAMP/tmp/mysql/mysql.sock';
    $zfConfArr['db']['params']['unix_socket'] = '/Applications/MAMP/tmp/mysql/mysql.sock';
}
else {
    $zfConfArr['doctrine']['connection']['host']  = $db_host;
}

$zfConfArr['doctrine']['connection']['driver']    = 'pdo_mysql';
$zfConfArr['doctrine']['connection']['port']      = '3306';
$zfConfArr['doctrine']['connection']['dbname']    = $db_name;
$zfConfArr['doctrine']['connection']['user']      = $db_user;
$zfConfArr['doctrine']['connection']['password']  = $db_pass;

$zfConfArr['doctrine']['settings']['entities_path']['Entities\Core\Premium']  =  APPLICATION_PATH . "/modules/core/premium/doctrine";
$zfConfArr['doctrine']['settings']['entities_path']['Entities\Core\Access']   =  APPLICATION_PATH . "/modules/core/access/doctrine";
$zfConfArr['doctrine']['settings']['entities_path']['Entities\Core\Blog']     =  APPLICATION_PATH . "/modules/core/blog/doctrine";
$zfConfArr['doctrine']['settings']['entities_path']['Entities\Core\Cms']      =  APPLICATION_PATH . "/modules/core/cms/doctrine";
$zfConfArr['doctrine']['settings']['entities_path']['Entities\Core']          =  APPLICATION_PATH . "/doctrine";
$zfConfArr['doctrine']['settings']['entities_path']['Entities\Other']         =  APPLICATION_PATH . "/doctrine";
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
