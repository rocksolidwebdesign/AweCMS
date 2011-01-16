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

class Admin_Cms_PagewidgetController extends Awe_Controller_AutoAdmin
{
    protected $entity = '\Entities\Cms\PageWidget';
    protected $entity_label = 'Page Widget';
    protected $entity_label_plural = 'Page Widgets';
    protected $controller_name = 'cms_pagewidget';
}
?>
