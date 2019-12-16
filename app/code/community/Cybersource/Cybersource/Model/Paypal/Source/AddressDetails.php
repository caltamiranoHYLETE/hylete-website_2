<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_Paypal_Source_AddressDetails
{
    const NO = 0;
    const PAYPAL_ADDRESS = 1;
    const MAGENTO_ADDRESS = 2;
    
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::NO,
                'label' =>Mage::helper('cybersourcepaypal')->__('No')
            ),
            array(
                'value' => self::PAYPAL_ADDRESS,
                'label' => Mage::helper('cybersourcepaypal')->__('PayPal Address')
            ),
            array(
                'value' => self::MAGENTO_ADDRESS,
                'label' => Mage::helper('cybersourcepaypal')->__('Magento Address')
            ),
        );
    }
}
