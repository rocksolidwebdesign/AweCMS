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
    public function __construct($sessionArea)
    {
        $this->_sessName = $sessionArea;
    }

    public function authenticate($username, $password)
    {
        $table = Zend_Db_Table::getDefaultAdapter();
        $zAuth = Zend_Auth::getInstance();
        $authAdapter = new Zend_Auth_Adapter_DbTable($table);
        $authAdapter
            ->setTableName('access_user')
            ->setIdentityColumn('username')
            ->setCredentialColumn('password');

        $authAdapter
            ->setIdentity($username)
            ->setCredential($password);
            //->setCredential(sha1($password));

        $result = $zAuth->authenticate($authAdapter);
        if ($result->isValid()) {
            $sess = new Zend_Session_Namespace($this->_sessName);
            $authedUser = $authAdapter->getResultRowObject();

            $em = \Zend_Registry::get('doctrineEm');
            $user = $em->find('\Entities\Core\Access\User', $authedUser->id);

            $sess->username     =  $user->username;
            $sess->userType     =  $user->user_type;
            $sess->memberId     =  $user->id;
            $sess->email        =  $user->email;
            $sess->groups       =  $user->groups;

            return true;
        }

        return false;
    }
}
