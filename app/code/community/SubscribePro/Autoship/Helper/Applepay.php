<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

class SubscribePro_Autoship_Helper_Applepay extends Mage_Core_Helper_Abstract
{

    /** @var Mage_Customer_Model_Customer|null */
    protected $_customer = null;
    /** @var Mage_Checkout_Model_Session|null */
    protected $_checkout = null;
    /** @var Mage_Sales_Model_Quote|null */
    protected $_quote    = null;


    /**
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession()
    {
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');

        return $customerSession;
    }

    /**
     * Get logged in customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (null === $this->_customer) {
            $this->_customer = $this->getCustomerSession()->getCustomer();
        }
        return $this->_customer;
    }

    /**
     * Get checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        if (null === $this->_checkout) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (null === $this->_quote) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * @return array
     */
    public function getApplePayPaymentRequest()
    {
        // Req fields
        if ($this->getQuote()->isVirtual()) {
            $requiredShippingContactFields = ['name', 'email'];
        }
        else {
            $requiredShippingContactFields = ['name', 'email', 'postalAddress'];
        }

        return [
            'countryCode' => $this->getMerchantCountryCode(),
            'currencyCode' => $this->getMerchantCurrencyCode(),
            'shippingMethods' => $this->getApplePayShippingMethods(),
            'lineItems' => $this->getApplePayLineItems(),
            'total' => $this->getApplePayTotal(),
            'supportedNetworks' => $this->getSupportedApplePayCardTypes(),
            'merchantCapabilities' => ['supports3DS'],
            'requiredShippingContactFields' => $requiredShippingContactFields,
            'requiredBillingContactFields' => ['name', 'postalAddress'],
        ];
    }

    /**
     * @return array
     */
    public function getApplePayTotal()
    {
        return [
            'label' => 'MERCHANT',
            'amount' => number_format($this->getQuote()->getGrandTotal(), 2),
        ];
    }

    /**
     * @return array
     */
    public function getApplePayLineItems()
    {
        return [
            [
                'label' => 'SUBTOTAL',
                'amount' => number_format($this->getQuote()->getShippingAddress()->getSubtotalWithDiscount(), 2),
            ],
            [
                'label' => 'SHIPPING',
                'amount' => number_format($this->getQuote()->getShippingAddress()->getShippingAmount(), 2),
            ],
            [
                'label' => 'TAX',
                'amount' => number_format($this->getQuote()->getShippingAddress()->getTaxAmount(), 2),
            ],
        ];
    }

    /**
     * @return string|null
     */
    public function getMerchantCountryCode()
    {
        $countryCode = Mage::getStoreConfig('payment/account/merchant_country');
        if (!$countryCode) {
            /** @var Mage_Core_Helper_Data $coreHelper */
            $coreHelper = Mage::helper('core');
            $countryCode = $coreHelper->getDefaultCountry($this->getQuote()->getStore());
        }

        return $countryCode;
    }

    /**
     * @return string|null
     */
    public function getMerchantCurrencyCode()
    {
        return Mage::getStoreConfig('payment/' . SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE . '/currency');
    }

    /**
     * Returns array of support card types in Apple Pay format
     *
     * @return array
     */
    public function getSupportedApplePayCardTypes()
    {
        $magentoTypeToApplePayType = array(
            'VI' => 'visa',
            'MC' => 'masterCard',
            'DI' => 'discover',
            'AE' => 'amex',
        );

        $configSupportedCardTypes = Mage::getStoreConfig('payment/' . SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE . '/cctypes');
        $configSupportedCardTypes = explode(',', $configSupportedCardTypes);

        $supportedTypes = array();

        foreach ($configSupportedCardTypes as $configType) {
            if (isset($magentoTypeToApplePayType[$configType])) {
                $supportedTypes[] = $magentoTypeToApplePayType[$configType];
            }
        }

        return $supportedTypes;
    }

    /**
     * @param array $applePayShippingContact
     * @return $this
     */
    public function setApplePayShippingContactOnQuote(array $applePayShippingContact)
    {
        $quote = $this->getQuote();

        // Retrieve the countryId from the request
        $countryId = isset($applePayShippingContact['countryCode']) ? $applePayShippingContact['countryCode'] : null;
        if (!strlen($countryId)) {
            $countryName = isset($applePayShippingContact['countryName']) ? $applePayShippingContact['countryName'] : null;
            if (strlen($countryName)) {
                $countryId = $this->lookupCountryIdByName($countryName);
            }
        }
        else {
            $countryId = strtoupper($countryId);
        }

        // Lookup region
        /** @var Mage_Directory_Model_Region $directoryRegion */
        $directoryRegion = Mage::getModel('directory/region');
        $regionModel = $directoryRegion->loadByCode($applePayShippingContact['administrativeArea'], $countryId);
        if (!$regionModel instanceof Mage_Directory_Model_Region) {
            $directoryRegion->loadByName($applePayShippingContact['administrativeArea'], $countryId);
        }

        // Set the values on the quotes shipping address
        $quote->getShippingAddress()
            ->setCountryId($countryId)
            ->setCity(isset($applePayShippingContact['locality']) ? $applePayShippingContact['locality'] : null)
            ->setPostcode(isset($applePayShippingContact['postalCode']) ? $applePayShippingContact['postalCode'] : null)
            ->setCollectShippingRates(true);
        if ($regionModel instanceof Mage_Directory_Model_Region) {
            $quote->getShippingAddress()->setRegionId($regionModel->getId());
            $quote->getShippingAddress()->setRegion($regionModel->getName());
        }
        $quote->getShippingAddress()->save();
        $quote->save();

        // Recalculate quote
        $this->getQuote()
            ->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();

        return $this;
    }

    /**
     * @param array $applePayShippingMethod
     * @return $this
     */
    public function setApplePayShippingMethodOnQuote(array $applePayShippingMethod)
    {
        if (isset($applePayShippingMethod['identifier'])) {
            $this->getQuote()
                ->getShippingAddress()
                ->setShippingMethod($applePayShippingMethod['identifier'])
                ->save()
            ;
            // Recalculate quote
            $this->getQuote()
                ->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
        }

        return $this;
    }

    /**
     * Retrieve the shipping rates for the Apple Pay session
     *
     * @return array
     */
    public function getApplePayShippingMethods()
    {
        $quote = $this->getQuote();
        $shippingAddress = $quote->getShippingAddress();

        // Pull out the shipping rates
        $shippingRates = $shippingAddress
            ->collectShippingRates()
            ->getGroupedAllShippingRates();

        $rates = [];
        $currentRate = false;

        /* @var $shippingRate Mage_Sales_Model_Quote_Address_Rate */
        foreach ($shippingRates as $carrier => $groupRates) {
            foreach ($groupRates as $shippingRate) {
                // Is this the current selected shipping method?
                if ($quote->getShippingAddress()->getShippingMethod() == $shippingRate->getCode()) {
                    $currentRate = $this->convertShippingRate($shippingRate);
                }
                else {
                    $rates[] = $this->convertShippingRate($shippingRate);
                }
            }
        }

        // Add the current shipping rate first
        if ($currentRate) {
            array_unshift($rates, $currentRate);
        }

        return $rates;
    }

    /**
     * Convert a shipping rate into Apple Pay format
     *
     * @param Mage_Sales_Model_Quote_Address_Rate $shippingRate
     * @return array
     */
    protected function convertShippingRate(Mage_Sales_Model_Quote_Address_Rate $shippingRate)
    {
        // Don't show the same information twice
        $detail = $shippingRate->getMethodTitle();
        if ($shippingRate->getCarrierTitle() == $detail || $detail == 'Free') {
            $detail = '';
        }

        return [
            'label' => $shippingRate->getCarrierTitle(),
            'amount' => (float) number_format($shippingRate->getPrice(), 2),
            'detail' => $detail,
            'identifier' => $shippingRate->getCode(),
        ];
    }

    /**
     * Retrieve the country ID from the name
     *
     * @param $countryName
     * @return bool|string
     */
    public function lookupCountryIdByName($countryName)
    {
        /** @var Mage_Directory_Model_Mysql4_Country_Collection $countryCollection */
        $countryCollection = Mage::getModel('directory/country')->getCollection();
        foreach ($countryCollection as $country) {
            if ($countryName == $country->getName()) {
                return $country->getCountryId();
                break;
            }
        }

        return false;
    }

    /**
     * Convert the incoming Apple Pay address into a Magento address
     *
     * @param $address
     * @return array
     */
    protected function convertToMagentoAddress($address)
    {
        if (is_string($address)) {
            $address = Mage::helper('core')->jsonDecode($address);
        }

        // Retrieve the countryId from the request
        $countryId = strtoupper($address['countryCode']);
        if ((!$countryId || empty($countryId)) && ($countryName = $address['country'])) {
            $countryCollection = Mage::getModel('directory/country')->getCollection();
            foreach ($countryCollection as $country) {
                if ($countryName == $country->getName()) {
                    $countryId = strtoupper($country->getCountryId());
                    break;
                }
            }
        }

        $magentoAddress = array(
            'street' => implode("\n", $address['addressLines']),
            'firstname' => $address['givenName'],
            'lastname' => $address['familyName'],
            'city' => $address['locality'],
            'country_id' => $countryId,
            'postcode' => $address['postalCode'],
            'telephone' => (isset($address['phoneNumber']) ? $address['phoneNumber'] : '0000000000')
        );

        // Determine if a region is required for the selected country
        if (Mage::helper('directory')->isRegionRequired($countryId) && isset($address['administrativeArea'])) {
            // Lookup region
            /** @var Mage_Directory_Model_Region $directoryRegion */
            $directoryRegion = Mage::getModel('directory/region');
            $regionModel = $directoryRegion->loadByCode($address['administrativeArea'], $countryId);
            if (!$regionModel instanceof Mage_Directory_Model_Region) {
                $directoryRegion->loadByName($address['administrativeArea'], $countryId);
            }
            if ($regionModel instanceof Mage_Directory_Model_Region) {
                $magentoAddress['region_id'] = $regionModel->getId();
                $magentoAddress['region'] = $regionModel->getName();
            }
        }

        return $magentoAddress;
    }

    /**
     * @param array $applePayPayment
     * @return $this
     */
    public function setApplePayPaymentOnQuote(array $applePayPayment)
    {
        // Check for payment data
        if (!isset($applePayPayment['token']['paymentData']) || !is_array($applePayPayment['token']['paymentData'])) {
            Mage::throwException('Apple Pay payment data not found!');
        }

        // Quote
        $quote = $this->getQuote();

        // Set customer details
        if ($this->getCustomerSession()->isLoggedIn()) {
            $quote->setCustomer($this->getCustomer());
        }
        else {
            // Save email for guests
            if (!isset($applePayPayment['shippingContact']['emailAddress'])) {
                Mage::throwException('Email address missing from Apple Pay payment details!');
            }
            $quote->setCustomerEmail($applePayPayment['shippingContact']['emailAddress']);
            // Save name
            if (!isset($applePayPayment['shippingContact']['givenName']) || !isset($applePayPayment['shippingContact']['familyName'])) {
                Mage::throwException('Customer name missing from Apple Pay payment details!');
            }
            $quote->setCustomerFirstname($applePayPayment['shippingContact']['givenName']);
            $quote->setCustomerLastname($applePayPayment['shippingContact']['familyName']);
        }

        // Save billing address
        $quote->getBillingAddress()->addData($this->convertToMagentoAddress($applePayPayment['billingContact']));

        // Save shipping address
        if (!$quote->isVirtual()) {
            $quote->getShippingAddress()->addData($this->convertToMagentoAddress($applePayPayment['shippingContact']));
        }

        // Save payment details on quote
        if ($this->getCustomerSession()->isLoggedIn()) {
            $this->createPaymentProfileForCustomer($applePayPayment);
        }
        else {
            $this->createPaymentToken($applePayPayment);
        }

        return $this;
    }

    /**
     * @param array $applePayPayment
     * @return $this
     */
    protected function createPaymentToken(array $applePayPayment)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $platformVaultHelper */
        $platformVaultHelper = Mage::helper('autoship/platform_vault');

        $quote = $this->getQuote();

        $paymentMethod = $platformVaultHelper->createApplePayPaymentToken($quote->getBillingAddress(), $applePayPayment['token']['paymentData']);

        // Set apple pay pay method on quote
        $payment = $quote->getPayment();
        $payment->setMethod(SubscribePro_Autoship_Model_Payment_Method_Applepay::METHOD_CODE);
        // Clear out additional information that may have been set previously in the session
        $payment->setAdditionalInformation(array());
        $payment->setAdditionalInformation('save_card', false);
        $payment->setAdditionalInformation('is_new_card', true);
        $payment->setAdditionalInformation('payment_token', $paymentMethod->getToken());
        $payment->setAdditionalInformation('is_third_party', false);
        $payment->setAdditionalInformation('subscribe_pro_order_token', '');
        // CC Number
        $ccNumber = $paymentMethod->getFirstSixDigits() . 'XXXXXX' . $paymentMethod->getLastFourDigits();
        $payment->setAdditionalInformation('obscured_cc_number', $ccNumber);
        $payment->setData('cc_number', $ccNumber);
        $payment->setCcNumberEnc($payment->encrypt($ccNumber));
        $payment->setData('cc_exp_month', $paymentMethod->getMonth());
        $payment->setData('cc_exp_year', $paymentMethod->getYear());
        $payment->setData('cc_type', $platformVaultHelper->mapSubscribeProCardTypeToMagento($paymentMethod->getCardType()));
        $quote->setPayment($payment);

        // Save quote
        $payment->save();
        $quote->save();

        return $this;
    }

    /**
     * @param array $applePayPayment
     * @return $this
     */
    protected function createPaymentProfileForCustomer(array $applePayPayment)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $platformVaultHelper */
        $platformVaultHelper = Mage::helper('autoship/platform_vault');
        /** @var SubscribePro_Autoship_Helper_Platform_Customer $platformCustomerHelper */
        $platformCustomerHelper = Mage::helper('autoship/platform_customer');

        $quote = $this->getQuote();

        // Create SP customer
        $platformCustomer = $platformCustomerHelper->createOrUpdatePlatformCustomer($quote->getCustomer());
        // Create payment profile
        $paymentProfile = $platformVaultHelper->createApplePayPaymentProfile(
            $platformCustomer->getId(),
            $quote->getCustomer(),
            $quote->getBillingAddress(),
            $applePayPayment['token']['paymentData']
        );

        // Set apple pay pay method on quote
        $payment = $quote->getPayment();
        $payment->setMethod(SubscribePro_Autoship_Model_Payment_Method_Applepay::METHOD_CODE);
        // Clear out additional information that may have been set previously in the session
        $payment->setAdditionalInformation(array());
        $payment->setAdditionalInformation('save_card', false);
        $payment->setAdditionalInformation('is_new_card', false);
        $payment->setAdditionalInformation('payment_token', $paymentProfile->getPaymentToken());
        $payment->setAdditionalInformation('payment_profile_id', $paymentProfile->getId());
        $payment->setAdditionalInformation('is_third_party', false);
        $payment->setAdditionalInformation('subscribe_pro_order_token', '');
        // CC Number
        $ccNumber = $paymentProfile->getCreditcardFirstDigits() . 'XXXXXX' . $paymentProfile->getCreditcardLastDigits();
        $payment->setAdditionalInformation('obscured_cc_number', $ccNumber);
        $payment->setData('cc_number', $ccNumber);
        $payment->setCcNumberEnc($payment->encrypt($ccNumber));
        $payment->setData('cc_exp_month', $paymentProfile->getCreditcardMonth());
        $payment->setData('cc_exp_year', $paymentProfile->getCreditcardYear());
        $payment->setData('cc_type', $platformVaultHelper->mapSubscribeProCardTypeToMagento($paymentProfile->getCreditcardType()));
        $quote->setPayment($payment);

        // Recalculate quote
        $payment->save();
        $quote->save();

        return $this;
    }

    /**
     * @return $this
     */
    public function placeOrder()
    {
        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');

        $quote = $this->getQuote();

        // Recalculate quote
        $this->getQuote()
            ->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();

        // Submit
        /* @var $quoteService Mage_Sales_Model_Service_Quote */
        $quoteService = Mage::getModel('sales/service_quote', $this->getQuote());
        $quoteService->submitAll();
        $order = $quoteService->getOrder();

        // Save quote (which was set as inactive by $quoteService, but not saved
        $quote->save();

        // Send the new order email
        $order->sendNewOrderEmail();

        // Trigger 'checkout_submit_all_after', so subscriptions are created, etc
        $quoteHelper->onCheckoutSubmitAllAfter($quote, $order);

        // Add ids to session for success page
        Mage::getSingleton('checkout/session')->setLastSuccessQuoteId($quote->getId());
        Mage::getSingleton('checkout/session')->setLastQuoteId($quote->getId());
        Mage::getSingleton('checkout/session')->clearHelperData();
        Mage::getSingleton('checkout/session')->setLastOrderId($order->getId());
        Mage::getSingleton('checkout/session')->setLastRealOrderId($order->getIncrementId());

        return $this;
    }

}
