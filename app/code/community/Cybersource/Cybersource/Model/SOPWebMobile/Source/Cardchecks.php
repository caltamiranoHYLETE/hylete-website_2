<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Source_Cardchecks
{
    /**
     * Returns array from arrays with labels and credit card-check values
     * @return array
     */
	public function toOptionArray()
	{
		return array(
				array(
						'value' => Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_ACCEPT,
						'label' => Mage::helper('core')->__('Accept')
				),
				array(
						'value' => Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_ACCEPT_HOLD,
						'label' => Mage::helper('core')->__('Accept and Hold')
				),
				array(
						'value' => Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_DECLINE,
						'label' => Mage::helper('core')->__('Decline')
				)
		);
	}
}
