<?php

class Ebizmarts_BakerlooPayment_Model_Magestorecredit extends Ebizmarts_BakerlooPayment_Model_Method_Abstract {

    protected $_code  = "bakerloo_magestorecredit";


    public function isActive($storeId = null) {
        $customerCredit = Mage::helper('core')->isModuleEnabled('Magestore_Customercredit');

        return (bool)(int)$this->getConfigData('active', $storeId) and $customerCredit;
    }

    public function isAvailable($quote = null)
    {
        $available = parent::isAvailable($quote);

        if($available)
            $available = $this->isActive();


        return $available;
    }

    public function isApplicableToQuote($quote, $checksBitMask) {

        $applicable = parent::isApplicableToQuote($quote, $checksBitMask);

        if($applicable === true) {

            $session = Mage::getSingleton('checkout/session');
            $amount = $session->getBaseCustomerCreditAmount();
            $posAmount = (float)Mage::registry('pos_credit_amount');

            $customer = $quote->getCustomer()->load($quote->getCustomerId());

            if($posAmount > $customer->getCreditValue() or $amount != $posAmount)
                $applicable = false;

        }

        return $applicable and $this->isActive();
    }

}