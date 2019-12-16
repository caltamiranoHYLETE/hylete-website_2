<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

abstract class Cybersource_Cybersource_Model_ApplePay_Pay_AbstractApiCall
{
    /**
     * Retrieve the adapter factory
     *
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Factory
     * @throws Exception If configuration is incorrect
     */
    protected function getFactory()
    {
        $factory = Mage::getSingleton(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME . '/api_factory');
        if ($factory instanceof Cybersource_Cybersource_Model_ApplePay_Api_Factory) {
            return $factory;
        }
        $message = 'Unable to process request due to incorrect configuration';
        $this->getHelper()->log($message, Zend_Log::CRIT);
        Mage::throwException($this->getHelper()->__($message));
    }

    /**
     * Get the helper
     *
     * @return Cybersource_Cybersource_Helper_ApplePay_Data
     */
    protected function getHelper()
    {
        return Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME);
    }
}
