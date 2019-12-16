<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_Tax_Checkout_TaxAddressChanged extends Mage_Core_Block_Template
{
    /**
     * Allows the template to see if the tax value has changed.  This is used to force a reload of the review panel
     * in the checkout
     *
     * @return bool
     */
    public function isTaxChanged()
    {
        $calculationResource = Mage::getModel('tax/calculation')->getResource();
        if ($calculationResource instanceof Cybersource_Cybersource_Model_TaxServices_Resource_Calculation) {
            return $calculationResource->isChanged();
        }
        return false;
    }

    /**
     * Allows the template to see if the tax value needs to be updated.  This is used to force a reload of the review
     * panel in the checkout, if necessary.
     *
     * @return bool
     */
    public function isUpdateNeeded()
    {
        $calculationResource = Mage::getModel('tax/calculation')->getResource();
        if ($calculationResource instanceof Cybersource_Cybersource_Model_TaxServices_Resource_Calculation) {
            return !$calculationResource->isTaxCalculated();
        }
        return false;
    }
}
