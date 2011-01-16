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

class Cms_PageController extends Awe_Controller_Frontend
{
    protected $_page = null;

    // $_public_actions {{{
    protected $_public_actions = array(
        'login',
        'logout',
    );
    // }}}
    // init {{{
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
    // }}}
    // isPublic {{{
    protected function isPublic()
    {
        return !$this->getPage()->auth_required;
    }
    // }}}
    // isLoggedIn {{{
    public function isLoggedIn()
    {
        return Zend_Auth::getInstance()->hasIdentity();
    }
    // }}}
    protected function getPage() // {{{
    {
        if ($this->_page == null) {
            $page_id = $this->getRequest()->getParam('id');

            if (!$page_id)
            {
                $page_id = 1;
            }

            $em = $this->getInvokeArg('bootstrap')
                       ->getResource('doctrine');

            $this->_page  = $em->find("\\Entities\\Cms\\Page", $page_id);
        }

        return $this->_page;
    }
    // }}}
    // viewAction {{{
    public function viewAction()
    {
        $page = $this->getPage();
        $this->view->page = $page;

        // Render Widgets
        foreach ($page->widgets as $pw)
        {
            $code_name = $pw->code_name;
            $entity_name = $pw->widget->entity_name;
            $template_file = $pw->widget->template_file;
            $title = $pw->widget->title;
            $order_by = $pw->widget->order_by;
            $max_results = $pw->widget->max_results;
            $max_results = $max_results ? $max_results : 1;

            $em = $this->getInvokeArg('bootstrap')
                       ->getResource('doctrine');


            $dql = "SELECT e FROM $entity_name e";
            if ($order_by) {
                $dql .= " ORDER BY e.$order_by";
            }

            $query = $em->createQuery($dql);
            $query->setMaxResults($max_results);
            $entities = $query->getResult();

            $this->renderDynamicPlaceholder(
                $code_name, $template_file,
                array('entities' => $entities)
            );
        }

        $this->renderDynamicPlaceholder(
            'left_menu', 'site/_cms_menu.phtml', 
            array('children' => $page->children)
        );

        $this->renderDynamicPlaceholder(
            'breadcrumbs', 'site/_breadcrumbs.phtml', 
            array('breadcrumbs' => $page->getBreadcrumbs())
        );
    }
    // }}}
}
