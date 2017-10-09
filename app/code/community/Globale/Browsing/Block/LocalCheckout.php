<?php
use GlobalE\SDK\Models\Country;

/**
 * Class Globale_Browsing_Block_LocalCheckout
 */
class Globale_Browsing_Block_LocalCheckout extends Mage_Core_Block_Template {

    public function getSupportedCountries(){

        /** @var  GlobalE\SDK\SDK $GlobaleSDK */
        $GlobaleSDK = Mage::registry('globale_sdk');

        $CountryResponse = $GlobaleSDK->Browsing()->GetCountries();
        if(!$CountryResponse->getSuccess()){
            return array();
        }
        /**@var $AllCountryModel Country */
        $AllCountryModel = $CountryResponse->getData();
        $SupportedCountries = $AllCountryModel->getOperatedCountries();
        return $SupportedCountries;
    }

    public function getCustomerAddresses(){

        $Addresses = array();

        if(Mage::getSingleton('customer/session')->isLoggedIn()){
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if(count($customer->getAddresses()) > 0){
                foreach ($customer->getAddresses() as $address) {
                    $Addresses[$address->getId()] = $address->getData();
                    foreach ($Addresses[$address->getId()] as $Key => $Value) {
                        $Addresses[$address->getId()][$Key] = $string = trim(preg_replace('/\s+/', ' ', $Value));
                    }
                }
            }
        }

        return $Addresses;
    }

}