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
 * @package    AweCMS_Admin_Cms
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Admin_Core_Cms_WidgetController extends Awe_Controller_AutoAdmin
{
    protected $entity = '\Entities\Core\Cms\Widget';
    protected $entity_label = 'Widget';
    protected $entity_label_plural = 'Widgets';
    protected $controller_name = 'core_cms_widget';
}
?>
