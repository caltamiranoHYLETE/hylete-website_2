<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Sopwm_RequestBuilder extends Mage_Core_Model_Abstract
{
    const STATUS_ECHECK_SECCODE      = 'WEB';
    const STATUS_ECHECK_ACCOUNT_TYPE = 'C';

    protected $fields = array();

    /**
     * @return array
     */
    public function getCreateTokenFields()
    {
        try {
            $this->getQuote()->reserveOrderId()->save();

            $this->buildCoreFields(Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::PAY_METHOD_CARD);
            $this->buildAuthFields();
            $this->buildBillingAddressFields();
            $this->buildCcPaymentFields();
            $this->buildUnsignedFields();
            $this->buildSignedFields();
        } catch (Exception $e) {
            Mage::helper('cybersourcesop')->log('Failed to build cc fields: ' . $e->getMessage(), true);
        }

        return $this->fields;
    }

    /**
     * @return array
     */
    public function getCcFields()
    {
        try {
            $this->getQuote()->reserveOrderId()->save();

            $this->buildCoreFields(Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::PAY_METHOD_CARD);
            $this->buildAuthFields();
            $this->buildDmConfigFields();
            $this->buildBillingAddressFields();
            $this->buildShippingAddressFields();
            $this->buildLineItems();
            $this->buildTotals();
            $this->buildCcPaymentFields();
            $this->buildMerchantDefinedFields();
            $this->buildUnsignedFields();
            $this->buildSignedFields();
        } catch (Exception $e) {
            Mage::helper('cybersourcesop')->log('Failed to build cc fields: ' . $e->getMessage(), true);
        }

        return $this->fields;
    }

    /**
     * @return array
     */
    public function getEcheckFields()
    {
        try {
            $this->getQuote()->reserveOrderId()->save();

            $this->buildCoreFields(Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::PAY_METHOD_ECHECK);
            $this->buildAuthFields();
            $this->buildDmConfigFields();
            $this->buildBillingAddressFields();
            $this->buildShippingAddressFields();
            $this->buildLineItems();
            $this->buildTotals();
            $this->buildEcheckPaymentFields();
            $this->buildMerchantDefinedFields();
            $this->buildUnsignedFields();
            $this->buildSignedFields();
        } catch (Exception $e) {
            Mage::helper('cybersourcesop')->log('Failed to build echeck fields: ' . $e->getMessage(), true);
        }

        return $this->fields;
    }

    /**
     * @param string $method
     * @return $this
     * @throws Exception
     */
    protected function buildCoreFields($method)
    {
        $url = Mage::getUrl('cybersource/sopwm/receipt', array('_secure' => true));
        $locale = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('cybersource_locale');

        $this->addField('payment_method', $method);
        $this->addField('locale', $locale ? $locale : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::LOCALE);
        $this->addField('transaction_type', Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CS_ACTION_AUTHORIZE);
        $this->addField('amount', '0.00');
        $this->addField('currency', $this->getQuote()->getBaseCurrencyCode());
        $this->addField('signed_date_time', gmdate("Y-m-d\TH:i:s\Z"));
        $this->addField('reference_number', $this->getQuote()->getReservedOrderId());
        $this->addField('transaction_uuid', $this->getQuote()->getReservedOrderId() . '_' . time());
        $this->addField('customer_ip_address', $this->getQuote()->getRemoteIp(), false);
        $this->addField('override_custom_receipt_page', $url);

        return $this;
    }

    /**
     * @param null|string $transType
     * @return $this
     * @throws Exception
     */
    protected function buildAuthFields()
    {
        $config = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig();

        if (Mage::helper('cybersourcesop')->isMobile()) {
            if (empty($config['mobile_merchant_secret_key'])) {
                throw new Exception('mobile secret key is empty.');
            }
        } else {
            if (empty($config['secret_key'])) {
                throw new Exception('secret key is empty.');
            }
        }

        if (Mage::helper('cybersourcesop')->isMobile()) {
            $this->addField('access_key', $config['mobile_merchant_access_key']);
            $this->addField('profile_id', $config['mobile_profile_id']);
        } else {
            $this->addField('access_key', $config['merchant_access_key']);
            $this->addField('profile_id', $config['profile_id']);
        }

        return $this;
    }

    protected function buildDmConfigFields()
    {
        if (Mage::helper('cybersource_core')->getIsDmEnabled()) {
            if (Mage::helper('cybersource_core')->getIsFingerprintEnabled()) {
                $this->addField('device_fingerprint_id', Mage::getSingleton('customer/session')->getEncryptedSessionId());
            }
            $this->addField('skip_decision_manager', "false");
        } else {
            $this->addField('skip_decision_manager', "true");
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function buildMerchantDefinedFields()
    {
        if (! Mage::helper('cybersource_core')->getIsDmMerchantDataEnabled()) {
            return $this;
        }

        $quoteData  = $this->getQuote()->getData();
        $definedFields = array();

        try {
            $definedFields = Zend_Serializer::unserialize(Mage::helper('cybersource_core')->getDmMerchantDataFields());
        } catch (Exception $e) { }

        foreach ($definedFields as $key => $value) {

            $customMddValue = false;

            if (strpos($value['value'], '^') === 0) {
                $customMddValue = $this->processCustomMddField($this->getQuote(), $value['value']);
            }

            if (!$customMddValue && !isset($quoteData[$value['value']])) {
                continue;
            }

            $mddValue = $customMddValue ? $customMddValue : $quoteData[$value['value']];

            $this->addField('merchant_defined_data' . $value['mdd'], $mddValue);
        }

        return $this;
    }

    protected function buildBillingAddressFields()
    {
        $billingAddress = $this->getQuote()->getBillingAddress();
        $street = $billingAddress->getStreet(-1);
        $billingStreets = is_array($street) ? $street : explode("\n", $street);

        $billToCompany = $billingAddress->getCompany();
        if (strlen($billToCompany) > 40) {
            $billToCompany = substr($billToCompany, 0, 40);
        }
        $id = 1;
        foreach ($billingStreets as $street) {
            $this->addField('bill_to_address_line' . $id, $street);
            $id++;
        }
        $this->addField('bill_to_address_city', $billingAddress->getCity());
        $this->addField('bill_to_address_state', $billingAddress->getRegionCode(), false);
        $this->addField('bill_to_forename', $billingAddress->getFirstname());
        $this->addField('bill_to_surname', $billingAddress->getLastname());
        $this->addField('bill_to_email', $this->getQuote()->getCustomerEmail());
        $this->addField('bill_to_phone', $this->cleanPhoneNum($billingAddress->getTelephone()));
        $this->addField('bill_to_address_country', $billingAddress->getCountry());
        $this->addField('bill_to_address_postal_code', $billingAddress->getPostcode(), false);
        $this->addField('bill_to_company_name', $billToCompany, false);

        return $this;
    }

    protected function buildShippingAddressFields()
    {
        if ($this->getQuote()->isVirtual()) {
            return $this;
        }

        $shippingAddress = $this->getQuote()->getShippingAddress();
        $street = $shippingAddress->getStreet(-1);
        $shippingStreets = is_array($street) ? $street : explode("\n", $street);

        $shipToCompany = $shippingAddress->getCompany();

        if (strlen($shipToCompany) > 40) {
            $shipToCompany = substr($shipToCompany, 0, 40);
        }
        $id = 1;
        foreach ($shippingStreets as $shipStreet) {
            $this->addField('ship_to_address_line' . $id, $shipStreet);
            $id++;
        }
        $shipPostCode = $shippingAddress->getPostcode();
        if (strlen($shipPostCode) > 10) {
            $shipPostCode = substr($shipPostCode, 0, 10);
        }

        $this->addField('ship_to_address_city', $shippingAddress->getCity());
        $this->addField('ship_to_address_state', $shippingAddress->getRegionCode(), false);
        $this->addField('ship_to_forename', $shippingAddress->getFirstname());
        $this->addField('ship_to_surname', $shippingAddress->getLastname());
        $this->addField('ship_to_email', $this->getQuote()->getCustomerEmail());
        $this->addField('ship_to_phone', $this->cleanPhoneNum($shippingAddress->getTelephone()));
        $this->addField('ship_to_address_country', $shippingAddress->getCountry());
        $this->addField('ship_to_address_postal_code', $shipPostCode, false);
        $this->addField('ship_to_company_name', $shipToCompany, false);

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function buildTotals()
    {
        if ($this->useWebsiteCurrency()) {
            $currencyCode = $this->getQuote()->getQuoteCurrencyCode();
            $totals = $this->formatNumber($this->getQuote()->getGrandTotal());
        } else {
            $currencyCode = $this->getQuote()->getBaseCurrencyCode();
            $totals = $this->formatNumber($this->getQuote()->getBaseGrandTotal());
        }

        $this->addField('currency', $currencyCode);
        $this->addField('amount', $totals);

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function buildLineItems()
    {
        $items = $this->getQuote()->getAllVisibleItems();

        $i = 0;
        foreach ($items as $item) {
            $this->addField('item_'.$i.'_code', $item->getProductId());
            $this->addField('item_'.$i.'_name', $item->getName());
            $this->addField('item_'.$i.'_quantity', $item->getQty());
            $this->addField('item_'.$i.'_sku', $item->getSku());

            if ($this->useWebsiteCurrency()) {
                $this->addField('item_'.$i.'_unit_price', $this->formatNumber($item->getPriceInclTax() - $item->getTaxAmount()));
                $this->addField('item_'.$i.'_tax_amount', $this->formatNumber($item->getTaxAmount()));
            } else {
                $this->addField('item_'.$i.'_unit_price', $this->formatNumber($item->getBasePrice()));
                $this->addField('item_'.$i.'_tax_amount', $this->formatNumber($item->getBaseTaxAmount()));
            }
            $i++;
        }

        $this->addField('line_item_count', $i);

        if ($this->getQuote()->isVirtual()) {
            return $this;
        }

        $shippingAddress = $this->getQuote()->getShippingAddress();
        $taxAmount = $this->useWebsiteCurrency() ? $shippingAddress->getTaxAmount() : $shippingAddress->getBaseTaxAmount();
        $this->addField('tax_amount', $this->formatNumber($taxAmount));

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function buildCcPaymentFields()
    {
        $config = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig();

        if (Mage::helper('cybersourcesop')->isMobile()) {
            return $this;
        }

        if (isset($config['forceavs']) && $config['forceavs'] != Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_DECLINE) {
            $this->addField('ignore_avs', "true");
        }

        if (isset($config['forcecvn']) && $config['forcecvn'] != Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_DECLINE) {
            $this->addField('ignore_cvn', "true");
        }

        $this->addField('card_number', false, false);
        $this->addField('card_type', false, false);
        $this->addField('card_expiry_date', false, false);

        if (Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('useccv')) {
            $this->addField('card_cvn',false, false);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function buildEcheckPaymentFields()
    {
        $this->addField('echeck_sec_code', self::STATUS_ECHECK_SECCODE);
        $this->addField('echeck_account_type', self::STATUS_ECHECK_ACCOUNT_TYPE);
        $this->addField('echeck_routing_number', false, false);
        $this->addField('echeck_account_number', false, false);

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function buildSignedFields()
    {
        $formFields = $this->fields;

        unset($formFields['card_cvn']);
        unset($formFields['card_number']);
        unset($formFields['card_expiry_date']);
        unset($formFields['card_type']);
        unset($formFields['echeck_sec_code']);
        unset($formFields['echeck_account_number']);
        unset($formFields['echeck_routing_number']);
        unset($formFields['echeck_account_type']);

        $fieldNames = implode(',', array_keys($formFields));
        $fieldNames = $fieldNames . ',signed_field_names';
        $this->addField('signed_field_names', $fieldNames);

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function buildUnsignedFields()
    {
        if (Mage::helper('cybersourcesop')->isMobile()) {
            return $this;
        }

        $unsignedFields = array();

        $allFields = $this->fields;
        if (isset($allFields['card_cvn']))
            $unsignedFields['card_cvn'] = $allFields['card_cvn'];
        if (isset($allFields['card_number']))
            $unsignedFields['card_number'] = $allFields['card_number'];
        if (isset($allFields['card_expiry_date']))
            $unsignedFields['card_expiry_date'] = $allFields['card_expiry_date'];
        if (isset($allFields['card_type']))
            $unsignedFields['card_type'] = $allFields['card_type'];
        if (isset($allFields['echeck_account_number']))
            $unsignedFields['echeck_account_number'] = $allFields['echeck_account_number'];
        if (isset($allFields['echeck_routing_number']))
            $unsignedFields['echeck_routing_number'] = $allFields['echeck_routing_number'];
        if (isset($allFields['echeck_account_type']))
            $unsignedFields['echeck_account_type'] = $allFields['echeck_account_type'];
        if (isset($allFields['echeck_sec_code']))
            $unsignedFields['echeck_sec_code'] = $allFields['echeck_sec_code'];

        if (count($unsignedFields)) {
            $fieldNames = implode(',', array_keys($unsignedFields));
            $this->addField('unsigned_field_names', $fieldNames);
        }

        return $this;
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @param bool $required
     * @return $this
     * @throws Exception
     */
    private function addField($key, $value, $required = true)
    {
        if ($required && empty($value)) {
            throw new Exception('Required setting missing: ' . $key);
        } else {
            $value = empty($value) ? '' : $value;
            $this->fields[$key] = $value;
        }
        return $this;
    }

    /**
     * @return bool
     */
    private function useWebsiteCurrency()
    {
        $defaultCurrencyType = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('default_currency');
        return $defaultCurrencyType == Cybersource_Cybersource_Model_SOPWebMobile_Source_Currency::DEFAULT_CURRENCY;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    private function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * @param string|int $num
     * @return string
     */
    private function formatNumber($num)
    {
        return number_format($num, 2, '.', '');
    }

    /**
     * @param string $phoneNumberIn
     * @return string|mixed
     */
    private function cleanPhoneNum($phoneNumberIn)
    {
        $filtered = preg_replace("/[^0-9]/","", $phoneNumberIn);

        if (strlen($filtered) < 6) {
            return '000000000';
        } else {
            return $filtered;
        }
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param string $command
     * @return string
     */
    private function processCustomMddField($quote, $command)
    {
        $explodedCommand = explode(':', $command);
        $action = $explodedCommand[0];
        $params = $explodedCommand[1];

        switch ($action) {
            case '^isGuest':
                return $quote->getCustomerId() ? 'N' : 'Y';
            case '^shippingMethod':
                return $quote->getShippingAddress()->getShippingDescription();
            case '^raw':
                return $params;
        }

        return false;
    }
}
