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
        $this->view->account = $this->getAccount();
    }

    protected function getAccount()
    {
        if ($this->_account == null) {
            $userId = $this->getRequest()->getParam('id');

            if (!$userId) {
                $userId = 1;
            }

            $em = $this->getInvokeArg('bootstrap')
                       ->getResource('doctrine');

            $this->_account  = $em->find('\Entities\Core\Access\User', $userId);
        }

        return $this->_account;
    }
}
