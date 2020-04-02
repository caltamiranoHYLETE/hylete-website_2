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

class SubscribePro_Autoship_Helper_Payment extends Mage_Payment_Helper_Data
{

    /**
     * @param string $code
     * @return bool
     */
    public function isSubscribeProCreditCardMethod($code)
    {
        // Check for token payment method
        $keyCc = SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE . SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE_KEY_TOKEN;
        if ($keyCc == substr($code, 0, strlen($keyCc))) {
            return true;
        }
        else if($code == SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE) {
            return true;
        }

        return false;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function isSubscribeProBankAccountMethod($code)
    {
        // Check for token payment method
        $keyEcheck = SubscribePro_Autoship_Model_Payment_Method_Echeck::METHOD_CODE . SubscribePro_Autoship_Model_Payment_Method_Echeck::METHOD_CODE_KEY_TOKEN;
        if ($keyEcheck == substr($code, 0, strlen($keyEcheck))) {
            return true;
        }
        else if($code == SubscribePro_Autoship_Model_Payment_Method_Echeck::METHOD_CODE) {
            return true;
        }

        return false;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function isSubscribeProApplePayMethod($code)
    {
        // Check for token payment method
        $keyApplepay = SubscribePro_Autoship_Model_Payment_Method_Applepay::METHOD_CODE . SubscribePro_Autoship_Model_Payment_Method_Applepay::METHOD_CODE_KEY_TOKEN;
        if ($keyApplepay == substr($code, 0, strlen($keyApplepay))) {
            return true;
        }
        else if($code == SubscribePro_Autoship_Model_Payment_Method_Applepay::METHOD_CODE) {
            return true;
        }

        return false;
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public function isAnySubscribeProPayMethod($code)
    {
        return $this->isSubscribeProCreditCardMethod($code)
            || $this->isSubscribeProBankAccountMethod($code)
            || $this->isSubscribeProApplePayMethod($code);
    }

    /**
     * @param string $code
     * @return null|string
     */
    public function getPaymentTokenFromMethodCode($code)
    {
        // Check if code includes key
        $keyCc = SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE . SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE_KEY_TOKEN;
        $keyEcheck = SubscribePro_Autoship_Model_Payment_Method_Echeck::METHOD_CODE . SubscribePro_Autoship_Model_Payment_Method_Echeck::METHOD_CODE_KEY_TOKEN;
        if ($keyCc == substr($code, 0, strlen($keyCc))) {
            $encodedMethodParts = explode(SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE_KEY_TOKEN, $code);
            $paymentToken = $encodedMethodParts[1];

            return $paymentToken;
        }
        else if ($keyEcheck == substr($code, 0, strlen($keyEcheck))) {
            $encodedMethodParts = explode(SubscribePro_Autoship_Model_Payment_Method_Echeck::METHOD_CODE_KEY_TOKEN, $code);
            $paymentToken = $encodedMethodParts[1];

            return $paymentToken;
        }
        else {
            return null;
        }
    }

    /**
     * Retrieve method model object
     *
     * @param   string $code
     * @return  Mage_Payment_Model_Method_Abstract|false
     */
    public function getMethodInstance($code)
    {
        // Log
        SubscribePro_Autoship::log('SubscribePro_Autoship_Helper_Payment::getMethodInstance', Zend_Log::INFO);
        SubscribePro_Autoship::log("Method code: '{$code}'", Zend_Log::INFO);

        // Check if code includes key
        if ($this->isSubscribeProCreditCardMethod($code)) {
            $classKey = self::XML_PATH_PAYMENT_METHODS . '/' . SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE . '/model';
            $class = Mage::getStoreConfig($classKey);
            /** @var SubscribePro_Autoship_Model_Payment_Method_Cc $model */
            $model = Mage::getModel($class);

            return $model;
        }
        else if ($this->isSubscribeProBankAccountMethod($code)) {
            $classKey = self::XML_PATH_PAYMENT_METHODS . '/' . SubscribePro_Autoship_Model_Payment_Method_Echeck::METHOD_CODE . '/model';
            $class = Mage::getStoreConfig($classKey);
            /** @var SubscribePro_Autoship_Model_Payment_Method_Echeck $model */
            $model = Mage::getModel($class);

            return $model;
        }
        else {
            return parent::getMethodInstance($code);
        }
    }

    /**
     *
     * @param mixed $store
     * @param mixed $quote
     * @return mixed
     */
    public function getStoreMethods($store = null, $quote = null)
    {
        // Log
        SubscribePro_Autoship::log('SubscribePro_Autoship_Helper_Payment::getStoreMethods', Zend_Log::INFO);
        if(is_numeric($store)) {
            SubscribePro_Autoship::log('Store Id: ' . $store, Zend_Log::INFO);
        }
        if($store != null && is_object($store)) {
            SubscribePro_Autoship::log('Store Id: ' . $store->getId(), Zend_Log::INFO);
        }
        if(is_numeric($quote)) {
            SubscribePro_Autoship::log('Quote Id: ' . $quote, Zend_Log::INFO);
        }
        if($quote != null && is_object($quote)) {
            SubscribePro_Autoship::log('Quote Id: ' . $quote->getId(), Zend_Log::INFO);
        }

        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');

        // Call parent implementation
        $parentMethods = parent::getStoreMethods($store, $quote);
        // Build new list
        $methods = array();
        // Find payment method in list
        /** @var Mage_Payment_Model_Method_Abstract $method */
        foreach ($parentMethods as $method) {
            // Check, is this method current method
            if ($method->getCode() == SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE) {
                // Add extra token methods
                // Get customer
                $customer = $this->getCustomerForProfiles($quote);
                if (strlen($customer->getId())) {
                    // Set website / store for config on API helper
                    $store = Mage::app()->getWebsite($customer->getData('website_id'))->getDefaultStore();
                    $apiHelper->setConfigStore($store);
                    $paymentProfiles = $vaultHelper->getCreditCardProfilesForCustomer($customer);
                    // Iterate payment profiles
                    /** @var \SubscribePro\Service\PaymentProfile\PaymentProfileInterface $paymentProfile */
                    foreach ($paymentProfiles as $paymentProfile) {
                        // Build new method out of profile
                        /** @var  $newMethodInstance */
                        $newMethodInstance = $this->getMethodInstance(SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE);
                        $newMethodInstance->setSavedPaymentProfile($paymentProfile);
                        $methods[] = $newMethodInstance;
                    }
                }
            }
            // Check, is this method current method
            else if ($method->getCode() == SubscribePro_Autoship_Model_Payment_Method_Echeck::METHOD_CODE) {
                // Add extra token methods
                // Get customer
                $customer = $this->getCustomerForProfiles($quote);
                if (strlen($customer->getId())) {
                    // Set website / store for config on API helper
                    $store = Mage::app()->getWebsite($customer->getData('website_id'))->getDefaultStore();
                    $apiHelper->setConfigStore($store);
                    $paymentProfiles = $vaultHelper->getBankAccountProfilesForCustomer($customer);
                    // Iterate payment profiles
                    /** @var \SubscribePro\Service\PaymentProfile\PaymentProfileInterface $paymentProfile */
                    foreach ($paymentProfiles as $paymentProfile) {
                        // Build new method out of profile
                        /** @var  $newMethodInstance */
                        $newMethodInstance = $this->getMethodInstance(SubscribePro_Autoship_Model_Payment_Method_Echeck::METHOD_CODE);
                        $newMethodInstance->setSavedPaymentProfile($paymentProfile);
                        $methods[] = $newMethodInstance;
                    }
                }
            }
            // Add method from parent implementation
            $methods[] = $method;
        }

        return $methods;
    }

    /**
     * Reauthorize the authorization transaction on an order that used Subscribe Pro Vault payment method in
     * Authorize-Only mode.
     *
     * @param Mage_Sales_Model_Order $order
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function reauthorizeOrder(Mage_Sales_Model_Order $order)
    {
        // Check for SP pay method
        if (!$this->isSubscribeProCreditCardMethod($order->getPayment()->getMethod())) {
            Mage::throwException("Reauthorize only applicable to orders which were paid with Subscribe Pro Vault payment method!");
        }

        // Get payment method instance
        /** @var SubscribePro_Autoship_Model_Payment_Method_Cc $methodInstance */
        $methodInstance = $order->getPayment()->getMethodInstance();
        $methodInstance->setStore($order->getStoreId());
        // Get payment
        $payment = $order->getPayment();

        // Check this was an authorize only transaction
        if (!$methodInstance->canReauthorize($payment)) {
            Mage::throwException("Reauthorize only applicable to orders which were ordered in authorize-only mode, where credit card was stored and other criteria were met!");
        }

        // Get amount
        $amount = $payment->getData('amount_authorized') - $order->getPayment()->getData('amount_paid');

        // Attempt a void when current Authorization transaction is still open
        $transaction = $payment->getTransaction($payment->getCcTransId());
        if (!$transaction->getIsClosed()) {
            try {
                // Void
                $order->getPayment()->void(
                    new Varien_Object() // workaround for backwards compatibility
                );
                $order->save();
            }
            catch (Exception $e) {
                // Void failed, but lets ignore that and reauthorize anyway
                SubscribePro_Autoship::log('Void failed, continuing to reauthorize!', Zend_Log::ERR);
            }
            // Reload order after void
            $order = Mage::getModel('sales/order')->load($order->getId());
        }

        // Reauthorize
        $methodInstance->reauthorize($order->getPayment(), $amount);
        // Add transaction message to order history
        $message = Mage::helper('autoship')->__('Reauthorized amount of ' . $this->formatPrice($order, $amount) . '. Transaction ID: "' . $order->getPayment()->getTransactionId() . '"');
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message);
        // Save order
        $order->save();

    }

    /**
     * @return Mage_Core_Model_Store
     */
    protected function detectStore()
    {
        if (!Mage::app()->getStore()->isAdmin()) {
            return Mage::app()->getStore();
        }
        else {
            // If we are in admin store, try to find correct store from current quote
            /** @var Mage_Adminhtml_Model_Session_Quote $adminhtmlQuoteSession */
            $adminhtmlQuoteSession = Mage::getSingleton('adminhtml/session_quote');
            $quote = $adminhtmlQuoteSession->getQuote();
            $store = $quote->getStore();

            return $store;
        }
    }

    /**
     * @param Mage_Sales_Model_Quote|null $quote
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomerForProfiles($quote)
    {
        // If quote is null, use customer from session
        if ($quote != null && is_object($quote) && $quote instanceof Mage_Sales_Model_Quote) {
            $customer = $quote->getCustomer();

            return $customer;
        }
        else {
            /** @var Mage_Customer_Model_Session $customerSession */
            $customerSession = Mage::getSingleton('customer/session');
            /** @var Mage_Customer_Model_Customer $customer */
            $customer = $customerSession->getCustomer();

            return $customer;
        }
    }


    /**
     * Format price with currency sign
     * @param Mage_Sales_Model_Order $order
     * @param float $amount
     * @param null|string $currency
     * @return string
     */
    protected function formatPrice(Mage_Sales_Model_Order $order, $amount, $currency = null)
    {
        return $order->getBaseCurrency()->formatTxt(
            $amount,
            $currency ? array('currency' => $currency) : array()
        );
    }

}
