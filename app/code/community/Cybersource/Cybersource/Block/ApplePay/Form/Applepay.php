<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_ApplePay_Form_Applepay extends Mage_Payment_Block_Form_Cc
{
    /**
     * The configuration value for setting -apple-pay-button-type
     */
    const CONFIG_BUTTON_TYPE = 'payment/cybersourceapplepay/button_type';

    /**
     * The configuration value for setting -apple-pay-button-style
     */
    const CONFIG_BUTTON_STYLE = 'payment/cybersourceapplepay/button_style';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cybersourceapplepay/cc.phtml');
    }

    /**
     * Retrieves the value to modify -apple-pay-button-type
     *
     * @return string
     */
    public function getButtonType()
    {
        return Mage::getStoreConfig(self::CONFIG_BUTTON_TYPE);
    }

    /**
     * Retrieves the value to modify -apple-pay-button-style
     *
     * @return string
     */
    public function getButtonStyle()
    {
        return Mage::getStoreConfig(self::CONFIG_BUTTON_STYLE);
    }

    /**
     * Retrieves the merchant ID
     *
     * @return string
     */
    public function getMerchantId()
    {
        return Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME)->getMerchantIdentifier();
    }

    /**
     * Retrieves the store name which will be displayed in the Apple Pay card
     *
     * @return string
     */
    public function getStoreName()
    {
        return Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME)->getStoreName();
    }

    /**
     * What is the country that the store has primary nexus in
     *
     * @return string
     */
    public function getStoreCountryCode()
    {
        return Mage::getStoreConfig('general/country/default');
    }

    /**
     * Get the current quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME)->getQuote();
    }

    /**
     * The HTML ID for the payment form
     *
     * @return string
     */
    public function getFormId()
    {
        return 'payment_form_' . $this->getMethodCode();
    }

    /**
     * The HTML ID for the payment token input
     *
     * @return string
     */
    public function getPaymentTokenId()
    {
        return $this->getMethodCode() . '_payment_token';
    }

    /**
     * The HTML ID for the template that contains the Apple Pay button
     *
     * @return string
     */
    public function getTemplateId()
    {
        return $this->getMethodCode() . '_template';
    }

    /**
     * The ID for the element that contains the form elements
     *
     * @return string
     */
    public function getPaymentDivId()
    {
        return $this->getMethodCode() . '_cc_type_select_div';
    }

    /**
     * The selector for the main submit buttons
     *
     * @return string
     */
    public function getButtonId()
    {
        return 'order_submit_button';
    }

    /**
     * The CSS class for the Apple Pay button.  Used to select multiple buttons.
     *
     * @return string
     */
    public function getButtonClass()
    {
        return $this->getMethodCode() . '_button';
    }

    /**
     * The ID for the payment selector, usually an input[type=radio]
     *
     * @return string
     */
    public function getSelectorId()
    {
        return 'p_method_' . $this->getMethodCode();
    }

    /**
     * The ID of the element that contains configuration data.  This is required because Prototype may or may not
     * attach variables to the window variable.  As a result an alternate means of providing the configuration must
     * be made available.  Attaching JSON to a data attribute works each time.
     *
     * @return string
     */
    public function getConfigurationDataElementId()
    {
        return $this->getMethodCode() . '_configuration_data';
    }

    /**
     * Retrieves the configuration data to allow the JavaScript object to configure itself
     *
     * @return array
     */
    public function getConfigurationArray()
    {
        return array(
            'templateId' => $this->escapeHtml($this->getTemplateId()),
            'selectorId' => $this->escapeHtml($this->getSelectorId()),
            'buttonSelector' => '.' . $this->getButtonClass(),
            'ingestionUrl' => $this->getUrl('cybersource/apple/ingest'),
            'paymentTokenId' => $this->getPaymentTokenId(),
            'paymentRequestUrl' => $this->getUrl('cybersource/apple/paymentrequest'),
            'applePayButtonSelector' => '.' . $this->getButtonClass()
        );
    }
}
