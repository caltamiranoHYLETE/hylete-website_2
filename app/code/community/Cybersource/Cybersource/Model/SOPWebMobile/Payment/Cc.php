<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Payment_Cc extends Mage_Payment_Model_Method_Cc
{
    const CODE = 'cybersourcesop';

    /**
     * Payment method code
     * @access protected
     * @var string
     */
	protected $_code = self::CODE;
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
    protected $_canUseInternal = true;

    /**
     * Used to check if multi-shipping is enabled
     * @access protected
     * @var bool
     */
    protected $_canUseForMultishipping = false;

    /**
     * Used to authorize the payment
     * @access protected
     * @var bool
     */
	protected $_canAuthorize = true;

    /**
     * Used to capture the payment
     * @access protected
     * @var bool
     */
	protected $_canCapture = true;

    /**
     * Used during refund
     * @access protected
     * @var bool
     */
	protected $_canRefund = true;

    /**
     * Used to refund partial capture
     * @access protected
     * @var bool
     */
	protected $_canRefundInvoicePartial = true;

    /**
     * Used to void transaction
     * @access protected
     * @var bool
     */
	protected $_canVoid = true;

    /**
     * Used to invoice the order
     * @access protected
     * @var bool
     */
	protected $_canCancelInvoice = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = true;

	/**
	 * Redirect to the post page
	 *
	 * @return string|boolean
	 */
	public function getOrderPlaceRedirectUrl()
	{
	    return false;
	}

    /**
     * Validates the payment method/card
     * @return $this
     */
    public function validate()
	{
		return $this;
	}

    /**
     * Overridden for admin area SOAP calls
     *
     * @see Mage_Payment_Model_Method_Abstract::authorize()
     * @param Varien_Object $payment
     * @param $amount
     * @return $this
     * @throws Exception
     */
    public function authorize(Varien_Object $payment, $amount)
	{
		if ($this->isAdmin()) {
            if ($tokenObj = $this->extractPaymentToken(false)) {
                Mage::getModel('cybersourcesop/soapapi_auth')->process($payment, $tokenObj, true);
                return $this;
            }

            throw new Exception('Token was not provided.');
		}

        if ($tokenObj = $this->extractPaymentToken()) {
            $soapResponse = Mage::getModel('cybersourcesop/soapapi_auth')->process($payment, $tokenObj);

            Mage::register('cyber_payment_occurred', true);
            $this->emulateCyberSopResponse($soapResponse);

            return $this;
        }

        if (Mage::helper('cybersourcesop')->useSoapForTransactions()) {
            throw new Exception('Token was not extracted. Payment failed.');
        }

        // regular payment flow after CyberSource callback
        if (! $response = Mage::registry('cyber_response')) {
            throw new Exception('CyberSource response is empty.');
        }

        $this->processCyberSopResponse($payment, $response);
		return $this;
	}

    /**
     * Overridden for admin area SOAP calls
     *
     * @see Mage_Payment_Model_Method_Abstract::authorize()
     * @param Varien_Object $payment
     * @param $amount
     * @return $this
     * @throws Exception
     */
    public function capture(Varien_Object $payment, $amount)
	{
		if ($this->isAdmin()) {

		    // admin sale
            if (! $payment->getAdditionalInformation('authRequestID')) {
                if ($tokenObj = $this->extractPaymentToken(false)) {
                    Mage::getModel('cybersourcesop/soapapi_sale')->process($payment, $tokenObj, true);
                    return $this;
                }

                throw new Exception('Token was not provided.');
            }

            // admin auth capture
            $storeId = $payment->getOrder()->getStore()->getId();
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

            try {
                Mage::getModel('cybersourcesop/soapapi_capture')->process($payment, $amount);
            } catch (Exception $e) {
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                throw $e;
            }

            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
			return $this;
		}

		if ($tokenObj = $this->extractPaymentToken()) {
            $soapResponse = Mage::getModel('cybersourcesop/soapapi_sale')->process($payment, $tokenObj);

            Mage::register('cyber_payment_occurred', true);
            $this->emulateCyberSopResponse($soapResponse);

            return $this;
        }

		if (Mage::helper('cybersourcesop')->useSoapForTransactions()) {
		    throw new Exception('Token was not extracted. Payment failed.');
        }

		// regular payment flow after CyberSource callback
		if (! $response = Mage::registry('cyber_response')) {
		    throw new Exception('CyberSource response is empty.');
        }

        $this->processCyberSopResponse($payment, $response, true);
		return $this;
	}

    /**
     * Process the refunds
     * @param Varien_Object $payment
     * @param $amount
     * @return $this
     * @throws Exception
     */
    public function refund(Varien_Object $payment, $amount)
	{
        $storeId = $payment->getOrder()->getStore()->getId();
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            Mage::getModel('cybersourcesop/soapapi_refund')->process($payment, $amount);
        } catch (Exception $e) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $e;
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
	 	return $this;
	}

    /**
     * Void payment abstract method
     *
     * @param Varien_Object $payment
     *
     * @return Cybersource_Cybersource_Model_SOPWebMobile_Payment_Cc
     * @throws Exception
     */
	public function void(Varien_Object $payment)
    {
        $storeId = $payment->getOrder()->getStore()->getId();
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            Mage::getModel('cybersourcesop/soapapi_void')->process($payment);
        } catch (Exception $e) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $e;
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
		return $this;
	}

    public function canVoid(Varien_Object $payment)
    {
        $paymentInfo = $payment->getAdditionalInformation();
        return !isset($paymentInfo['captureTransactionID'])
            && !isset($paymentInfo['reversalRequestID']);
    }

    public function getConfigPaymentAction()
    {
        return Mage::helper('cybersourcesop')->getPaymentAction();
    }

    public function assignData($data)
    {
        parent::assignData($data);

        if ($token = $data->getCsToken()) {
            $this->getInfoInstance()->setAdditionalInformation('cs_token', $token);
        }

        if ($tokenCvv = $data->getCsTokenCvv()) {
            $this->getInfoInstance()->setAdditionalInformation('cs_token_cvv', $tokenCvv);
        }

        return $this;
    }

    /**
     * @param $payment
     * @param $response
     * @param bool $isSale
     * @return $this
     */
    private function processCyberSopResponse(&$payment, $response, $isSale = false)
    {
        $payment
            ->setIsTransactionClosed(0)
            ->setCcApproval(true)
            ->setLastTransId($response['transaction_id'])
            ->setCcTransId($response['transaction_id'])
            ->setTransactionId($response['transaction_id'])
            ->setCcLast4(Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::retrieveCardNum($response['req_card_number']))
            ->setCcType(Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCyberCCs($response['req_card_type']));

        if ($isSale) {
            $payment->setAdditionalInformation('captureTransactionID', $response['transaction_id']);
            $payment->setAdditionalInformation('captureRequestID', $response['transaction_id']);
            $payment->setAdditionalInformation('captureRequestToken', $response['request_token']);
        } else {
            $payment->setAdditionalInformation('authTransactionID', $response['transaction_id']);
            $payment->setAdditionalInformation('authRequestID', $response['transaction_id']);
            $payment->setAdditionalInformation('authRequestToken', $response['request_token']);
        }

        if ($response['reason_code'] == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_DM_REVIEW) {
            $payment->setIsTransactionPending(1);
            $payment->setIsFraudDetected(1);
        }

        if ($response['is_fraud_detected']) {
            $payment->setIsFraudDetected(1);
        }

        if (isset($response['payer_authentication_xid'])) {
            $payment->setAdditionalInformation('cybersourcesop_auth_xid', $response['payer_authentication_xid']);
        }

        if (isset($response['payer_authentication_proof_xml'])) {
            $payment->setAdditionalInformation('cybersourcesop_proof_xml', $response['payer_authentication_proof_xml']);
        }

        if (isset($response['payer_authentication_eci'])) {
            $payment->setAdditionalInformation('cybersourcesop_eci', $response['payer_authentication_eci']);
        }

        if (isset($response['payer_authentication_cavv'])) {
            $payment->setAdditionalInformation('cybersourcesop_cavv', $response['payer_authentication_cavv']);
        }

        if (isset($response['auth_avs_code'])) {
            $payment->setAdditionalInformation('cc_avs_status', $response['auth_avs_code']);
            $payment->setCcAvsStatus($response['auth_avs_code']);
        }

        if (isset($response['auth_cv_result'])) {
            $payment->setAdditionalInformation('cc_cid_status', $response['auth_cv_result']);
            $payment->setCcCidStatus($response['auth_cv_result']);
        }

        return $this;
    }

    private function emulateCyberSopResponse($soapResponse, $isSale = false)
    {
        $result = array();

        $result['req_reference_number'] = $soapResponse->merchantReferenceCode;
        $result['req_transaction_type'] = $isSale ? 'sale' : 'authorization';
        $result['req_payment_method'] = 'card';
        $result['req_amount'] = $soapResponse->ccAuthReply->amount;
        $result['request_token'] = $soapResponse->requestToken;
        $result['transaction_id'] = $soapResponse->requestID;
        $result['reason_code'] = $soapResponse->reasonCode;

        if ($initialSopResponse = Mage::registry('cyber_response')) {
            $result['req_card_number'] =  $initialSopResponse['req_card_number'];
            $result['req_card_type'] = $initialSopResponse['req_card_type'];
            $result['auth_avs_code'] =  $initialSopResponse['auth_avs_code'];
            $result['auth_cv_result'] = $initialSopResponse['auth_cv_result'];
        }

        Mage::unregister('cyber_response');
        Mage::register('cyber_response', $result);

        return $result;
    }

    private function extractPaymentToken($requireCvv = true)
    {
        $result = new stdClass();

        $tokenId = $this->getInfoInstance()->getAdditionalInformation('cs_token');
        $tokenCvv = $this->getInfoInstance()->getAdditionalInformation('cs_token_cvv');
        $skipTokenValidation = $this->getCheckoutSession()->getSkipTokenValidation();

        // trying to extract token + cvv from session instead
        if (!$this->isAdmin() && !$tokenId) {
            $tokenId = $this->getCheckoutSession()->getCsToken();
            $tokenCvv = $this->getCheckoutSession()->getCsTokenCvv();
        }

        // session will be cleaned later as we may be required to resubmit the payment in case of step up PA scenario
        $this->getInfoInstance()->unsAdditionalInformation('cs_token');
        $this->getInfoInstance()->unsAdditionalInformation('cs_token_cvv');

        if (!$tokenId) {
            return false;
        }

        // payment with OT Token
        if ($skipTokenValidation) {
            $result->tokenId = $tokenId;
            return $result;
        }

        if (!$tokenCvv && $requireCvv) {
            return false;
        }

        if (! Mage::helper('cybersourcesop')->isValidToken($tokenId)) {
            throw new Exception('You are not authorized to use this token.');
        }

        // token already passed validation
        $tokenModel = Mage::getModel('cybersourcesop/token')->load($tokenId,'token_id');

        $result->tokenId = $tokenId;
        $result->cvv = $tokenCvv;
        $result->model = $tokenModel;

        return $result;
    }

    private function isAdmin()
    {
        return Mage::app()->getStore()->isAdmin();
    }

    private function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }
}
