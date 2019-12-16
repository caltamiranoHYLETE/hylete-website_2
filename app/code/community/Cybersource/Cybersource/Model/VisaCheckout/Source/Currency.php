<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_VisaCheckout_Source_Currency extends Mage_Core_Model_Abstract
{
	const BASE_CURRENCY     = 'base';
    const DEFAULT_CURRENCY  = 'website';

    /**
     * Returns currency codes array
     * @return array
     */
	public function toOptionArray()
	{
		return array(
            array('value' => self::BASE_CURRENCY, 'label' => Mage::helper('cybersourcevisacheckout')->__('Use G.T (Base) - Base Currency')),
            array('value' => self::DEFAULT_CURRENCY, 'label' => Mage::helper('cybersourcevisacheckout')->__('Use G.T (Purchase) - Default Currency'))
        );
	}
}
  
