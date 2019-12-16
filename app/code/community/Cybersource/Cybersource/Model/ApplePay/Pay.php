<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Pay extends Mage_Payment_Model_Method_Cc
{
    /**
     * Payment method code
     * @access protected
     * @var string
     */
    const CODE = 'cybersourceapplepay';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * System config path of the block displayed when redirected to cybersource
     * @access protected
     * @var string
     */
    protected $_formBlockType = 'cybersourceapplepay/form_applepay';

    /**
     * System config path
     * @access protected
     * @var string
     */
    protected $_infoBlockType = 'cybersourceapplepay/info_applepay';

    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_isInitializeNeeded = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        if ($data->getToken()) {
            $tokenData = json_decode($data->getToken(), true);
            $this->getInfoInstance()->setAdditionalInformation('token', $tokenData);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function validate()
    {
        return $this;
    }

    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canRefund()
    {
        return $this->_canRefund;
    }

    /**
     * Check void availability
     *
     * @param   Varien_Object $payment
     * @return  bool
     */
    public function canVoid(Varien_Object $payment)
    {
        return $this->_canVoid;
    }

    /**
     * Void the payment
     *
     * @param Varien_Object $payment
     * @return $this
     * @throws Exception When unable to void a transaction
     */
    public function void(Varien_Object $payment)
    {
        $void = new Cybersource_Cybersource_Model_ApplePay_Pay_Void();

        $order = $payment->getOrder();
        $storeId = $order->getStore()->getId();

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            $void->void($payment);
        } catch (Exception $e) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $e;
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        return $this;
    }

    /**
     * Cancel the payment
     *
     * @param Varien_Object $payment
     * @return Cybersource_Cybersource_Model_ApplePay_Pay
     * @throws Exception When unable to cancel a payment
     */
    public function cancel(Varien_Object $payment)
    {
        return $this->void($payment);
    }

    /**
     * Refund the payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Exception When unable to refund a payment
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $refund = new Cybersource_Cybersource_Model_ApplePay_Pay_Refund();

        $order = $payment->getOrder();
        $storeId = $order->getStore()->getId();

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            $refund->refund($payment, $amount);
        } catch (Exception $e) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $e;
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        return $this;
    }

    /**
     * Authorize the payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Exception When unable to authorize a payment
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $authorization = new Cybersource_Cybersource_Model_ApplePay_Pay_Authorize();
        $authorization->authorize($payment);
        return $this;
    }

    /**
     * Capture the payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Exception When unable to capture a payment
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $capture = new Cybersource_Cybersource_Model_ApplePay_Pay_Capture();

        if (! Mage::app()->getStore()->isAdmin()) {
            $capture->capture($payment, $amount);
            return $this;
        }

        $order = $payment->getOrder();
        $storeId = $order->getStore()->getId();

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            $capture->capture($payment, $amount);
        } catch (Exception $e) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $e;
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        return $this;
    }
}
