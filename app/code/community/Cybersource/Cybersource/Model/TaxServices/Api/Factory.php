<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_TaxServices_Api_Factory
{
    private $adapter;

    /**
     * Builds an adapter instance based off of the Magento configuration.
     *
     * @param $class string The name of the class, or leave blank to use the default
     * @param $client SoapClient A configured instance of the SoapClient.  Leave blank to use the default
     * @return Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter
     */
    public function factory($class = 'Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter', SoapClient $client = null)
    {
        if (!$this->adapter instanceof Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter) {
            $this->adapter = new $class(
                Mage::helper('cybersource_core')->getMerchantId(),
                Mage::helper('cybersource_core')->getSoapKey(),
                Mage::helper('cybersource_core')->getWsdlUrl(),
                $client
            );
        }
        return $this->adapter;
    }
}
