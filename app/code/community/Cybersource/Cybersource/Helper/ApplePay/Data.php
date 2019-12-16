<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Helper_ApplePay_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Helper constant to refer to internal classes
     */
    const GROUP_NAME = 'cybersourceapplepay';

    /**
     * Configuration path for the certificate file relative to app/etc
     */
    const CONFIG_CERTIFICATE_FILE = 'payment/cybersourceapplepay/certificate';

    /**
     * The Apple Pay merchant ID
     */
    const CONFIG_BUTTON_MERCHANT_ID = 'payment/cybersourceapplepay/merchant_id';

    /**
     * The domain name of the current site.
     */
    const CONFIG_BUTTON_DOMAIN_NAME = 'payment/cybersourceapplepay/domain_name';

    /**
     * The payment networks that the merchant supports.
     */
    const CONFIG_BUTTON_SUPPORTEDNETWORKS = 'payment/cybersourceapplepay/supported_networks';

    /**
     * The payment capabilities for the merchant.
     */
    const CONFIG_BUTTON_CAPABILITIES = 'payment/cybersourceapplepay/capabilities';

    /**
     * The name of the store that will be passed to the Apple Pay card
     */
    const CONFIG_BUTTON_STORE_NAME = 'payment/cybersourceapplepay/store_name';

    /**
     * Allow transactions from the simulator.  It will not call the gateway.
     */
    const CONFIG_CAN_ALLOW_SIMULATOR = 'dev/cybersourceapplepay/allow_simulator';

    /**
     * The name of the log file.
     */
    const LOG_FILE = 'cybs_applepay.log';

    /**
     * Retrieves the fully qualified path to the certificate file.
     *
     * @return string
     * @throws \Exception In the event that the file cannot be resolved
     */
    public function getCertificateFilename()
    {
        $etcDir = realpath(Mage::getBaseDir('etc'));
        $filename = Mage::getStoreConfig(self::CONFIG_CERTIFICATE_FILE);
        if (!$filename) {
            Mage::throwException($this->__('Missing Apple Pay certificate file name'));
        }
        $unresolvedPath = $etcDir . DS . $filename;
        $path = realpath($unresolvedPath);
        if ($path === false) {
            Mage::throwException($this->__('Unable to find Apple Pay certificate file. Requested: %s',  $unresolvedPath));
        }
        if (strpos($path, $etcDir) !== 0) {
            Mage::throwException($this->__('Path provided for Apple Pay certificate must be in /etc'));
        }
        return $path;
    }

    /**
     * Retrieves the optional and required capabilities of the merchant
     *
     * @return array
     */
    public function getMerchantCapabilities()
    {
        $capabilities = Mage::getStoreConfig(self::CONFIG_BUTTON_CAPABILITIES);
        if ($capabilities) {
            $capabilities = explode(',', $capabilities);
        } else {
            $capabilities = array();
        }
        $capabilities = array_merge($capabilities, array('supports3DS'));
        return $capabilities;
    }

    /**
     * Creates the payment request which is initially sent to Apple when the session is created.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    public function buildPaymentRequest(Mage_Sales_Model_Quote $quote)
    {
        $request = array(
            'countryCode' => $this->getStoreCountryCode(),
            'currencyCode' => $quote->getStoreCurrencyCode(),
            'supportedNetworks' => $this->getSupportedNetworks(),
            'merchantCapabilities' => $this->getMerchantCapabilities(),
            'total' => array(
                'label' => $this->getStoreName(),
                'amount' => (float)$quote->getBaseGrandTotal()
            )
        );
        $linesItems = $this->getLineItems($quote);
        if ($linesItems) {
            $request['lineItems'] = $linesItems;
        }
        $request['billingContact'] = $this->buildAddress($quote->getBillingAddress());
        $request['shippingContact'] = $this->buildAddress($quote->getShippingAddress());
        return $request;
    }

    /**
     * Build the address component for the request.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return array
     */
    public function buildAddress(Mage_Sales_Model_Quote_Address $address)
    {
        return array(
            'emailAddress' => $address->getEmail(),
            'familyName' => $address->getLastname(),
            'givenName' => $address->getFirstname(),
            'phoneNumber' => $address->getTelephone(),
            'addressLines' => $address->getStreet(),
            'locality' => $address->getCity(),
            'administrativeArea' => $address->getRegion(),
            'country' => $address->getCountry()
        );
    }

    /**
     * Renders the Apple Pay line items such as the subtotal and shipping
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    public function getLineItems(Mage_Sales_Model_Quote $quote)
    {
        $subTotal = (float)$quote->getBaseSubtotal();
        $shippingAmount = (float)$quote->getShippingAddress()->getBaseShippingAmount();
        $tax = (float)$quote->getShippingAddress()->getBaseTaxAmount();
        $lineItems = array();
        if ($subTotal) {
            $lineItems[] = $this->getLineItemArray($subTotal, 'Subtotal');
        }
        if ($shippingAmount) {
            $lineItems[] = $this->getLineItemArray($shippingAmount, 'Shipping');
        }
        if ($tax) {
            $lineItems[] = $this->getLineItemArray($tax, 'Tax');
        }
        return $lineItems;
    }

    /**
     * Returning a properly formatted line item.
     *
     * @param $amount
     * @param $label
     * @return array
     */
    private function getLineItemArray($amount, $label)
    {
        return array(
            'label' => $this->__($label),
            'amount' => $amount,
            'type' => 'final'
        );
    }

    /**
     * Retrieves the supported networks
     *
     * @return array|mixed
     * @throws Mage_Core_Exception
     */
    public function getSupportedNetworks()
    {
        $networks = Mage::getStoreConfig(self::CONFIG_BUTTON_SUPPORTEDNETWORKS);
        $networks = explode(',', $networks);
        if (!$networks) {
            Mage::throwException($this->__('Apple Pay not configured'));
        }
        return $networks;
    }

    /**
     * Retrieve the store name
     *
     * @return string
     */
    public function getStoreName()
    {
        return Mage::getStoreConfig(self::CONFIG_BUTTON_STORE_NAME);
    }

    /**
     * Determine the Decision Manager Integration is turned on
     *
     * @return bool
     */
    public function getTestMode()
    {
        return Mage::helper('cybersource_core')->getIsTestMode();
    }

    /**
     * Retrieve the store country code
     *
     * @return string
     */
    public function getStoreCountryCode()
    {
        return Mage::getStoreConfig('general/country/default');
    }

    /**
     * Return the Apple Pay merchant identifier
     *
     * @return string
     */
    public function getMerchantIdentifier()
    {
        return Mage::getStoreConfig(self::CONFIG_BUTTON_MERCHANT_ID);
    }

    /**
     * Return the Cybersource merchant identifier
     *
     * @return string
     */
    public function getCybersourceMerchantIdentifier()
    {
        return Mage::helper('cybersource_core')->getMerchantId();
    }

    /**
     * The domain name of the website
     *
     * @return string
     */
    public function getDomainName()
    {
        return Mage::getStoreConfig(self::CONFIG_BUTTON_DOMAIN_NAME);
    }

    /**
     * Get the checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Are all conditions satisfied to allow for transactions from the IOS simulator
     *
     * @return bool
     */
    public function getAllowSimulator()
    {
        $allowSimulator = Mage::getStoreConfigFlag(self::CONFIG_CAN_ALLOW_SIMULATOR);
        $testMode = Mage::helper('cybersource_core')->getIsTestMode();
        return $allowSimulator && $testMode;
    }

    /**
     * Get the current quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        $quote = $this->getCheckoutSession()->getQuote();
        return $quote;
    }

    /**
     * Log something to the Cybersource Apple Pay log file
     *
     * @param $message
     * @param null $level
     */
    public function log($message, $level = null)
    {
        Mage::log($message, $level, self::LOG_FILE);
    }

}
