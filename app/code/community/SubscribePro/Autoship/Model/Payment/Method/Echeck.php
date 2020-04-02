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
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */

use \SubscribePro\Service\PaymentProfile\PaymentProfileInterface;

class SubscribePro_Autoship_Model_Payment_Method_Echeck extends Mage_Payment_Model_Method_Cc
{

    const METHOD_CODE = 'subscribe_pro_echeck';
    const METHOD_CODE_KEY_TOKEN = '_token_';

    /**
     * Payment method code
     */
    protected $_code = self::METHOD_CODE;

    /**
     * Form block type
     */
    protected $_formBlockType = 'autoship/payment_form_echeck';
    protected $_formBlockTypeSaved = 'autoship/payment_form_echeck_saved';

    /**
     * Info block type
     */
    protected $_infoBlockType = 'autoship/payment_info_echeck';

    /**
     * Availability options
     */
    protected $_isGateway = true;
    protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = false;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_isInitializeNeeded = false;
    protected $_canFetchTransactionInfo = false;
    protected $_canReviewPayment = false;
    protected $_canCreateBillingAgreement = false;
    protected $_canManageRecurringProfiles = false;

    /**
     * @var PaymentProfileInterface|null
     */
    protected $_savedPaymentProfile = null;

    /**
     * Turn this method instance into a method representing once particular saved card / profile
     * @param PaymentProfileInterface $paymentProfile
     */
    public function setSavedPaymentProfile(PaymentProfileInterface $paymentProfile)
    {
        $this->_savedPaymentProfile = $paymentProfile;
    }

    /**
     * @return PaymentProfileInterface
     */
    public function getSavedPaymentProfile()
    {
        return $this->_savedPaymentProfile;
    }

    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle()
    {
        // If this is a saved card instance, title is saved card last digits
        if ($this->_savedPaymentProfile instanceof PaymentProfileInterface) {
            return Mage::helper('autoship')->__($this->_savedPaymentProfile->getCustomerFacingName());
        }
        else {
            return $this->getConfigData('title');
        }
    }

    /**
     * Retrieve payment method code
     *
     * @return string
     */
    public function getCode()
    {
        if (empty($this->_code)) {
            Mage::throwException(Mage::helper('payment')->__('Cannot retrieve the payment method code.'));
        }
        if ($this->_savedPaymentProfile instanceof PaymentProfileInterface) {
            return self::METHOD_CODE . self::METHOD_CODE_KEY_TOKEN . $this->_savedPaymentProfile->getPaymentToken();
        }
        else {
            return $this->_code;
        }
    }

    /**
     * payment_action should always be auth n capture for this module
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     */
    public function getFormBlockType()
    {
        if ($this->_savedPaymentProfile instanceof PaymentProfileInterface) {
            return $this->_formBlockTypeSaved;
        }
        else {
            return $this->_formBlockType;
        }
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|Mage_Core_Model_Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'payment/' . self::METHOD_CODE . '/' . $field;

        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  $this
     */
    public function assignData($data)
    {
        // Get Mage_Payment_Model_Info instance from quote
        $info = $this->getInfoInstance();

        //Clear out additional information that may have been set previously in the session
        $info->setAdditionalInformation(array());

        // Call parent assignData
        parent::assignData($data);

        // Customer entering new card
        // Save basic fields in additional info
        if (isset($data['is_new_echeck'])) {
            $info->setAdditionalInformation('is_new_echeck', $data['is_new_echeck']);
        }
        if (isset($data['payment_token'])) {
            $info->setAdditionalInformation('payment_token', $data['payment_token']);
        }
        if (isset($data['payment_profile_id'])) {
            $info->setAdditionalInformation('payment_profile_id', $data['payment_profile_id']);
        }
        if (isset($data['payment_profile_name'])) {
            $info->setAdditionalInformation('payment_profile_name', $data['payment_profile_name']);
        }
        if (isset($data['bank_routing_number'])) {
            $info->setAdditionalInformation('bank_routing_number', $data['bank_routing_number']);
        }
        if (isset($data['bank_account_number'])) {
            $info->setAdditionalInformation('bank_account_number', $data['bank_account_number']);
        }
        if (isset($data['bank_account_last_digits'])) {
            $info->setAdditionalInformation('bank_account_last_digits', $data['bank_account_last_digits']);
        }
        if (isset($data['bank_account_type'])) {
            $info->setAdditionalInformation('bank_account_type', $data['bank_account_type']);
        }
        if (isset($data['bank_account_holder_type'])) {
            $info->setAdditionalInformation('bank_account_holder_type', $data['bank_account_holder_type']);
        }
        if (isset($data['subscribe_pro_order_token'])) {
            $info->setAdditionalInformation('subscribe_pro_order_token', $data['subscribe_pro_order_token']);
        }
        else {
            $info->setAdditionalInformation('subscribe_pro_order_token', '');
        }

        return $this;
    }

    /**
     * Prepare info instance for save
     *
     * @return $this
     */
    public function prepareSave()
    {
        // Call parent prepareSave
        parent::prepareSave();

        // Obscure bank account info before save
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('bank_account_number', 'XXXXXXXXX');

        return $this;
    }

    /**
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Echeck::isAvailable called ======', Zend_Log::INFO);

        // If $quote object not populated, call parent method
        if ($quote == null) {
            // Call parent
            return Mage_Payment_Model_Method_Abstract::isAvailable($quote);
        }
        // Check the checkout method selected and look for guest checkout
        if ($quote != null && $quote->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST) {
            // Check configuration settings
            if (Mage::getStoreConfig('payment/' . self::METHOD_CODE . '/allow_guest_checkout', $quote->getStore()) == '1') {
                // Guest checkout option is enabled, call parent method to see if payment method is available
                return Mage_Payment_Model_Method_Abstract::isAvailable($quote);
            }
            else {
                // return No for guest checkout situation when guest checkout option disabled
                return false;
            }
        }

        // If all else fails, call the parent method
        return Mage_Payment_Model_Method_Abstract::isAvailable($quote);
    }

    /**
     * Check whether payment method is applicable to quote
     * Purposed to allow use in controllers some logic that was implemented in blocks only before
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param int|null $checksBitMask
     * @return bool
     */
    public function isApplicableToQuote($quote, $checksBitMask)
    {
        SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Echeck::isApplicableToQuote called ======', Zend_Log::INFO);

        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');

        if ($quoteHelper->hasProductsToCreateNewSubscription($quote) || $quoteHelper->hasSubscriptionReorderProduct($quote)) {
            // Remove the check for zero dollar checkout on this method, as its code is not 'free' but it does support zero dollar checkout
            if ($checksBitMask) {
                $checksBitMask = $checksBitMask & ~self::CHECK_ZERO_TOTAL;
            }
        } else {
            if ($this->getConfigData('active_non_subscription') != '1') {
                return false;
            }
        }
        return parent::isApplicableToQuote($quote, $checksBitMask);
    }

    /**
     * Validate payment method information object
     * @return boolean
     * @throws SubscribePro_Autoship_Helper_PaymentError_Exception
     */
    public function validate()
    {
        // Don't validate if we're in the API
        if (Mage::app()->getFrontController()->getRequest()->getModuleName() == 'api') {
            return true;
        }

        foreach($this->_getValidateFields($this->getInfoInstance()) as $field) {
            if (!$this->_validateField($field, $this->getInfoInstance())) {
                throw new SubscribePro_Autoship_Helper_PaymentError_Exception(Mage::helper("autoship")->__('Payment form field: \'' . $field . '\' is missing from POST!'));
            }
        }

        // We are letting vault do any additional validation
        return true;
    }

    /**
     * Determine which fields should be validated
     * @param Mage_Payment_Model_Info $infoInstance
     * @return array
     */
    protected function _getValidateFields(Mage_Payment_Model_Info $infoInstance)
    {
        if (!$infoInstance->getAdditionalInformation('is_new_echeck')) {
            // Otherwise validate all fields provided
            $_required_keys = array('additional_information/payment_profile_id');
        }
        else {
            // Otherwise validate all fields provided
            $_required_keys = array('additional_information/bank_routing_number', 'additional_information/bank_account_number', 'additional_information/bank_account_type', 'additional_information/bank_account_holder_type');
        }

        return $_required_keys;
    }

    /**
     * Validate a field on the info instance.
     * @param $field
     * @param Mage_Payment_Model_Info $infoInstance
     * @return mixed
     */
    protected function _validateField($field, Mage_Payment_Model_Info $infoInstance)
    {
        $method = 'getData';
        if (preg_match('/^additional_information\//', $field)) {
            $method = 'getAdditionalInformation';
            $field = preg_replace('/^additional_information\//', '', $field);
        }
        return $infoInstance->{$method}($field);
    }

    /**
     * Send capture request to gateway
     *
     * @param Mage_Payment_Model_Info|\Varien_Object $payment
     * @param float $amount
     * @return $this
     */
    public function capture(Varien_Object $payment, $amount)
    {
        // Log
        SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Echeck::capture called ======', Zend_Log::INFO);

        // We are doing auth-and-capture (purchase) transaction here...
        // This is the only transaction supported
        $this->purchase($payment, $amount);

        return $this;
    }

    /**
     * Send purchase request to gateway or just verify for $0 order.
     * Select between a transaction against a saved card (payment profile) or a "one-time" transaction.
     *
     * @param Mage_Payment_Model_Info|\Varien_Object $payment
     * @param float $amount
     */
    protected function purchase(Varien_Object $payment, $amount)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');

        // Get order, etc from $payment
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = $order->getCustomer();

        // Set website / store for config on API helper
        $store = Mage::app()->getWebsite($customer->getData('website_id'))->getDefaultStore();
        $apiHelper->setConfigStore($store);

        // Check if we should do profile transaction or one-time transaction
        if ($payment->getAdditionalInformation('is_new_echeck') == '1') {
            // Create the payment profile
            $paymentProfile = $this->createPaymentProfile($payment, $order->getBillingAddress());
            // Get id
            $paymentProfileId = $paymentProfile->getId();
        }
        else {
            $paymentProfileId = $payment->getAdditionalInformation('payment_profile_id');
        }

        // We are using existing pay profile
        // Create purchase transaction
        if ($amount > 0) {
            // Capture (purchase) if positive amount
            $platformTransaction = $vaultHelper->purchase(
                $paymentProfileId,
                $amount,
                $order->getData('base_currency_code'),
                array(
                    'email' => $order->getCustomerEmail(),
                    'order_id' => $order->getIncrementId(),
                    'ip' => $order->getRemoteIp(),
                    'subscribe_pro_order_token' => $payment->getAdditionalInformation('subscribe_pro_order_token'),
                )
            );
        }
        else {
            SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Cc::capture called, but only saving due to $0.00! ======', Zend_Log::INFO);
        }

        // Save transaction details in $payment
        $payment
            ->setIsTransactionClosed(0)
            ->setAdditionalInformation('bank_account_number', 'XXXXXXXXX')
        ;
        if ($platformTransaction instanceof \SubscribePro\Service\Transaction\TransactionInterface) {
            $payment
                ->setCcTransId($platformTransaction->getId())
                ->setTransactionId($platformTransaction->getId())
                ->setAdditionalInformation('transaction_token', $platformTransaction->getToken())
                ->setAdditionalInformation('gateway_transaction_id', $platformTransaction->getGatewayTransactionId())
                ->setAdditionalInformation('transaction_type', $platformTransaction->getType())
            ;
        }
        // Create transaction record on order / payment
        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
    }

    /**
     * @param Varien_Object $payment
     * @param Mage_Customer_Model_Address_Abstract $billingAddress
     * @return PaymentProfileInterface
     * @throws SubscribePro_Autoship_Helper_PaymentError_Exception
     */
    protected function createPaymentProfile(Varien_Object $payment, Mage_Customer_Model_Address_Abstract $billingAddress)
    {
        /** Mage_Quote_Model_Payment $payment */
        // Log
        SubscribePro_Autoship::log('createPaymentProfile()', Zend_Log::INFO);

        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');
        /** @var SubscribePro_Autoship_Helper_Platform_Customer $customerHelper */
        $customerHelper = Mage::helper('autoship/platform_customer');

        // Get order, etc from $payment
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = $order->getCustomer();

        if ($customer == null || !strlen($customer->getId())) {
            Mage::exception("Bank account can't be saved with a guest checkout.");
        }

        try {
            // Create or update customer on platform
            $platformCustomer = $customerHelper->createOrUpdatePlatformCustomer($customer);

            // Create profile via API
            $paymentProfile = $vaultHelper->createBankAccountPaymentProfile(
                $platformCustomer->getId(),
                $customer,
                $billingAddress,
                $payment->getAdditionalInformation('bank_account_number'),
                $payment->getAdditionalInformation('bank_routing_number'),
                $payment->getAdditionalInformation('bank_account_type'),
                $payment->getAdditionalInformation('bank_account_holder_type')
            );

            // Save payment profile id in additional info
            $payment->setAdditionalInformation('payment_profile_id', $paymentProfile->getId());
            $payment->setAdditionalInformation('payment_profile_name', $paymentProfile->getCustomerFacingName());
            $payment->setAdditionalInformation('payment_token', $paymentProfile->getPaymentToken());
            $payment->setAdditionalInformation('bank_account_last_digits', $paymentProfile->getBankAccountLastDigits());

            // Finally return the profile
            return $paymentProfile;
        }
        catch (SubscribePro_Autoship_Helper_PaymentError_Exception $e) {
            // Rethrow these exceptions as the message will be customer friendly
            throw $e;
        }
        catch (Exception $e) {
            // Throw new exception with generic message
            Mage::throwException(Mage::helper('autoship')->__('There was an error while saving your bank account information!'));
        }
    }

    /**
     * Round up and cast specified amount to float or string
     *
     * @param string|float $amount
     * @param bool $asFloat
     * @return string|float
     */
    protected function formatAmount($amount, $asFloat = false)
    {
        $amount = sprintf('%.2F', $amount); // 'f' depends on locale, 'F' doesn't
        return $asFloat ? (float)$amount : $amount;
    }

}
