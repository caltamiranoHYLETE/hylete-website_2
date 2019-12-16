<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Source_Capabilities
{
    /**
     * Retrieves a list of possible Apple Pay button styles
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('cybersourceapplepay')->__('Credit'),
                'value' => 'supportsCredit'
            ), array(
                'label' => Mage::helper('cybersourceapplepay')->__('Debit'),
                'value' => 'supportsDebit'
            ), array(
                'label' => Mage::helper('cybersourceapplepay')->__('China Union Pay'),
                'value' => 'supportsEMV'
            )
        );
    }
}
