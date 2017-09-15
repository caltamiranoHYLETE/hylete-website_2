<?php

/**
 * Class Gene_ApplePay_Model_Paymentmethod
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_ApplePay_Model_Paymentmethod extends Gene_Braintree_Model_Paymentmethod_Abstract
{
    /**
     * Setup block types
     *
     * @var string
     */
    protected $_formBlockType = 'gene_applepay/method';
    protected $_infoBlockType = 'gene_applepay/method_info';

    /**
     * Set the code
     *
     * @var string
     */
    protected $_code = 'gene_braintree_applepay';

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway = false;
    protected $_canOrder = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_isInitializeNeeded = false;
    protected $_canFetchTransactionInfo = false;
    protected $_canReviewPayment = true;
    protected $_canCreateBillingAgreement = false;
    protected $_canManageRecurringProfiles = false;

    /**
     * Verify that the module has been setup
     *
     * @param null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (!Mage::helper('gene_applepay')->hasDependencies()) {
            return false;
        }

        // Check Magento's internal methods allow us to run
        if (parent::isAvailable($quote)) {
            // Should the method be hidden from the checkout?
            if (!$this->getIsSetupRequiredCall() && $this->_getConfig('disable_checkout') == 1) {
                return false;
            }

            // Validate the configuration is okay
            if ($this->_getWrapper()->validateCredentialsOnce()) {
                return true;
            }
        } else {
            // Otherwise it's a no
            return false;
        }
    }

    /**
     * Place Braintree specific data into the additional information of the payment instance object
     *
     * @param   mixed $data
     *
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('payment_method_nonce', $data->getData('payment_method_nonce'))
            ->setAdditionalInformation('device_data', $data->getData('device_data'));

        return $this;
    }

    /**
     * Return the payment method nonce from the info instance
     *
     * @return null|string
     */
    public function getPaymentMethodNonce()
    {
        return $this->getInfoInstance()->getAdditionalInformation('payment_method_nonce');
    }


    /**
     * Psuedo _authorize function so we can pass in extra data
     *
     * @param \Varien_Object $payment
     * @param                $amount
     * @param bool|false     $shouldCapture
     * @param bool|false     $token
     *
     * @return $this
     * @throws \Mage_Core_Exception
     */
    protected function _authorize(Varien_Object $payment, $amount, $shouldCapture = false, $token = false)
    {
        // Confirm that we have a nonce from Braintree
        // We cannot utilise the validate() function as these checks need to happen at the capture point
        if (!$this->getPaymentMethodNonce()) {
            Mage::throwException(
                $this->_getHelper()->__('There has been an issue processing your Apple Pay payment, please try again.')
            );
        }

        // Init the environment
        $this->_getWrapper()->init();

        // Retrieve the amount we should capture
        $amount = $this->_getWrapper()->getCaptureAmount($payment->getOrder(), $amount);

        // Attempt to create the sale
        try {
            // Build up the sale array
            $saleArray = $this->_getWrapper()->buildSale(
                $amount,
                $this->_buildPaymentRequest($token),
                $payment->getOrder(),
                $shouldCapture,
                $this->getInfoInstance()->getAdditionalInformation('device_data')
            );

            // Attempt to make the sale, firstly dispatching an event
            $result = $this->_getWrapper()->makeSale(
                $this->_dispatchSaleArrayEvent('gene_braintree_applepay_sale_array', $saleArray, $payment)
            );

        } catch (Exception $e) {
            // Dispatch an event for when a payment fails
            Mage::dispatchEvent('gene_braintree_applepay_failed_exception', array('payment' => $payment, 'exception' => $e));

            return $this->_processFailedResult($this->_getHelper()->__('We were unable to complete your purchase through Apple Pay, please try again or an alternative payment method.'), $e);
        }

        // Log the result
        Gene_Braintree_Model_Debug::log(array('result' => $result));

        // If the sale has failed
        if ($result->success != true) {
            // Dispatch an event for when a payment fails
            Mage::dispatchEvent('gene_braintree_applepay_failed', array('payment' => $payment, 'result' => $result));

            return $this->_processFailedResult($this->_getHelper()->__('%s. Please try again or attempt refreshing the page.', rtrim($result->message, '.')));
        }

        $this->_processSuccessResult($payment, $result, $amount);

        return $this;
    }

    /**
     * Build up the payment request
     *
     * @param $token
     *
     * @return array
     */
    protected function _buildPaymentRequest($token)
    {
        // Build our payment array with either our token, or nonce
        $paymentArray = array();

        // If we have an original token use that for the subsequent requests
        if ($originalToken = $this->_getOriginalToken()) {
            $paymentArray['paymentMethodToken'] = $originalToken;

            return $paymentArray;
        }

        $paymentArray['paymentMethodNonce'] = $this->getPaymentMethodNonce();

        // If a token is present in the request use that
        if ($token) {
            // Remove this unneeded data
            unset($paymentArray['paymentMethodNonce']);

            // Send the token as the payment array
            $paymentArray['paymentMethodToken'] = $token;
        }

        return $paymentArray;
    }

    /**
     * Authorize the requested amount
     *
     * @param \Varien_Object $payment
     * @param float          $amount
     *
     * @return \Gene_Braintree_Model_Paymentmethod_Paypal
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        return $this->_authorize($payment, $amount, false);
    }

    /**
     * Process capturing of a payment
     *
     * @param \Varien_Object $payment
     * @param float          $amount
     *
     * @return \Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        return $this->_captureAuthorized($payment, $amount);
    }

    /**
     * Process a successful result from the sale request
     *
     * @param Varien_Object               $payment
     * @param Braintree_Result_Successful $result
     * @param                             $amount
     *
     * @return Varien_Object
     */
    protected function _processSuccessResult(Varien_Object $payment, $result, $amount)
    {
        // Pass an event if the payment was a success
        Mage::dispatchEvent('gene_braintree_applepay_success', array(
            'payment' => $payment,
            'result' => $result,
            'amount' => $amount
        ));

        // Set some basic things
        $payment->setStatus(self::STATUS_APPROVED)
            ->setCcTransId($result->transaction->id)
            ->setLastTransId($result->transaction->id)
            ->setTransactionId($result->transaction->id)
            ->setIsTransactionClosed(0)
            ->setAmount($amount)
            ->setShouldCloseParentTransaction(false);

        // Set information about the card
        $payment->setCcOwner($result->transaction->applePayCardDetails->cardholderName)
            ->setCcLast4($result->transaction->applePayCardDetails->last4)
            ->setCcType($result->transaction->applePayCardDetails->cardType)
            ->setCcExpMonth($result->transaction->applePayCardDetails->expirationMonth)
            ->setCcExpYear($result->transaction->applePayCardDetails->expirationYear);

        // Handle any fraud response from Braintree
        $this->handleFraud($result, $payment);

        return $payment;
    }
}
