<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_TaxServices_Cart_Shipping extends Mage_Checkout_Block_Cart_Shipping
{
    /**
     * Forces the city field to be required in the tax estimation form
     *
     * @return bool
     */
    public function isCityRequired()
    {
        return true;
    }

    /**
     * Forces display of the city in the tax estimation form
     *
     * @return bool
     */
    public function getCityActive()
    {
        return true;
    }
}
