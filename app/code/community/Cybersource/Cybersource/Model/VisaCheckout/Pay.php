<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_VisaCheckout_Pay extends Mage_Payment_Model_Method_Abstract
{
    const CODE = 'cybersourcevisacheckout';

    protected $successCodeList = array(
        Cybersource_Cybersource_Model_VisaCheckout_Source_Consts::STATUS_ACCEPT,
        Cybersource_Cybersource_Model_VisaCheckout_Source_Consts::STATUS_DM_REVIEW
    );

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @access protected
     * @var string
     */
    protected $_formBlockType = 'cybersourcevisacheckout/form_visacheckout';

    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseForMultishipping  = false;
    protected $_canUseInternal          = false;

    /**
     * @param mixed $data
     * @return $this|Mage_Payment_Model_Info
     * @throws Mage_Core_Exception
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        if ($vcOrderId = $data->getCybersourceVcOrderId()) {
            $this->getInfoInstance()->setAdditionalInformation('vcOrderId', $vcOrderId);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function validate()
    {
        parent::validate();

        if (! $this->getInfoInstance()->getAdditionalInformation('vcOrderId')){
			throw new Exception('VC order id is undefined.');
		}

        return $this;
    }

    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        try {
            $result = $this->getApi()->requestAuthorization($payment);

            if (in_array($result->reasonCode, $this->successCodeList)) {

                $payment->setAdditionalInformation('authTransactionID', $result->requestID);
                $payment->setAdditionalInformation('authRequestID', $result->requestID);
                $payment->setAdditionalInformation('authRequestToken', $result->requestToken);

                $payment->setLastTransId($result->requestID);
                $payment->setTransactionId($result->requestID);
                $payment->setIsTransactionClosed(0);

                if ($result->reasonCode == Cybersource_Cybersource_Model_VisaCheckout_Source_Consts::STATUS_DM_REVIEW) {
                    $payment->setIsTransactionPending(1);
                    $payment->setIsFraudDetected(1);
                }

                return $this;
            }

            throw new Exception('Unable to perform authorization.');
        } catch (Exception $e) {
            Mage::helper('cybersourcevisacheckout')->log('Auth: ' . $e->getMessage(), true);
            throw new Exception('Gateway error: ' . $e->getMessage());
        }
    }

    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        try {
            if ($payment->getParentTransactionId()) {
                $order = $payment->getOrder();
                $storeId = $order->getStore()->getId();

                $appEmulation = Mage::getSingleton('core/app_emulation');
                $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

                try {
                    $result = Mage::getModel('cybersourcevisacheckout/soapapi_visacheckout')->requestCapture($payment, $amount);
                } catch (Exception $e) {
                    $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                    throw $e;
                }

                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            } else {
                $result = Mage::getModel('cybersourcevisacheckout/soapapi_visacheckout')->requestSale($payment, $amount);
            }

            if (in_array($result->reasonCode, $this->successCodeList)) {

                $payment->setAdditionalInformation('captureTransactionID', $result->requestID);
                $payment->setAdditionalInformation('captureRequestID', $result->requestID);
                $payment->setAdditionalInformation('captureRequestToken', $result->requestToken);

                $payment->setLastTransId($result->requestID);
                $payment->setTransactionId($result->requestID);
                $payment->setIsTransactionClosed(0);

                if ($result->reasonCode == Cybersource_Cybersource_Model_VisaCheckout_Source_Consts::STATUS_DM_REVIEW) {
                    $payment->setIsTransactionPending(1);
                    $payment->setIsFraudDetected(1);
                }

                return $this;
            }

            throw new Exception('Unable to perform capture.');
        } catch (Exception $e) {
            Mage::helper('cybersourcevisacheckout')->log('Capture: ' . $e->getMessage(), true);
            throw new Exception('Gateway error: ' . $e->getMessage());
        }
    }

    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Exception
     */
    public function refund(Varien_Object $payment, $amount)
    {
        try {
            $order = $payment->getOrder();
            $storeId = $order->getStore()->getId();

            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

            try {
                $result = Mage::getModel('cybersourcevisacheckout/soapapi_visacheckout')->requestRefund($payment, $amount);
            } catch (Exception $e) {
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                throw $e;
            }

            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

            if ($result->reasonCode == Cybersource_Cybersource_Model_VisaCheckout_Source_Consts::STATUS_ACCEPT) {

                $payment->setAdditionalInformation('refundRequestID', $result->requestID);
                $payment->setAdditionalInformation('refundTransactionID', $result->requestID);

                $closeParent = $order->canCreditmemo() ? 0 : 1;

                $payment->setTransactionId($result->requestID);
                $payment->setIsTransactionClosed(1);
                $payment->setShouldCloseParentTransaction($closeParent);

                return $this;
            }

            throw new Exception('Unable to perform refund.');
        } catch (Exception $e) {
            Mage::helper('cybersourcevisacheckout')->log('Refund: ' . $e->getMessage(), true);
            throw new Exception('Gateway error: ' . $e->getMessage());
        }
    }

    /**
     * @param Varien_Object $payment
     * @return $this
     * @throws Exception
     */
    public function void(Varien_Object $payment)
    {
        try {
            $order = $payment->getOrder();
            $storeId = $order->getStore()->getId();

            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

            try {
                $result = Mage::getModel('cybersourcevisacheckout/soapapi_visacheckout')->requestVoid($payment);
            } catch (Exception $e) {
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                throw $e;
            }

            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

            if ($result->reasonCode == Cybersource_Cybersource_Model_VisaCheckout_Source_Consts::STATUS_ACCEPT) {

                $payment->setAdditionalInformation('reversalRequestID', $result->requestID);
                $payment->setAdditionalInformation('reversalRequestToken', $result->requestToken);

                $payment->setTransactionId($result->requestID);
                $payment->setIsTransactionClosed(1);
                $payment->setShouldCloseParentTransaction(1);

                return $this;
            }

            throw new Exception('Unable to perform void.');
        } catch (Exception $e) {
            Mage::helper('cybersourcevisacheckout')->log('Void: ' . $e->getMessage(), true);
            throw new Exception('Gateway error: ' . $e->getMessage());
        }
    }

    /**
     * @param Varien_Object $payment
     * @return Cybersource_Cybersource_Model_VisaCheckout_Pay
     * @throws Exception
     */
    public function cancel(Varien_Object $payment)
    {
        return $this->void($payment);
    }

    /**
     * @return Cybersource_Cybersource_Model_VisaCheckout_Soapapi_Visacheckout
     */
    private function getApi()
    {
        return Mage::getModel('cybersourcevisacheckout/soapapi_visacheckout');
    }
}
