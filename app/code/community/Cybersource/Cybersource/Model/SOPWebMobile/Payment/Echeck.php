<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Payment_Echeck extends Mage_Payment_Model_Method_Abstract
{
    const CODE = 'cybersourceecheck';

    protected $allowedCurrencies = array('USD', 'CAD');

    /**
     * @access protected
     * @var string
     */
    protected $_formBlockType = 'cybersourcesop/form_pay';

    /**
     * @access protected
     * @var string
     */
    protected $_infoBlockType = 'cybersourcesop/info_pay';

    /**
     * Payment method code
     * @access protected
     * @var string
     */
    protected $_code = self::CODE;

    /**
     *
     * @access protected
     * @var bool
     */
    protected $_isGateway = true;

    /**
     *
     * @access protected
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * Used when checking out with saved card
     * @access protected
     * @var bool
     */
    protected $_canSaveCc = false;

    /**
     * Used to check if multi-shipping is enabled
     * @access protected
     * @var bool
     */
    protected $_canUseForMultishipping = false;

    /**
     * @access protected
     * @var bool
     */
    protected $_canAuthorize = false;

    /**
     * @access protected
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Used during refund
     * @access protected
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * Used to void transaction
     * @access protected
     * @var bool
     */
    protected $_canVoid = false;

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
     * @return Cybersource_Cybersource_Model_SOPWebMobile_Payment_Echeck
     * @throws Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if (! $response = Mage::registry('cyber_response')) {
            throw new Exception('CyberSource response is empty.');
        }

        $this->processCyberResponse($payment, $response);

        $payment->setAdditionalInformation('captureTransactionID', $response['transaction_id']);
        $payment->setAdditionalInformation('captureRequestID', $response['transaction_id']);
        $payment->setAdditionalInformation('captureRequestToken', $response['request_token']);

        return $this;
    }

    /**
     * @param $payment
     * @param $response
     * @return $this
     */
    private function processCyberResponse(&$payment, $response)
    {
        $payment
            ->setIsTransactionClosed(0)
            ->setLastTransId($response['transaction_id'])
            ->setTransactionId($response['transaction_id'])
            ->setIsTransactionPending(1);

        if ($response['reason_code'] == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_DM_REVIEW) {
            $payment->setIsFraudDetected(1);
        }

        if ($response['is_fraud_detected']) {
            $payment->setIsFraudDetected(1);
        }

        if (!empty($response['req_echeck_account_number'])) {
            $payment->setAdditionalInformation('echeck_account_masked', 'xxxx' . substr($response['req_echeck_account_number'], -4));
        }

        if (!empty($response['req_echeck_routing_number'])) {
            $payment->setAdditionalInformation('echeck_routing_masked', 'xxxx' . substr($response['req_echeck_routing_number'], -4));
        }

        return $this;
    }
}
