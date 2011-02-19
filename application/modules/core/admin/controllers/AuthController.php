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
 * @category   Awe
 * @package    AweCMS
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Admin_AuthController extends Awe_Controller_Admin
{
    protected $_public_actions = array(
        'login',
        'logout'
    );

    public function dashboardAction()
    {
    }

    // loginAction {{{
    public function loginAction()
    {
        $sess = new Zend_Session_Namespace('login_target');
        $form = new Access_Form_Login();

        $this->view->login_attempt = '';
        $request = $this->getRequest();
        if ($request->isPost())
        {
            if ($form->isValid($request->getPost()))
            {
                $post  = $form->getValues();
                $guard = new Awe_Access_Auth('awe_admin_interface');
                if ($guard->authenticate($post['username'], $post['password']))
                {
                    $sess = new Zend_Session_Namespace('admin_login_target');
                    return $this->_helper->redirector($sess->action, $sess->controller, $sess->module);
                }

                $this->view->login_attempt = 'invalid';
            }
        }

        $this->view->form = $form;

        $em = \Zend_Registry::get('doctrine_entity_manager');
        $page  = $em->find('\Entities\Core\Cms\Page', 1);
    }
    // }}}
    // logoutAction {{{
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
    }
    // }}}
}
