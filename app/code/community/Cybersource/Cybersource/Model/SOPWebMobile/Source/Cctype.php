<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Source_Cctype extends Varien_Object
{
    /**
     * Holds array of available card types.
     * @access public
     * @var array
     */
    public $avalailable_types;

    /**
     * Main constructor
     */
    function _construct()
    {
        $this->avalailable_types = $this->toOptionArray();
    }
    /**
     * Returns array which holds arrays of payment codes and names/label
     * @return array
     */
	static function toOptionArray()
    {
        $options =  array();
		$allowedoptions = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCCMap();

        foreach (Mage::getSingleton('payment/config')->getCcTypes() as $code => $name) {
        	if (array_key_exists($code, $allowedoptions)) {
	            $options[] = array(
	               'value' => $code,
	               'label' => $name
	            );
        	}
        }

        return $options;
    }
}
