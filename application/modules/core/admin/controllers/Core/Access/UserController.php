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
 * @package    AweCMS_Admin_Access
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Admin_Core_Access_UserController extends Awe_Controller_AutoAdmin
{
    protected $entity = '\Entities\Core\Access\User';
    protected $entity_label = 'User';
    protected $entity_label_plural = 'Users';
    protected $controller_name = 'core_access_user';
}
?>
