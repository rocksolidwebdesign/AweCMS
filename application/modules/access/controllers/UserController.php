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
 * @package    AweCMS_Access
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Access_UserController extends Awe_Controller_Frontend
{
    // isLoggedIn {{{
    public function isLoggedIn()
    {
        return Zend_Auth::getInstance()->hasIdentity();
    }
    // }}}
    // loginAction {{{
    public function loginAction()
    {
        $form = new Access_Form_Login();

        $this->view->login_attempt = '';
        $request = $this->getRequest();
        if ($request->isPost())
        {
            if ($form->isValid($request->getPost()))
            {
                $post  = $form->getValues();
                $guard = new Awe_Access_Auth('premium_user_account');
                if ($guard->authenticate($post['username'], $post['password']))
                {
                    $sess = new Zend_Session_Namespace('cms_login_target');
                    return $this->_helper->redirector($sess->action, $sess->controller, $sess->module);
                }

                $this->view->login_attempt = 'invalid';
            }
        }

        $this->view->form = $form;

        $em = \Zend_Registry::get('doctrine_entity_manager');
        $page  = $em->find("\\Entities\\Cms\\Page", 1);

        $this->renderDynamicPlaceholder(
            'left_menu', 'site/_cms_menu.phtml', 
            array('children' => $page->children));

        $breadcrumbs = array(
            array(
                'url' => '/',
                'title' => 'Home',
            ),
            array(
                'url' => '/access',
                'title' => 'Members Only Area',
            ),
            array(
                'url' => '/access/user',
                'title' => 'My Account',
            ),
            array(
                'url' => '/access/user/login',
                'title' => 'Login',
            ),
        );

        $this->renderDynamicPlaceholder(
            'breadcrumbs', 'site/_breadcrumbs.phtml', 
            array('breadcrumbs' => $breadcrumbs));
    }
    // }}}
    // logoutAction {{{
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
    }
    // }}}
}
