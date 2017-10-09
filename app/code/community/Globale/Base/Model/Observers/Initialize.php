<?php
use GlobalE\SDK\SDK;

/**
 * @desc Observer for Initialize the SDK and update it by magento Global-e settings
 */
class Globale_Base_Model_Observers_Initialize {

    /**
     * Initialize the SDK and update the SDK settings from magento.
     * Save the SDK object in a registry variable for further uses.
	 * Event ==> controller_front_init_before
     */
    public function initializeSDK() {

		/**@var $Initializer Globale_Base_Model_Initializer */
		$Initializer = Mage::getModel('globale_base/initializer');
		$Initializer->initializeSDK();
    }



}
