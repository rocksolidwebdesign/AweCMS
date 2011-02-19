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

class Premium_AccountController extends Awe_Controller_Frontend
{
    protected $_account = null;

    public function viewAction()
    {
        $entry = $this->getAccount();
        $this->view->account = $account;
    }

    protected function getAccount()
    {
        if ($this->_entry == null) {
            $user_id = $this->getRequest()->getParam('id');

            if (!$user_id) {
                $user_id = 1;
            }

            $em = $this->getInvokeArg('bootstrap')
                       ->getResource('doctrine');

            $this->_account  = $em->find('\Entities\Core\Access\User', $user_id);
        }

        return $this->_account;
    }
}
