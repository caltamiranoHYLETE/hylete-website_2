<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_VisaCheckout_Visacheckout extends Mage_Core_Block_Template
{
    public function getVisaSettings()
    {
        $result = array();

        $result['sdkUrl'] = Mage::helper('cybersource_core')->getIsTestMode()
            ? Cybersource_Cybersource_Model_VisaCheckout_Source_Consts::VISA_SDK_SANDBOX
            : Cybersource_Cybersource_Model_VisaCheckout_Source_Consts::VISA_SDK_PRODUCTION;

        $result['dataServiceUrl'] = Mage::getUrl('cybersource/vc/dataservice');
        $result['apiKey'] = Mage::getStoreConfig('payment/cybersourcevisacheckout/visacheckoutapikey');
        $result['locale'] = Mage::app()->getLocale()->getLocaleCode();
        $result['countryCode'] = $this->getCountryCode();
        $result['displayName'] = (string) Mage::getStoreConfig('general/store_information/name');
        $result['buttonAction'] = Mage::helper('cybersourcevisacheckout')->__('Continue');
        $result['dataLevel'] = 'SUMMARY';
        $result['currencyCode'] = $this->getCurrency();
        $result['subtotal'] = $this->getAmount();

        return $result;
    }

    private function getCurrency()
    {
        if ($this->useWebsiteCurrency()) {
            return $this->getQuote()->getQuoteCurrencyCode();
        } else {
            return $this->getQuote()->getBaseCurrencyCode();
        }
    }

    private function getAmount()
    {
        if ($this->useWebsiteCurrency()) {
            return $this->getQuote()->getGrandTotal();
        } else {
            return $this->getQuote()->getBaseGrandTotal();
        }
    }

    private function getCountryCode()
    {
        $countryCode = $this->getQuote()->getBillingAddress()->getCountryId();
        return $countryCode ? $countryCode : 'US';
    }

    /**
     * @return bool
     */
    private function useWebsiteCurrency()
    {
        $defaultCurrencyType = Cybersource_Cybersource_Model_VisaCheckout_Source_Consts::getSystemConfig('default_currency');
        return $defaultCurrencyType == Cybersource_Cybersource_Model_VisaCheckout_Source_Currency::DEFAULT_CURRENCY;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    private function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }
}
