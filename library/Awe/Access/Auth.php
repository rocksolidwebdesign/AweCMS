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
 * @package    AweCMS_Access
 * @copyright  Copyright (c) 2010 Rock Solid Web Design (http://rocksolidwebdesign.com)
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

class Awe_Access_Auth
{
    public function __construct($session_area)
    {
        $this->_sess_name = $session_area;
    }

    public function authenticate($username, $password)
    {
        $table = Zend_Db_Table::getDefaultAdapter();
        $zauth = Zend_Auth::getInstance();
        $auth_adapter = new Zend_Auth_Adapter_DbTable($table);
        $auth_adapter
            ->setTableName('access_user')
            ->setIdentityColumn('username')
            ->setCredentialColumn('password');

        $auth_adapter
            ->setIdentity($username)
            ->setCredential($password);
            //->setCredential(sha1($password));

        $result = $zauth->authenticate($auth_adapter);
        if ($result->isValid()) {
            $sess = new Zend_Session_Namespace($this->_sess_name);
            $authed_user = $auth_adapter->getResultRowObject();

            $em = \Zend_Registry::get('doctrine_entity_manager');
            $user = $em->find('\Entities\Core\Access\User', $authed_user->id);

            $sess->username     =  $user->username;
            $sess->user_type    =  $user->user_type;
            $sess->member_id    =  $user->id;
            $sess->email        =  $user->email;
            $sess->groups       =  $user->groups;

            return true;
        }

        return false;
    }
}
