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

class SubscribePro_Autoship_Model_Payment_Method_Cc extends Mage_Payment_Model_Method_Cc
{

    const METHOD_CODE = 'subscribe_pro';
    const METHOD_CODE_KEY_TOKEN = '_token_';

    /**
     * Payment method code
     */
    protected $_code = self::METHOD_CODE;

    /**
     * Form block type
     */
    protected $_formBlockType = 'autoship/payment_form_cc';
    protected $_formBlockTypeSaved = 'autoship/payment_form_cc_saved';

    /**
     * Info block type
     */
    protected $_infoBlockType = 'autoship/payment_info_cc';

    /**
     * Availability options
     */
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;
    protected $_canFetchTransactionInfo = true;

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
            return Mage::helper('autoship')->__('Use my Saved Credit Card (%s)', $this->_savedPaymentProfile->getCreditcardLastDigits());
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
        if (isset($data['save_card'])) {
            $info->setAdditionalInformation('save_card', $data['save_card']);
        }
        if (isset($data['is_new_card'])) {
            $info->setAdditionalInformation('is_new_card', $data['is_new_card']);
        }
        if (isset($data['payment_token'])) {
            $info->setAdditionalInformation('payment_token', $data['payment_token']);
        }
        if (isset($data['payment_profile_id'])) {
            $info->setAdditionalInformation('payment_profile_id', $data['payment_profile_id']);
        }
        if (isset($data['cc_number'])) {
            $info->setAdditionalInformation('obscured_cc_number', $data['cc_number']);
        }
        if (isset($data['third_party_token'])) {
            $info->setAdditionalInformation('is_third_party', true);
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
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        // If $quote object not populated, call parent method
        if ($quote == null) {
            // Call parent
            return parent::isAvailable($quote);
        }
        // Check the checkout method selected and look for guest checkout
        if ($quote != null && $quote->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST) {
            // Check configuration settings
            if (Mage::getStoreConfig('payment/' . self::METHOD_CODE . '/allow_guest_checkout', $quote->getStore()) == '1') {
                // Guest checkout option is enabled, call parent method to see if payment method is available
                return parent::isAvailable($quote);
            }
            else {
                // return No for guest checkout situation when guest checkout option disabled
                return false;
            }
        }

        // If all else fails, call the parent method
        return parent::isAvailable($quote);
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
     * @return bool
     */
    public function hasVerification()
    {
        // Always ignore verification code in admin ordering
        if (Mage::app()->getStore()->isAdmin()
            || ($this->getSavedPaymentProfile() instanceof PaymentProfileInterface
                && $this->getSavedPaymentProfile()->getPaymentMethodType() == PaymentProfileInterface::TYPE_THIRD_PARTY_TOKEN)) {
            return false;
        }
        else {
            return parent::hasVerification();
        }
    }

    /**
     * Validate payment method information object
     * @return bool
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
        if ($infoInstance->getAdditionalInformation('is_third_party')) {
            // If third party we'll only be validating the cc & token
            $_required_keys = array('cc_number', 'additional_information/payment_token');
        } else {
            // Otherwise validate all fields provided
            $_required_keys = array('cc_number', 'cc_exp_month', 'cc_exp_year', 'additional_information/payment_token');
            if ($infoInstance->getAdditionalInformation('is_new_card') != '1') {
                if ($this->hasVerification()) {
                    $_required_keys[] = 'cc_cid';
                }
            }
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
     * Send authorize request to gateway
     *
     * @param Mage_Payment_Model_Info|\Varien_Object $payment
     * @param  float $amount
     * @return $this
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        // Log
        SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Cc::authorize called ======', Zend_Log::INFO);

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
        if ($payment->getAdditionalInformation('save_card') == '1'
            || $payment->getAdditionalInformation('is_new_card') != '1')
        {
            // We are using existing pay profile or storing new one
            // Get payment profile
            $paymentProfile = $this->createOrFetchPaymentProfile(
                $payment,
                $order->getBillingAddress(),
                $payment->getData('cc_exp_month'),
                $payment->getData('cc_exp_year'));
            // Create authorization transaction
            if ($amount > 0.00) {
                // Authorize if positive amount
                $platformTransaction = $vaultHelper->authorize(
                    $paymentProfile,
                    $amount,
                    $order->getData('base_currency_code'),
                    array(
                        'email' => $order->getCustomerEmail(),
                        'order_id' => $order->getIncrementId(),
                        'ip' => $order->getRemoteIp(),
                        'subscribe_pro_order_token' => $payment->getAdditionalInformation('subscribe_pro_order_token'),
                    )
                );
            } else {
                // Otherwise verify
                SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Cc::authorize called, but only verifying due to $0.00! ======', Zend_Log::INFO);
                $platformTransaction = $vaultHelper->verify(
                    $paymentProfile,
                    $order->getData('base_currency_code'),
                    array(
                        'email' => $order->getCustomerEmail(),
                        'order_id' => $order->getIncrementId(),
                        'ip' => $order->getRemoteIp(),
                        'subscribe_pro_order_token' => $payment->getAdditionalInformation('subscribe_pro_order_token'),
                    )
                );
            }
        }
        else {
            // Don't allow $0.00 for a one-time purchase, should use the 'free' method
            if ($amount <= 0) {
                Mage::throwException(Mage::helper('autoship')->__('Invalid amount for authorization.'));
            }

            // Create one-time transaction
            $platformTransaction = $vaultHelper->authorizeOneTime(
                $payment->getAdditionalInformation('payment_token'),
                $amount,
                $order->getData('base_currency_code'),
                $order->getBillingAddress(),
                array(
                    'email' => $order->getCustomerEmail(),
                    'order_id' => $order->getIncrementId(),
                    'ip' => $order->getRemoteIp(),
                    'creditcard_month' => $payment->getData('cc_exp_month'),
                    'creditcard_year' => $payment->getData('cc_exp_year'),
                )
            );
        }

        // Save cc type in $payment
        $ccType = $vaultHelper->mapSubscribeProCardTypeToMagento($platformTransaction->getCreditcardType(), false);
        if (strlen($ccType)) {
            $payment->setData('cc_type', $ccType);
        }

        // Save transaction details in $payment
        $payment
            ->setIsTransactionClosed(0)
            ->setCcTransId($platformTransaction->getId())
            ->setTransactionId($platformTransaction->getId())
            ->setAdditionalInformation('transaction_token', $platformTransaction->getToken())
            ->setAdditionalInformation('gateway_transaction_id', $platformTransaction->getGatewayTransactionId())
            ->setAdditionalInformation('transaction_type', $platformTransaction->getType())
        ;
        // Save AVS and CVV results when available
        if ($payment instanceof Mage_Sales_Model_Order_Payment) {
            if (strlen($platformTransaction->getAvsCode())) {
                $payment->setCcAvsStatus($platformTransaction->getAvsCode());
            }
            if (strlen($platformTransaction->getCvvCode())) {
                $payment->setCcCidStatus($platformTransaction->getCvvCode());
            }
        }
        /*
        // Create transaction
        // Magento seems to be creating this transaction already
        // $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH);
        */

        return $this;
    }

    public function canReauthorize(Varien_Object $payment)
    {
        // Only allow for authorize only payments
        if ($this->paymentWasAuthorizeOnly($payment)) {
            // Only allow when there is still outstanding balance
            if ($payment->getAmountPaid() < $payment->getAmountOrdered()) {
                // Only allow when payment profile was saved
                if (strlen($payment->getAdditionalInformation('payment_profile_id'))) {
                    // All checks passed, return true
                    return true;
                }
            }

        }

        // Otherwise return false
        return false;
    }

    /**
     * Create a new authorization to replace the existing one
     *
     * @param Mage_Payment_Model_Info|\Varien_Object $payment
     * @return $this
     */
    public function reauthorize(Varien_Object $payment, $amount)
    {
        // Log
        SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Cc::reauthorize called ======', Zend_Log::INFO);

        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');

        // Get order, etc from $payment
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->load($order->getData('customer_id'));

        // Set website / store for config on API helper
        $store = Mage::app()->getWebsite($customer->getData('website_id'))->getDefaultStore();
        $apiHelper->setConfigStore($store);

        // Check for original authorize transaction, otherwise we can't re-auth
        if (!$this->paymentWasAuthorizeOnly($payment)) {
            Mage::throwException(Mage::helper('autoship')->__('Reauthorize only allowed on authorize-only transactions!'));
        }

        // Reauthorize is only valid if the original auth was against a transaction with a saved CC / payment profile
        $paymentProfile = $this->fetchExistingPaymentProfile($payment);
        if (!$paymentProfile instanceof PaymentProfileInterface) {
            Mage::throwException(Mage::helper('autoship')->__('Existing payment profile required for re-authorization!'));
        }

        // Create new authorization transaction
        if ($amount > 0.00) {
            // Authorize if positive amount
            $platformTransaction = $vaultHelper->authorize(
                $paymentProfile,
                $amount,
                $order->getData('base_currency_code'),
                array(
                    'email' => $order->getCustomerEmail(),
                    'order_id' => $order->getIncrementId(),
                    'ip' => $order->getRemoteIp(),
                    'subscribe_pro_order_token' => $payment->getAdditionalInformation('subscribe_pro_order_token'),
                )
            );
        } else {
            // Otherwise verify
            SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Cc::reauthorize called, but only verifying due to $0.00! ======', Zend_Log::INFO);
            $platformTransaction = $vaultHelper->verify(
                $paymentProfile,
                $order->getData('base_currency_code'),
                array(
                    'email' => $order->getCustomerEmail(),
                    'order_id' => $order->getIncrementId(),
                    'ip' => $order->getRemoteIp(),
                    'subscribe_pro_order_token' => $payment->getAdditionalInformation('subscribe_pro_order_token'),
                )
            );
        }

        // Save transaction details in $payment
        // Create extra transaction record
        $payment
            ->setIsTransactionClosed(false)
            ->setParentTransactionId($payment->getData('cc_trans_id'))
            ->setTransactionId($platformTransaction->getId())
            ;
        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, null, true);

        // Replace original transaction so this looks like the original auth, so any future voids or captures are done
        $payment
            ->setCcTransId($platformTransaction->getId())
        ;
        // with this as the reference transaction
        // Save extra SP transaction fields
        $payment
            ->setAdditionalInformation('transaction_token', $platformTransaction->getToken())
            ->setAdditionalInformation('gateway_transaction_id', $platformTransaction->getGatewayTransactionId())
            ->setAdditionalInformation('transaction_type', $platformTransaction->getType())
        ;
        // Save AVS and CVV results when available
        if ($payment instanceof Mage_Sales_Model_Order_Payment) {
            if (strlen($platformTransaction->getAvsCode())) {
                $payment->setCcAvsStatus($platformTransaction->getAvsCode());
            }
            if (strlen($platformTransaction->getCvvCode())) {
                $payment->setCcCidStatus($platformTransaction->getCvvCode());
            }
        }

        return $this;
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
        SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Cc::capture called ======', Zend_Log::INFO);

        // Check if we're doing an auth-and-capture (purchase) transaction or if we are just capturing an already auth'd transaction
        // Look for a value in cc_trans_id and also that the saved transaction_type was 'Authorization'
        if ($this->paymentWasAuthorizeOnly($payment)) {
            // We are doing PriorAuthCapture here...
            $this->priorAuthCapture($payment, $amount);
        }
        else {
            // We are doing auth-and-capture (purchase) transaction here...
            $this->purchase($payment, $amount);
        }

        return $this;
    }

    public function paymentWasAuthorizeOnly(Varien_Object $payment)
    {
        return  (strlen($payment->getData('cc_trans_id')) > 0 && $payment->getAdditionalInformation('transaction_type') == 'Authorization');
    }

    /**
     * Send capture request to gateway for existing authorization
     *
     * @param Mage_Payment_Model_Info|\Varien_Object $payment
     * @param float $amount
     */
    protected function priorAuthCapture(Varien_Object $payment, $amount)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');

        if ($amount <= 0) {
            // Don't allow prior auth capture for $0.00 transaction
            Mage::throwException(Mage::helper('autoship')->__('Invalid amount for capture.'));
        }

        // Get order, etc from $payment
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();

        // Set website / store for config on API helper
        $store = Mage::app()->getStore($order->getStoreId());
        $apiHelper->setConfigStore($store);

        // Use API to create a new prior auth-capture transaction
        $platformTransaction = $vaultHelper->capture($payment->getData('cc_trans_id'), $amount, $order->getData('base_currency_code'));

        // Save transaction details in $payment
        // 'cc_trans_id', and additional_information fields 'token', 'gateway_transaction_id' and 'type' will
        // continue to point to the original Auth transaction
        $payment
            ->setIsTransactionClosed(0)
            ->setParentTransactionId($payment->getData('cc_trans_id'))
            ->setTransactionId($platformTransaction->getId())
        ;
        // If gateway doesn't have multiple capture support, always close parent auth transaction, even on a
        // partial capture
        if (!$this->getConfigData('multiple_capture_support')) {
            $payment
                ->setShouldCloseParentTransaction(true);
        }
        // Create transaction
        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
        // Save AVS and CVV results when available
        if ($payment instanceof Mage_Sales_Model_Order_Payment) {
            if (strlen($platformTransaction->getAvsCode())) {
                $payment->setCcAvsStatus($platformTransaction->getAvsCode());
            }
            if (strlen($platformTransaction->getCvvCode())) {
                $payment->setCcCidStatus($platformTransaction->getCvvCode());
            }
        }
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
        if ($payment->getAdditionalInformation('save_card') == '1'
            || $payment->getAdditionalInformation('is_new_card') != '1')
        {
            // We are using existing pay profile or storing new one
            // Get payment profile
            $paymentProfile = $this->createOrFetchPaymentProfile(
                $payment,
                $order->getBillingAddress(),
                $payment->getData('cc_exp_month'),
                $payment->getData('cc_exp_year'));
            // Create purchase transaction
            if ($amount > 0) {
                // Capture (purchase) if positive amount
                $platformTransaction = $vaultHelper->purchase(
                    $paymentProfile,
                    $amount,
                    $order->getData('base_currency_code'),
                    array(
                        'email' => $order->getCustomerEmail(),
                        'order_id' => $order->getIncrementId(),
                        'ip' => $order->getRemoteIp(),
                        'subscribe_pro_order_token' => $payment->getAdditionalInformation('subscribe_pro_order_token'),
                    )
                );
            } else {
                // Otherwise just verify the card
                SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Cc::capture called, but only verifying due to $0.00! ======', Zend_Log::INFO);
                $platformTransaction = $vaultHelper->verify(
                    $paymentProfile,
                    $order->getData('base_currency_code'),
                    array(
                        'email' => $order->getCustomerEmail(),
                        'order_id' => $order->getIncrementId(),
                        'ip' => $order->getRemoteIp(),
                        'subscribe_pro_order_token' => $payment->getAdditionalInformation('subscribe_pro_order_token'),
                    )
                );
            }
        }
        else {
            if ($amount <= 0) {
                // Don't allow $0.00 auth for one-time purchase, should use 'free' method
                Mage::throwException(Mage::helper('autoship')->__('Invalid amount for capture.'));
            }
            // Create one-time purchase transaction
            $platformTransaction = $vaultHelper->purchaseOneTime(
                $payment->getAdditionalInformation('payment_token'),
                $amount,
                $order->getData('base_currency_code'),
                $order->getBillingAddress(),
                array(
                    'email' => $order->getCustomerEmail(),
                    'order_id' => $order->getIncrementId(),
                    'ip' => $order->getRemoteIp(),
                    'creditcard_month' => $payment->getData('cc_exp_month'),
                    'creditcard_year' => $payment->getData('cc_exp_year'),
                )
            );
        }

        // Save cc type in $payment
        $ccType = $vaultHelper->mapSubscribeProCardTypeToMagento($platformTransaction->getCreditcardType(), false);
        if (strlen($ccType)) {
            $payment->setData('cc_type', $ccType);
        }

        // Save transaction details in $payment
        $payment
            ->setIsTransactionClosed(0)
            ->setCcTransId($platformTransaction->getId())
            ->setTransactionId($platformTransaction->getId())
            ->setAdditionalInformation('transaction_token', $platformTransaction->getToken())
            ->setAdditionalInformation('gateway_transaction_id', $platformTransaction->getGatewayTransactionId())
            ->setAdditionalInformation('transaction_type', $platformTransaction->getType())
        ;
        // Save AVS and CVV results when available
        if ($payment instanceof Mage_Sales_Model_Order_Payment) {
            if (strlen($platformTransaction->getAvsCode())) {
                $payment->setCcAvsStatus($platformTransaction->getAvsCode());
            }
            if (strlen($platformTransaction->getCvvCode())) {
                $payment->setCcCidStatus($platformTransaction->getCvvCode());
            }
        }
        // Create transaction record on order / payment
        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
    }

    /**
     * Void the payment through gateway
     *
     * @param Mage_Payment_Model_Info|\Varien_Object $payment
     * @return $this
     */
    public function void(Varien_Object $payment)
    {
        // Log
        SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Cc::void called ======', Zend_Log::INFO);

        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');

        // Set website / store for config on API helper
        $store = Mage::app()->getStore($payment->getOrder()->getStoreId());
        $apiHelper->setConfigStore($store);

        // Use API to create a new prior auth-capture transaction
        $platformTransaction = $vaultHelper->void($payment->getCcTransId());

        // Save transaction details in $payment
        // Field cc_trans_id in payment should hold the single authorize trans id and then the single capture trans id
        // (or just the single auth n capture trans id)
        //
        // 'cc_trans_id', and additional_information fields 'token', 'gateway_transaction_id' and 'type' will
        // continue to point to the original Auth transaction
        $payment
            ->setIsTransactionClosed(true)
            ->setShouldCloseParentTransaction(true)
            ->setParentTransactionId($payment->getData('cc_trans_id'))
            ->setTransactionId($platformTransaction->getId());
        // Create transaction
        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID);
        // This seems to be necessary to keep 1.5.1.x and 1.10.1.x from duplicating the void transaction
        $payment->setSkipTransactionCreation(true);

        return $this;
    }

    /**
     * Cancel the payment through gateway
     *
     * @param Mage_Payment_Model_Info|\Varien_Object $payment
     * @return $this
     */
    public function cancel(Varien_Object $payment)
    {
        // Log
        SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Cc::cancel called ======', Zend_Log::INFO);

        return $this->void($payment);
    }

    /**
     * Refund the amount with transaction id
     *
     * @param Mage_Payment_Model_Info|\Varien_Object $payment
     * @param float $requestedAmount
     * @return $this
     */
    public function refund(Varien_Object $payment, $requestedAmount)
    {
        // Log
        SubscribePro_Autoship::log('====== SubscribePro_Autoship_Model_Payment_Method_Cc::refund called ======', Zend_Log::INFO);

        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');

        // Set website / store for config on API helper
        $store = Mage::app()->getStore($payment->getOrder()->getStoreId());
        $apiHelper->setConfigStore($store);

        // Get order, etc from $payment
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();

        // Get reference transaction ID
        // This should be the ID of the "purchase" transaction if auth n capture mode enabled
        // This should be the ID of the "capture" transaction if auth only mode enabled, and if there was a
        // prior-auth-capture run at some point
        // $payment object current has a refund_transaction_id field, but this may be deprecated in future, so use parent_transaction_id
        //$referenceTransactionId = $payment->getRefundTransactionId();
        $referenceTransactionId = $payment->getParentTransactionId();

        // Use API to create a new prior auth-capture transaction
        $platformTransaction = $vaultHelper->credit($referenceTransactionId, $requestedAmount, $order->getData('base_currency_code'));

        /**
         * Duplicate logic from standard Authorize.net payment method:
         * This means that we should close the parent transaction if we have refunded the full amount of original transaction
         */
        $shouldCloseRefundTransaction = 0;
        if ($this->formatAmount($payment->getAmountPaid() - $payment->getAmountRefunded()) == $this->formatAmount($requestedAmount)) {
            $shouldCloseRefundTransaction = 1;
        }

        // Save transaction details in $payment
        // Field cc_trans_id in payment should hold the single original (purchase or capture) trans id
        // (or just the single auth n capture trans id)
        //
        // 'cc_trans_id', and additional_information fields 'token', 'gateway_transaction_id' and 'type' will
        // continue to point to the original transaction
        $payment
            ->setIsTransactionClosed(1)
            ->setShouldCloseParentTransaction($shouldCloseRefundTransaction)
            ->setParentTransactionId($payment->getData('cc_trans_id'))
            ->setTransactionId($platformTransaction->getId());
        // This seems to be necessary to keep 1.5.1.x and 1.10.1.x from causing "transaction already closed" error on 1.5.1.x and 1.10.1.x
        $payment->setSkipTransactionCreation(true);
        // Create transaction
        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param array $data
     */
    protected function createNewPaymentToken(Mage_Sales_Model_Quote $quote, $data)
    {

        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');
        // Set website / store for config on API helper
        $apiHelper->setConfigStore($quote->getStore());
        // Call api to create new payment token
        $paymentToken = $vaultHelper->createPaymentToken(
            $quote->getBillingAddress(),
            $data['cc_number'],
            $data['cc_exp_month'],
            $data['cc_exp_year'],
            $data['cc_cid']
        );

        // Now obfuscate card number and store token
        $data['cc_number'] = $paymentToken->getFirstSixDigits() . 'XXXXXX' . $paymentToken->getLastFourDigits();
        $data['cc_cid'] = 'XXX';
        $data['payment_token'] = $paymentToken->getToken();
    }

    protected function createOrFetchPaymentProfile(Varien_Object $payment, Mage_Customer_Model_Address_Abstract $billingAddress, $expMonth, $expYear)
    {
        // Log
        SubscribePro_Autoship::log('createOrFetchPaymentProfile()', Zend_Log::INFO);

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
            Mage::exception("Can't save credit card because no customer is associated with order!  Credit cards can't be saved with guest checkout feature.");
        }

        try {
            // Get payment token
            $paymentToken = $payment->getAdditionalInformation('payment_token');
            // Check if this is a new card
            $isNewCard = $payment->getAdditionalInformation('is_new_card') == '1';
            // Get token details
            //$paymentTokenDetails = $vaultHelper->getPaymentTokenDetails($paymentToken);
            //$paymentTokenStatus = $paymentTokenDetails['storage_state'];
            $paymentTokenStatus = 'cached';
            // Check status of token and new card flag
            if ($isNewCard) {
                // This is a new card
                if ($paymentTokenStatus == 'cached') {
                    // This is a new card, its only cache at this point, store it
                    // Create or update customer on platform
                    $platformCustomer = $customerHelper->createOrUpdatePlatformCustomer($customer);
                    // Store token as new pay profile
                    $paymentProfile = $vaultHelper->createPaymentProfileFromToken($platformCustomer->getId(), $paymentToken, $customer, $billingAddress, $expMonth, $expYear);
                }
                else if ($paymentTokenStatus = 'retained') {
                    // This is a new card, must have already been stored in a failed checkout type
                    // Just try to fetch it
                    //$paymentProfile = $vaultHelper->getPaymentProfileByToken($paymentToken);
                    // Now update billing address & exp date
                    //$this->updatePaymentProfile($paymentProfile, $billingAddress, $expMonth, $expYear);
                    // Create or update customer on platform
                    $platformCustomer = $customerHelper->createOrUpdatePlatformCustomer($customer);
                    // Store token as new pay profile
                    $paymentProfile = $vaultHelper->createPaymentProfileFromToken($platformCustomer->getId(), $paymentToken, $customer, $billingAddress, $expMonth, $expYear);
                }
                else {
                    throw new SubscribePro_Autoship_Helper_PaymentError_Exception(Mage::helper('autoship')->__('Credit card information has expired!  Please reenter card details.'));
                }
            }
            else {
                // This is an existing card, just fetch it
                // Just try to fetch it
                $paymentProfile = $vaultHelper->getPaymentProfileByToken($paymentToken);
                if (!$paymentProfile->getPaymentMethodType() == PaymentProfileInterface::TYPE_THIRD_PARTY_TOKEN) {
                    // Third party profiles can't be updated
                    // Now update billing address & exp date
                    //$this->updatePaymentProfile($paymentProfile, $billingAddress, $expMonth, $expYear);
                }
            }
            if (!$paymentProfile->getPaymentMethodType() == PaymentProfileInterface::TYPE_THIRD_PARTY_TOKEN) {
                // Third party profiles won't necessarily have the card type set
                // Update CC card type on payment record
                // Save cc type in $payment
                //$ccType = $vaultHelper->mapSubscribeProCardTypeToMagento($paymentProfile->getData('creditcard_type'), false);
                //if (strlen($ccType)) {
                  //  $payment->setCcType($ccType);
                //}
            }

            // Save payment profile id in additional info
            $payment->setAdditionalInformation('payment_profile_id', $paymentProfile->getId());

            // Finally return the profile
            return $paymentProfile;
        }
        catch (SubscribePro_Autoship_Helper_PaymentError_Exception $e) {
            // Rethrow these exceptions as the message will be customer friendly
            throw $e;
        }
        catch (Exception $e) {
            // Throw new exception with generic message
            Mage::throwException(Mage::helper('autoship')->__('Failed to store credit card for payment transaction!'));
        }
    }

    protected function fetchExistingPaymentProfile(Varien_Object $payment)
    {
        // Log
        SubscribePro_Autoship::log('fetchExistingPaymentProfile()', Zend_Log::INFO);

        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');

        // Get order, etc from $payment
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();

        // Get payment profile id
        $paymentProfileId = $payment->getAdditionalInformation('payment_profile_id');

        // This is an existing card, just fetch it
        // Just try to fetch it
        $paymentProfile = $vaultHelper->getPaymentProfile($paymentProfileId);

        // Finally return the profile
        return $paymentProfile;
    }

    /**
     * @param PaymentProfileInterface $paymentProfile
     * @param Mage_Customer_Model_Address_Abstract $billingAddress
     * @param $expMonth
     * @param $expYear
     */
    protected function updatePaymentProfile(PaymentProfileInterface $paymentProfile, Mage_Customer_Model_Address_Abstract $billingAddress, $expMonth, $expYear)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');

        $vaultHelper->updatePaymentProfile($paymentProfile);
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
