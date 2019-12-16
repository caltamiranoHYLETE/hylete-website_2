<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_BankTransfer_Payment_Abstract extends Mage_Payment_Model_Method_Abstract
{
    protected $allowedCurrencies = array('EUR');

    /**
     * @var string
     */
    protected $_formBlockType = 'cybersourcebanktransfer/form_banktransfer';

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canUseForMultishipping = false;

    /**
     * @var bool
     */
    protected $_canManageRecurringProfiles = false;

    /**
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return Mage::getUrl('cybersource/bt/index', array('_secure' => true));
    }

    /**
     * @param null $quote
     * @return bool
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isAvailable($quote = null)
    {
        $isAvailable = parent::isAvailable($quote);
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

        return $isAvailable && in_array($currencyCode, $this->allowedCurrencies);
    }

    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        /** @var $payment Mage_Sales_Model_Order_Payment */
        if (! $saleTransactionId = $payment->getAdditionalInformation('saleRequestID')) {
            throw new Exception('Sale transaction id was not found.');
        }

        $payment->setIsTransactionClosed(0);
        $payment->setLastTransId($saleTransactionId);
        $payment->setTransactionId($saleTransactionId);

        return $this;
    }

    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Exception
     */
    public function refund(Varien_Object $payment, $amount)
    {
        parent::refund($payment, $amount);

        $order = $payment->getOrder();
        $storeId = $order->getStore()->getId();

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            Mage::getModel('cybersourcebanktransfer/soapapi_banktransfer')->requestRefund($payment);
        } catch (Exception $e) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $e;
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        return $this;
    }
}
