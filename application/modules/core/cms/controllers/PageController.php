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
 * @package    AweCMS_Core_Cms
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Cms_PageController extends Awe_Controller_Frontend_Widget_Layout
{
    protected $_page = null;

    public function viewAction()
    {
        $page = $this->getPage();
        $this->view->page = $page;
        if ($page->layout) {
            if ($page->layout->layout_template) {
                $this->_helper->layout->setLayout($page->layout->layout_template);
            }
            $this->renderLayoutWidgets($page->layout->layout_containers);
        }
    }

    public function init()
    {
        parent::init();

        // If not logging in or out and not authenticated
        if (!($this->isPublic() || $this->isLoggedIn()))
        {
            $sess = new Zend_Session_Namespace('cms_login_target');
            $req = $this->getRequest();
            $sess->action = $req->getActionName();
            $sess->controller = $req->getControllerName();
            $sess->module = $req->getModuleName();

            // Force login
            return $this->_helper->redirector('login', 'user', 'access');
        }
    }

    protected function isPublic()
    {
        return !$this->getPage()->auth_required;
    }

    public function isLoggedIn()
    {
        return Zend_Auth::getInstance()->hasIdentity();
    }

    public function getCurrentEntity() {
        return $this->getPage();
    }

    protected function getPage()
    {
        if ($this->_page == null) {
            $pageId = $this->getRequest()->getParam('id');

            if (!$pageId)
            {
                $pageId = 1;
            }

            $em = $this->getInvokeArg('bootstrap')
                       ->getResource('doctrine');

            $this->_page  = $em->find('\Entities\Core\Cms\Page', $pageId);
        }

        return $this->_page;
    }
}
