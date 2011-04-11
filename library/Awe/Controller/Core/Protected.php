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

class Awe_Controller_Core_Protected extends Awe_Controller_Core_Themed
{
    protected $_publicActions = array();
    protected $_authorizedRoles = array(
        'root',
        'admin'
    );

    public function init()
    {
        // If not logging in or out and not authenticated
        if (!($this->isPublic() || $this->isLoggedIn()))
        {
            $sess = new Zend_Session_Namespace('admin_login_target');
            $req = $this->getRequest();
            $sess->action     = $req->getActionName();
            $sess->controller = $req->getControllerName();
            $sess->module     = $req->getModuleName();

            // Force login
            return $this->_helper->redirector('login', 'auth', 'admin');
        }

        parent::init();
    }

    public function isPublic()
    {
        return in_array($this->getRequest()->getActionName(), $this->_publicActions);
    }

    public function isLoggedIn()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            if (!$this->isAuthorized()) {
                Zend_Auth::getInstance()->clearIdentity();
            } else {
                return true;
            }
        }

        return false;
    }

    public function isAuthorized()
    {
        $sess = new Zend_Session_Namespace('awe_admin_interface');
        if (in_array($sess->userType, $this->_authorizedRoles)) {
            return true;
        }
    }
}
