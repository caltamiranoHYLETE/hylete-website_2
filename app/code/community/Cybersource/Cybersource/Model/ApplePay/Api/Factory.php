<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Api_Factory
{
    const TYPE_AUTHORIZE = 'authorize';
    const TYPE_CAPTURE = 'capture';
    const TYPE_REFUND = 'refund';
    const TYPE_VOID = 'void';
    const TYPE_SETTLE = 'settle';

    /**
     * Builds an adapter instance based off of the Magento configuration.
     *
     * @param $type string Which adapter to retrieve
     * @param $client SoapClient A configured instance of the SoapClient.  Leave blank to use the default
     * @return Cybersource_Cybersource_Model_ApplePay_Api_AbstractAdapter
     * @throws Exception When expected data does not exist
     */
    public function factory($type, SoapClient $client = null)
    {
        $className = Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME . '/api_action_' . $type;
        $adapter = Mage::getModel($className);
        if ($adapter instanceof Cybersource_Cybersource_Model_ApplePay_Api_AbstractAdapter) {
            $adapter->configure(
                Mage::helper('cybersource_core')->getMerchantId(),
                Mage::helper('cybersource_core')->getSoapKey(),
                Mage::helper('cybersource_core')->getWsdlUrl(),
                $client
            );
            return $adapter;
        }
        $message = 'Invalid Apple Pay Adapter requested';
        $this->getHelper()->log($message, Zend_Log::CRIT);
        Mage::throwException($this->getHelper()->__($message));
    }

    /**
     * @return Cybersource_Cybersource_Helper_ApplePay_Data
     */
    public function getHelper()
    {
        return Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME);
    }
}
