<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_TaxServices_Resource_Calculation extends Mage_Tax_Model_Resource_Calculation
{
    const SESSION_QUOTE_HASH_LIST = 'quote-hash-list';

    const CONFIG_ENABLED = 'tax/cybersource_taxservices/enabled';
    const CONFIG_NEXUS_REGIONS = 'tax/cybersource_taxservices/nexus_regions';
    const CONFIG_TAX_COUNTRIES = 'tax/cybersource_taxservices/tax_countries';
    const CONFIG_SHIP_FROM_CITY = 'tax/cybersource_taxservices/ship_from_city';
    const CONFIG_SHIP_FROM_POSTALCODE = 'tax/cybersource_taxservices/ship_from_postcode';
    const CONFIG_SHIP_FROM_REGION = 'tax/cybersource_taxservices/ship_from_region';
    const CONFIG_SHIP_FROM_COUNTRY = 'tax/cybersource_taxservices/ship_from_country';
    const CONFIG_ACCEPTANCE_CITY = 'tax/cybersource_taxservices/acceptance_city';
    const CONFIG_ACCEPTANCE_POSTALCODE = 'tax/cybersource_taxservices/acceptance_postcode';
    const CONFIG_ACCEPTANCE_REGION = 'tax/cybersource_taxservices/acceptance_region';
    const CONFIG_ACCEPTANCE_COUNTRY = 'tax/cybersource_taxservices/acceptance_country';
    const CONFIG_ORIGIN_CITY = 'tax/cybersource_taxservices/origin_city';
    const CONFIG_ORIGIN_POSTALCODE = 'tax/cybersource_taxservices/origin_postcode';
    const CONFIG_ORIGIN_REGION = 'tax/cybersource_taxservices/origin_region';
    const CONFIG_ORIGIN_COUNTRY = 'tax/cybersource_taxservices/origin_country';
    const CONFIG_ORIGIN_VAT = 'tax/cybersource_taxservices/merchant_vat';

    const CONFIG_TAX_SHIPPING_ENABLED = 'tax/cybersource_taxservices/tax_shipping_enabled';
    const CONFIG_TAX_SHIPPING_SKU = 'tax/cybersource_taxservices/tax_shipping_sku';
    const CONFIG_TAX_SHIPPING_PRODUCT_CODE = 'tax/cybersource_taxservices/tax_shipping_code';

    private $_quote;

    private $_isChanged = false;

    private $_regions = array();

    /**
     * This method is used as an interception method which checks to see if the tax services module is enabled.  If it
     * is NOT enabled, then it passes the request through to the regular table rate calculation.
     *
     * @param Varien_Object $request
     * @return array
     */
    public function getRateInfo($request)
    {
        if (Mage::getStoreConfigFlag(self::CONFIG_ENABLED)) {
            return $this->_collectTaxInformation();
        }

        return parent::getRateInfo($request);
    }

    /**
     * Set the quote object for the current tax calculation.  This is used to retrieving the addresses from the quote
     * which are, in turn, used for building the API request.
     *
     * @param Mage_Sales_Model_Quote $quote ,
     * @return Cybersource_Cybersource_Model_TaxServices_Resource_Calculation
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    /**
     * Retrieves the current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * A flag is set if the tax situation has changed.  This method returns that flag.
     *
     * @return bool
     */
    public function isChanged()
    {
        return $this->_isChanged;
    }

    /**
     * Calculates a unique identifier for the tax request so we can store recurring requests in the session.
     *
     * @return null|string
     */
    public function getQuoteHash()
    {
        if ($this->getQuote()) {
            $billingAddress = $this->getQuote()->getBillingAddress();
            $shippingAddress = $this->getQuote()->getShippingAddress();
            $hashSource = $billingAddress->getStreet1()
                . '|' . $billingAddress->getCity()
                . '|' . $billingAddress->getRegion()
                . '|' . $billingAddress->getCountry()
                . '|' . $billingAddress->getPostcode();
            $hashSource .= '|' . $shippingAddress->getStreet1()
                . '|' . $shippingAddress->getCity()
                . '|' . $shippingAddress->getRegion()
                . '|' . $shippingAddress->getCountry()
                . '|' . $shippingAddress->getPostcode();
            foreach ($this->getQuote()->getAllVisibleItems() as $item) {
                /** @var $item Mage_Sales_Model_Quote_Item */
                $price = round($item->getBasePrice(), 4);
                $hashSource .= '|' . $item->getProductId()
                    . '|' . $price
                    . '|' . $item->getQty();
            }

            $shippingAmount = $this->getQuote()->getShippingAddress()->getShippingAmount();
            $hashSource .= '|' . $shippingAmount;
            $hash = sha1($hashSource);  // We do not need cryptographic security.  Just a nice, plausibly unique value
            return 'quote-hash-' . $hash;
        }
        return null;
    }

    /**
     * Validates if the tax for the current quote has already been made.
     *
     * @return bool
     */
    public function isTaxCalculated()
    {
        $session = Mage::getSingleton('checkout/session');
        $calculated = isset($session[$this->getQuoteHash()]);
        return $calculated;
    }

    /**
     * @param $regionID
     * @return Mage_Directory_Model_Region
     */
    public function getRegionForRegionID($regionID)
    {
        if (!isset($this->_regions[$regionID])) {
            $region = Mage::getModel('directory/region');
            /** @var $region Mage_Directory_Model_Region */
            $region->load($regionID);
            $this->_regions[$regionID] = $region;
        }
        return $this->_regions[$regionID];
    }

    /**
     * Informs the adapter of the ship-from configuration.
     *
     * @param Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter $adapter
     * @return $this
     */
    protected function setShipFrom(Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter $adapter)
    {
        $city = Mage::getStoreConfig(self::CONFIG_SHIP_FROM_CITY);
        $postalCode = Mage::getStoreConfig(self::CONFIG_SHIP_FROM_POSTALCODE);
        $region = Mage::getStoreConfig(self::CONFIG_SHIP_FROM_REGION);
        $country = Mage::getStoreConfig(self::CONFIG_SHIP_FROM_COUNTRY);
        if ($city && $postalCode && $region && $country) {
            $adapter->setShipFrom($city, $postalCode, $this->getRegionForRegionID($region)->getCode(), $country);
        }
        return $this;
    }

    /**
     * Provides the adapter with the acceptance configuration information
     *
     * @param Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter $adapter
     * @return $this
     */
    protected function setAcceptance(Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter $adapter)
    {
        $city = Mage::getStoreConfig(self::CONFIG_ACCEPTANCE_CITY);
        $postalCode = Mage::getStoreConfig(self::CONFIG_ACCEPTANCE_POSTALCODE);
        $region = Mage::getStoreConfig(self::CONFIG_ACCEPTANCE_REGION);
        $country = Mage::getStoreConfig(self::CONFIG_ACCEPTANCE_COUNTRY);
        if ($city && $postalCode && $region && $country) {
            $adapter->setAcceptance($city, $postalCode, $this->getRegionForRegionID($region)->getCode(), $country);
        }
        return $this;
    }

    /**
     * Provides the adapter with the origin information
     *
     * @param Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter $adapter
     * @return $this
     */
    protected function setOrigin(Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter $adapter)
    {
        $city = Mage::getStoreConfig(self::CONFIG_ORIGIN_CITY);
        $postalCode = Mage::getStoreConfig(self::CONFIG_ORIGIN_POSTALCODE);
        $region = Mage::getStoreConfig(self::CONFIG_ORIGIN_REGION);
        $country = Mage::getStoreConfig(self::CONFIG_ORIGIN_COUNTRY);
        if ($city && $postalCode && $region && $country) {
            $adapter->setOrigin($city, $postalCode, $this->getRegionForRegionID($region)->getCode(), $country);
        }
        return $this;
    }

    /**
     * Provides the adapter with the nexus information from configuration
     *
     * @param Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter $adapter
     * @return $this
     */
    protected function setNexus(Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter $adapter)
    {
        $nexus = Mage::getStoreConfig(self::CONFIG_NEXUS_REGIONS);
        if ($nexus) {
            $collection = Mage::getModel('directory/region')->getCollection();
            /** @var $collection Mage_Directory_Model_Resource_Region_Collection */
            $collection->addFieldToFilter('main_table.region_id', explode(',', $nexus));
            $nexusRegions = array();
            foreach ($collection as $region) {
                /** @var $region Mage_Directory_Model_Region */
                $this->_regions[$region->getId()] = $region; // These may be re-used later on, so this helps populate the cache
                $nexusRegions[] = $region->getCode();
            }
            $adapter->setNexusRegions($nexusRegions);
        }
        return $this;
    }

    /**
     * Validates that the merchant has a sales nexus for the specified address
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return bool
     */
    protected function isAddressInCountry(Mage_Sales_Model_Quote_Address $address)
    {
        $countries = Mage::getStoreConfig(self::CONFIG_TAX_COUNTRIES);
        $countries = explode(',', $countries);
        return in_array($address->getCountry(), $countries);
    }

    /**
     * Configures the adapter to tax shipping based off of the Magento configuration.
     *
     * @param Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter $adapter
     */
    protected function setTaxShippingConfig(Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter $adapter)
    {
        if (Mage::getStoreConfigFlag(self::CONFIG_TAX_SHIPPING_ENABLED)) {
            $adapter->configureTaxShipping(
                true,
                Mage::getStoreConfig(self::CONFIG_TAX_SHIPPING_SKU),
                Mage::getStoreConfig(self::CONFIG_TAX_SHIPPING_PRODUCT_CODE)
            );
        }
    }

    /**
     * Calls the Cybersource adapter and provides the results back to Magento in a manner that Magento can understand.
     *
     * @return array
     */
    protected function _collectTaxInformation()
    {
        $adapter = Mage::getModel('cybersource_taxservices/api_factory')->factory();
        $processes = array();

        /**
         * Process the tax request if we 1) have a calculator adapter defined, 2) have a quote object provided, and
         * 3) products in the quote object.
         */
        if (
            $adapter instanceof Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter
            && $this->_quote instanceof Mage_Sales_Model_Quote
            && $this->getQuote()->getItemsCount() > 0
        ) {
            $address = $this->_quote->getShippingAddress();
            if ($address instanceof Mage_Sales_Model_Quote_Address) {
                $taxEligable = $adapter->isValidAddress($address) && $this->isAddressInCountry($address);

                /**
                 * If the address is not filled in, or the address is in a country that the merchant does not have a
                 * tax nexus then we ignore the request.
                 */
                if (!$taxEligable) {
                    return array('process' => array(),
                        'value' => array(),
                    );
                }
                $session = Mage::getSingleton('checkout/session');

                // Bypass the API request if the tax was calculated on a previous request.
                if ($this->isTaxCalculated()) {
                    $result = $session[$this->getQuoteHash()];
                } else {
                    $this->setNexus($adapter);
                    $this->setShipFrom($adapter);
                    $this->setAcceptance($adapter);
                    $this->setOrigin($adapter);
                    $this->setTaxShippingConfig($adapter);
                    $adapter->setVat(Mage::getStoreConfig(self::CONFIG_ORIGIN_VAT));
                    $result = $adapter->send($this->_quote);
                    $this->_isChanged = true;
                    $this->addResultToSession($session, $result);
                }

                if ($result->isSuccess()) {

                    // Convert the jurisdiction information to a Magento format
                    $jurisdictionTaxRates = $result->getJurisdictionTaxRates();
                    foreach ($jurisdictionTaxRates as $jurisdiction) {
                        $processes[] = array(
                            'rates' => array(
                                array(
                                    'code' => $jurisdiction['code'],
                                    'title' => $jurisdiction['code'],
                                    'percent' => $jurisdiction['percent'],
                                    'position' => $jurisdiction['position'],
                                    'priority' => $jurisdiction['priority'],
                                )
                            ),
                            'percent' => $jurisdiction['percent'],
                            'id' => $jurisdiction['code'],
                        );
                    }
                }
            }
            return array(
                'process' => $processes,
                'value' => $result->getTotalTaxPercentage(),
        );
        }

        return array('process' => array(),
            'value' => array(),
        );
    }

    /**
     * Adds the result to the session for caching using the quote hash as key.
     *
     * @param Mage_Checkout_Model_Session $session
     * @param Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Result $result
     */
    private function addResultToSession(
        Mage_Checkout_Model_Session $session,
        Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Result $result
    ) {

        $quoteHashes = $session[self::SESSION_QUOTE_HASH_LIST];
        if (!$quoteHashes) {
            $quoteHashes = array();
        }
        $quoteHash = $this->getQuoteHash();
        if (!in_array($quoteHash, $quoteHashes)) {
            $quoteHashes[] = $quoteHash;
            $session[self::SESSION_QUOTE_HASH_LIST] = $quoteHashes;
        }

        $session[$quoteHash] = $result;
    }
}
