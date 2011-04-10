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

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', dirname(__FILE__));

if (!defined('APPLICATION_ENV')) {
    if (getenv('APPLICATION_ENV'))  {
        $envAppType = getenv('APPLICATION_ENV');
    } else if (is_readable($envSettingFile = APPLICATION_PATH.'/configs/environment.ini')) {
        $envIni = parse_ini_file($envSettingFile);
        $envAppType = $envIni['APPLICATION_ENV'];
    } else {
        $envAppType = 'production';
    }
    define('APPLICATION_ENV', $envAppType);
}

// add library to include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

// load zend application config
require_once APPLICATION_PATH . '/configs/config.php';
