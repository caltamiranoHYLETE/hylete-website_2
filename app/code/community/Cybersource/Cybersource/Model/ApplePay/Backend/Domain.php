<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Backend_Domain extends Mage_Core_Model_Config_Data
{
    /**
     * Prepopulate the domain with the HTTP host in the system configuration
     *
     * @return string
     */
    public function getValue()
    {
        $value = parent::getValue();
        if (empty($value)) {
            $value = (string)Mage::app()->getRequest()->getHttpHost();
        }
        return $value;
    }

}
