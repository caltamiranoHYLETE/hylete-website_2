<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_Paypal_Paypal extends Mage_Payment_Model_Method_Abstract
{
    const METHOD_GUEST = 'guest';
    const METHOD_REGISTER = 'register';
    const METHOD_CUSTOMER = 'customer';
    const CODE = 'cybersourcepaypal';

    protected $_code = self::CODE;
    protected $_formBlockType = 'cybersourcepaypal/form_paypal';
    protected $_infoBlockType = 'cybersourcepaypal/info_paypal';
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canOrder = true;
    protected $_isInitializeNeeded = false;
    protected $_canUseInternal = false;

    public $_customerSession = null;
    public $_checkout = null;
    public $_customer = null;

    public function __construct()
    {
        $this->_customerSession = Mage::getSingleton('customer/session');
        $this->_checkout = Mage::getSingleton('checkout/session');
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see Mage_Checkout_OnepageController::savePaymentAction()
     * @see Mage_Sales_Model_Quote_Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return Mage::getUrl('cybersource/paypal/init');
    }

    public function validate()
    {
        return $this;
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        //call soap API
        $response = Mage::getModel('cybersourcepaypal/soapapi_paypal')->payPalEcDoPaymentService($payment->getOrder());

        /** @var $payment Mage_Sales_Model_Order_Payment */
        $payment->setAdditionalInformation('merchantReferenceCode', $response->merchantReferenceCode);
        $payment->setAdditionalInformation('authRequestID', $response->requestID);
        $payment->setAdditionalInformation('authRequestToken', $response->requestToken);
        $payment->setAdditionalInformation('authPaypalToken', $response->payPalEcDoPaymentReply->paypalToken);
        $payment->setAdditionalInformation('authOrderId', $response->payPalEcDoPaymentReply->orderId);
        $payment->setAdditionalInformation('authTransactionId', $response->payPalEcDoPaymentReply->transactionId);
        $payment->setAdditionalInformation('authCorrelationID', $response->payPalEcDoPaymentReply->correlationID);

        $payment->setTransactionId($response->payPalEcDoPaymentReply->transactionId);
        $payment->setIsTransactionClosed(0);

        $this->importPayPalAddress($response, $payment);

        return $this;
    }

    public function capture(Varien_Object $payment, $amount)
    {
        /** @var $payment Mage_Sales_Model_Order_Payment */
        if (! $authorizationTransaction = $payment->getAuthorizationTransaction()) {
            $this->authorize($payment, $amount);
        }

        if (Mage::app()->getStore()->isAdmin()) {
            $order = $payment->getOrder();
            $storeId = $order->getStore()->getId();

            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

            try {
                $response = Mage::getModel('cybersourcepaypal/soapapi_paypal')->payPalDoCaptureService($payment, $amount);
            } catch (Exception $e) {
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                throw $e;
            }

            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        } else {
            $response = Mage::getModel('cybersourcepaypal/soapapi_paypal')->payPalDoCaptureService($payment, $amount);
        }

        $payment->setAdditionalInformation('captureRequestID', $response->requestID);
        $payment->setAdditionalInformation('captureRequestToken', $response->requestToken);
        $payment->setAdditionalInformation('captureTransactionID', $response->payPalDoCaptureReply->transactionId);
        $payment->setAdditionalInformation('captureCorrelationID', $response->payPalDoCaptureReply->correlationID);

        $payment->setTransactionId($response->payPalDoCaptureReply->transactionId);
        $payment->setShouldCloseParentTransaction(1);

        return $this;
    }

    public function refund(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();
        $storeId = $order->getStore()->getId();

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            $response = Mage::getModel('cybersourcepaypal/soapapi_paypal')->payPalRefundService($payment, $amount);
        } catch (Exception $e) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $e;
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        /** @var $payment Mage_Sales_Model_Order_Payment */
        $payment->setAdditionalInformation('refundRequestID', $response->requestID);
        $payment->setAdditionalInformation('refundTransactionID', $response->payPalRefundReply->transactionId);

        $payment->setTransactionId($response->payPalRefundReply->transactionId);
        $payment->setShouldCloseParentTransaction(1);

        return $this;
    }

    public function void(Varien_Object $payment)
    {
        $order = $payment->getOrder();
        $storeId = $order->getStore()->getId();

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            $response = Mage::getModel('cybersourcepaypal/soapapi_paypal')->payPalAuthReversalService($payment);
        } catch (Exception $e) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $e;
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        /** @var $payment Mage_Sales_Model_Order_Payment */
        $payment->setAdditionalInformation('reversalRequestID', $response->requestID);
        $payment->setAdditionalInformation('reversalAuthorizationID', $response->payPalAuthReversalReply->authorizationId);

        $payment->setTransactionId($response->payPalAuthReversalReply->transactionId);
        $payment->setShouldCloseParentTransaction(1);
        $payment->setIsTransactionClosed(1);

        return $this;
    }

    public function cancel(Varien_Object $payment)
    {
        return $this->void($payment);
    }

    public function canVoid(Varien_Object $payment)
    {
        $paymentInfo = $payment->getAdditionalInformation();
        return !isset($paymentInfo['captureTransactionID'])
            && !isset($paymentInfo['reversalRequestID']);
    }

    /**
     * Get customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    private function importPayPalAddress($response, &$payment)
    {
        if (property_exists($response->payPalEcGetDetailsReply, 'street1')) {
            $payment->setAdditionalInformation('ppPayerFirstname', $response->payPalEcGetDetailsReply->payerFirstname);
        }
        if (property_exists($response->payPalEcGetDetailsReply, 'street2')) {
            $payment->setAdditionalInformation('ppPayerLastname', $response->payPalEcGetDetailsReply->payerLastname);
        }
        if (property_exists($response->payPalEcGetDetailsReply, 'street1')) {
            $payment->setAdditionalInformation('ppStreet1', $response->payPalEcGetDetailsReply->street1);
        }
        if (property_exists($response->payPalEcGetDetailsReply, 'street2')) {
            $payment->setAdditionalInformation('ppStreet2', $response->payPalEcGetDetailsReply->street2);
        }
        if (property_exists($response->payPalEcGetDetailsReply, 'city')) {
            $payment->setAdditionalInformation('ppCity', $response->payPalEcGetDetailsReply->city);
        }
        if (property_exists($response->payPalEcGetDetailsReply, 'state')) {
            $payment->setAdditionalInformation('ppState', $response->payPalEcGetDetailsReply->state);
        }
        if (property_exists($response->payPalEcGetDetailsReply, 'postalCode')) {
            $payment->setAdditionalInformation('ppPostcode', $response->payPalEcGetDetailsReply->postalCode);
        }
        if (property_exists($response->payPalEcGetDetailsReply, 'countryName')) {
            $payment->setAdditionalInformation('ppCountry', $response->payPalEcGetDetailsReply->countryName);
        }
        if (property_exists($response->payPalEcGetDetailsReply, 'payerPhone')) {
            $payment->setAdditionalInformation('ppPhonenumber', $response->payPalEcGetDetailsReply->payerPhone);
        }
    }
}
