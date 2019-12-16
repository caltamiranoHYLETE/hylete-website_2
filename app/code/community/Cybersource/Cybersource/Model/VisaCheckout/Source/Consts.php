<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_VisaCheckout_Source_Consts extends Mage_Core_Model_Abstract
{
    /**
     * Test mode Visa SDK URL
     * @var string
     */
    const VISA_SDK_SANDBOX = 'https://sandbox-assets.secure.checkout.visa.com/checkout-widget/resources/js/integration/v1/sdk.js';
    
    /**
     * LIVE mode Visa SDK URL
     * @var string
     */
    const VISA_SDK_PRODUCTION = 'https://assets.secure.checkout.visa.com/checkout-widget/resources/js/integration/v1/sdk.js';

    /**
     * Successful transaction Reasoning code
     * @var int
     */
    const STATUS_ACCEPT = 100;
	
    /**
     *  Transaction to be reviewed by Decision Manager
     * @var int
     */
    const STATUS_DM_REVIEW = 480;

    const LOGFILE = 'cybs_vco.log';

    /**
     * @param mixed $valin System config path of the field to be pulled
     * @param string $store storeId
     * @return mixed
     */
    static function getSystemConfig($valin = false, $store = null)
    {
        if (! $store) {
            $store = Mage::app()->getStore()->getId();
        }

        if ($valin) {
            return Mage::getStoreConfig('payment/cybersourcevisacheckout/' . $valin, $store);
        }

        return Mage::getStoreConfig('payment/cybersourcevisacheckout', $store);
    }
}
