<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2014 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

/**
 * Class Effy_Freshdesk_Model_User
 *
 * @method string|null getName
 * @method string|null getEmail
 * @method Effy_Freshdesk_Model_User setName
 * @method Effy_Freshdesk_Model_User setEmail
 */
class Effy_Freshdesk_Model_User extends Effy_Freshdesk_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->_init('effy_freshdesk/user');
    }

    public function syncCustomer(Mage_Customer_Model_Customer $customer)
    {
        if (!$email = $customer->getEmail()) {
            return false;
        }

        $this->load($email);
        if (!$this->getId()) {
            $this->setEmail($email);
        }
        $this->setName($customer->getName());

        $this->save();

        $this->cleanCache();

        return true;
    }

    public function cleanCache()
    {
        Effy_Freshdesk_Model_Freshdesk_Users::cleanCache();
    }
}