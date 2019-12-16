<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_BankTransfer_Source_Consts extends Mage_Core_Model_Abstract
{
	const STATUS_ACCEPT = 100;

	const STATUS_PROCESSOR_PAYMENT_PENDING = 'pending';
	const STATUS_PROCESSOR_PAYMENT_AUTHORIZED = 'authorized';
	const STATUS_PROCESSOR_PAYMENT_SETTLED = 'settled';
	const STATUS_PROCESSOR_PAYMENT_ABANDONED = 'abandoned';
	const STATUS_PROCESSOR_PAYMENT_FAILED = 'failed';

	const LOGFILE = 'cybs_bt.log';

    /**
     * @param string $code
     * @param string|null $param
     * @param string $store storeId
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    static function getSystemConfig($code, $param = null, $store = null)
    {
        if (! $store) {
            $store = Mage::app()->getStore()->getId();
        }

        if ($param) {
            return Mage::getStoreConfig('payment/' . $code . '/' . $param, $store);
        }

        return Mage::getStoreConfig('payment/' . $code, $store);
    }
}
