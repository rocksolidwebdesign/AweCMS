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

class Blog_EntryController extends Awe_Controller_Frontend_Widget_Layout
{
    protected $_entry = null;

    public function viewAction()
    {
        $entry = $this->getEntry();
        $this->view->entry = $entry;
        if ($entry->layout) {
            if ($entry->layout->layout_template) {
                $this->_helper->layout->setLayout($entry->layout->layout_template);
            }
            $this->renderLayoutWidgets($entry->layout->layout_containers);
        }
    }

    public function getCurrentEntity() {
        return $this->getEntry();
    }

    protected function getEntry()
    {
        if ($this->_entry == null) {
            $entry_id = $this->getRequest()->getParam('id');

            if (!$entry_id) {
                $entry_id = 1;
            }

            $em = $this->getInvokeArg('bootstrap')
                       ->getResource('doctrine');

            $this->_entry  = $em->find('\Entities\Core\Blog\Entry', $entry_id);
        }

        return $this->_entry;
    }
}
