<?php
use GlobalE\SDK\Core;

/**
 * Block that contains the Global-e shipping switcher HTML and clientSDK JS
 * Class Globale_Browsing_Block_Switcher
 */
class Globale_Browsing_Block_Switcher extends Mage_Core_Block_Template {

    /**
     * Get customer international information
     * @return bool|CustomerInfo
     * @access public
     */
    public function getCustomerInformation() {

        $GlobaleSDK = Mage::registry('globale_sdk');
        $CustomerInfo = $GlobaleSDK->Browsing()->GetCustomerInformation();
        if($CustomerInfo->getSuccess()) {
            return $CustomerInfo->getData();
        }else{
            return false;
        }
    }

    /**
     * Get default country from Global-e SDK settings
     * @return string
     * @access public
     */
    public function getBaseCountry() {
        $BaseCountry = Core\Settings::get('Base.Country');
        return $BaseCountry;
    }
}