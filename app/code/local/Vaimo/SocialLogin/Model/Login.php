<?php

class Vaimo_SocialLogin_Model_Login extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('sociallogin/login');
    }

    public function updateFacebookId($facebookId)
    {
        $this->setFacebookId($facebookId);
        $this->save();
    }

    public function addFacebookId($customerId, $facebookId)
    {
        $this->setCustomerId($customerId);
        $this->setFacebookId($facebookId);
        $this->save();
    }

    public function updateGoogleId($googleId)
    {
        $this->setGoogleId($googleId);
        $this->save();
    }

    public function addGoogleId($customerId, $googleId)
    {
        $this->setCustomerId($customerId);
        $this->setGoogleId($googleId);
        $this->save();
    }

    public function updateTwitterId($twitterId)
    {
        $this->setTwitterId($twitterId);
        $this->save();
    }

    public function addTwitterId($customerId, $twitterId)
    {
        $this->setCustomerId($customerId);
        $this->setGoogleId($twitterId);
        $this->save();
    }

    public function loginUser($customerId = null)
    {
        /** @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');

        if (!$customerId) {
            return false;
        }

        try {
            $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
            $customer->load($customerId);
            $customer->setConfirmation(null);
            $session->setCustomerAsLoggedIn($customer);

            return (bool )Mage::helper('customer')->isLoggedIn();
        } catch (Exception $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            return false;
        }
    }
}
