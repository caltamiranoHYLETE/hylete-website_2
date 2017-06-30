<?php

class Ebizmarts_BakerlooPayment_Model_Free extends Ebizmarts_BakerlooPayment_Model_Method_Abstract {

    protected $_code  = "bakerloo_free";

    /**
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    /*
     * Does not work on EE 1.12, CE < 1.8
     *
     * public function isAvailable($quote = null) {

        //FREE is always availble for POS orders, thats why we still do the
        //isApplicableToQuote check.
        //This solves issues with AW_Giftcard where they disabled payment methods base on order total
        //and if code != free.

        $checkResult = new StdClass;

        if ($quote && version_compare(Mage::getVersion(), '1.8.0.0', '>=')) {
            $checkResult->isAvailable = $this->isApplicableToQuote($quote, Mage_Payment_Model_Method_Abstract::CHECK_RECURRING_PROFILES);
        }
        else {
            $checkResult->isAvailable = true;
        }

        return $checkResult->isAvailable;
    }*/

}