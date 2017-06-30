<?php

class Ebizmarts_BakerlooPayment_Model_Method_Cc extends Mage_Payment_Model_Method_Cc {

    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = false;
    protected $_canUseForMultishipping      = false;

    public function validate() {
        return $this;
    }

    public function isAvailable($quote = null) {
        return Mage_Payment_Model_Method_Abstract::isAvailable($quote);
    }

}