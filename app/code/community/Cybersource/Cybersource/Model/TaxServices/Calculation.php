<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_TaxServices_Calculation extends Mage_Tax_Model_Calculation
{
    /**
     * Constructor.  Nothing to see here.
     */
    protected function _construct()
    {
        $this->_init('cybersource_taxservices/calculation');
    }

    /**
     * Intercepts the rate request so the resource can have it's quote object populated.
     *
     * @param null $shippingAddress
     * @param null $billingAddress
     * @param null $customerTaxClass
     * @param null $store
     * @return Varien_Object
     */
    public function getRateRequest(
        $shippingAddress = null,
        $billingAddress = null,
        $customerTaxClass = null,
        $store = null
    ) {
        if ($shippingAddress instanceof Mage_Sales_Model_Quote_Address) {
            $this->getResource()->setQuote($shippingAddress->getQuote());
        }
        return parent::getRateRequest($shippingAddress, $billingAddress, $customerTaxClass, $store);
    }
}
