<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Abstract extends Mage_Core_Model_Abstract
{
    protected $successCodeList = array(
        Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_ACCEPT,
        Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_DM_REVIEW,
        Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_PA_ENROLLED
    );

    /**
     * @param null $orderId
     * @return stdClass
     */
    protected function iniRequest($orderId = null)
    {
        $request = new stdClass();

        $request->merchantID = Mage::helper('cybersource_core')->getMerchantId();
        $request->merchantReferenceCode = $orderId ? $orderId : Mage::helper('core')->uniqHash();
        $request->clientLibrary = "PHP";
        $request->clientLibraryVersion = phpversion();

        return $request;
    }

    /**
     * @return Cybersource_Cybersource_Model_Core_Soap_Client
     */
    protected function getSoapClient()
    {
        $wsdlPath = Mage::helper('cybersource_core')->getWsdlUrl();
        $client = new Cybersource_Cybersource_Model_Core_Soap_Client($wsdlPath);

        $client->setContext(self::class);
        $client->setLogFilename(Cybersource_Cybersource_Helper_SOPWebMobile_Data::LOGFILE);
        $client->setPreventLogFlag(!Mage::helper('cybersourcesop')->isDebugMode());

        return $client;
    }

    /**
     * @param Mage_Sales_Model_Order_Address $billing
     * @param String $email
     * @return stdClass
     */
    protected function buildBillingAddress($billing, $email)
    {
        if (! $email) {
            $email = $billing->getEmail();
        }

        $billTo = new stdClass();

        $billTo->company = substr($billing->getCompany(), 0, 40);
        $billTo->firstName = $billing->getFirstname();
        $billTo->lastName = $billing->getLastname();
        $billTo->street1 = $billing->getStreet(1);
        $billTo->street2 = $billing->getStreet(2);
        $billTo->city = $billing->getCity();
        $billTo->state = $billing->getRegion();
        $billTo->postalCode = $billing->getPostcode();
        $billTo->country = $billing->getCountry();
        $billTo->phoneNumber = $this->cleanPhoneNum($billing->getTelephone());
        $billTo->email = ($email ? $email : Mage::getStoreConfig('trans_email/ident_general/email'));
        $billTo->ipAddress = Mage::helper('core/http')->getRemoteAddr();

        return $billTo;
    }

    /**
     * @param Mage_Sales_Model_Order_Address $shipping
     * @return bool|stdClass
     */
    protected function buildShippingAddress($shipping)
    {
        if (! $shipping) {
            return false;
        }

        $shipTo = new stdClass();

        $shipTo->company = substr($shipping->getCompany(), 0, 40);
        $shipTo->firstName = $shipping->getFirstname();
        $shipTo->lastName = $shipping->getLastname();
        $shipTo->street1 = $shipping->getStreet(1);
        $shipTo->street2 = $shipping->getStreet(2);
        $shipTo->city = $shipping->getCity();
        $shipTo->state = $shipping->getRegion();
        $shipTo->postalCode = $shipping->getPostcode();
        $shipTo->country = $shipping->getCountry();
        $shipTo->phoneNumber = $this->cleanPhoneNum($shipping->getTelephone());

        return $shipTo;
    }

    protected function buildLineItems($items)
    {
        $result = array();

        /** @var $item Mage_Sales_Model_Order_Item */
        foreach ($items as $i => $item) {
            $lineItem = new stdClass();
            $lineItem->id = $i;
            $lineItem->productName = $item->getName();
            $lineItem->productSKU = $item->getSku();
            $lineItem->quantity = $item->getQty() ? $item->getQty() : $item->getQtyOrdered();
            $lineItem->productCode = 'default';

            if ($this->useWebsiteCurrency()) {
                $lineItem->unitPrice = $this->formatNumber($item->getPriceInclTax() - $item->getTaxAmount());
                $lineItem->taxAmount = $this->formatNumber($item->getTaxAmount());
            } else {
                $lineItem->unitPrice = $this->formatNumber($item->getBasePrice());
                $lineItem->taxAmount = $this->formatNumber($item->getBaseTaxAmount());
            }

            $result[] = $lineItem;
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $response
     * @return $this
     * @throws Cybersource_Cybersource_Model_SOPWebMobile_PaEnrolledException
     */
    protected function processSoapResponse($payment, $response)
    {
        if ($response->reasonCode == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_PA_ENROLLED) {
            throw new Cybersource_Cybersource_Model_SOPWebMobile_PaEnrolledException(
                'Payer Authentication is required.',
                array(
                    'cca' => array(
                        'AcsUrl' => $response->payerAuthEnrollReply->acsURL,
                        'Payload' => $response->payerAuthEnrollReply->paReq
                    ),
                    'order' => array_replace_recursive(
                        array(
                            'OrderDetails' => array(
                                'TransactionId' => $response->payerAuthEnrollReply->authenticationTransactionID
                            )
                        ),
                        Mage::getModel('cybersourcesop/jwt_payload_builder')->build($payment->getOrder())
                    )
                )
            );
        }

        $payment->setLastTransId($response->requestID);
        $payment->setCcTransId($response->requestID);
        $payment->setTransactionId($response->requestID);
        $payment->setIsTransactionClosed(0);

        if (isset($response->payerAuthEnrollReply)) {
            $paEnrollReply = (array) $response->payerAuthEnrollReply;
            foreach ($paEnrollReply as $k => $v) {
                $payment->setAdditionalInformation($k, $v);
            }
        }

        if (isset($response->payerAuthValidateReply)) {
            $paValidateReply = (array) $response->payerAuthValidateReply;
            foreach ($paValidateReply as $k => $v) {
                $payment->setAdditionalInformation($k, $v);
            }
        }

        if ($response->reasonCode == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_DM_REVIEW) {
            $payment->setIsTransactionPending(1);
            $payment->setIsFraudDetected(1);
        }

        if (!isset($response->ccAuthReply)) {
            return $this;
        }

        if (isset($response->ccAuthReply->avsCode)) {
            $payment->setCcAvsStatus($response->ccAuthReply->avsCode);
            $payment->setAdditionalInformation('cc_avs_status', $response->ccAuthReply->avsCode);
        }

        if (isset($response->ccAuthReply->cvCode)) {
            $payment->setCcCidStatus($response->ccAuthReply->cvCode);
            $payment->setAdditionalInformation('cc_cid_status', $response->ccAuthReply->cvCode);
        }

        return $this;
    }

    protected function buildTokenDetails($request, $token, $isMit = false)
    {
        $recurringSubscriptionInfo = new stdClass();
        $recurringSubscriptionInfo->subscriptionID = $token->tokenId;
        $request->recurringSubscriptionInfo = $recurringSubscriptionInfo;

        if ($cvv = $token->cvv) {
            $card = new \stdClass();
            $card->cvNumber = $cvv;
            $request->card = $card;
        }

        if (! $isMit) {
            return $this;
        }

        // adding required MIT fields
        $request->subsequentAuth = "true";
        $request->subsequentAuthStoredCredential = "true";

        if ($networkTransactionId = $token->model->getNetworkTransactionId()) {
            $request->subsequentAuthTransactionID = $networkTransactionId;
        } elseif ($networkTransactionId !== '0') { // zero value means that this is not first MIT with this token
            $request->subsequentAuthFirst = "true";
        }

        return $this;
    }

    public function buildPayerAuthService($request)
    {
        if (! Mage::helper('cybersourcesop')->isPaEnabled()) {
            return $this;
        }

        $paData = Mage::registry('pa_data');

        // force PA if it's enabled
        if (! $paData) {
            throw new Exception('PA data is empty.');
        }

        switch ($paData['paStep']) {
            case 'pa_enroll':
                $payerAuthEnrollService = new stdClass();
                $payerAuthEnrollService->run ="true";
                $payerAuthEnrollService->mobilePhone = $request->billTo->phoneNumber;
                $payerAuthEnrollService->referenceID = $paData['paReferenceId'];
                $payerAuthEnrollService->transactionMode = 'S';
                if ($httpAccept = Mage::app()->getRequest()->getHeader('accept')) {
                    $payerAuthEnrollService->httpAccept = $httpAccept;
                }
                if ($httpUserAgent = Mage::app()->getRequest()->getHeader('user-agent')) {
                    $payerAuthEnrollService->httpUserAgent = $httpUserAgent;
                }
                $request->payerAuthEnrollService = $payerAuthEnrollService;
                break;

            case 'pa_validate':
                $payerAuthValidateService = new stdClass();
                $payerAuthValidateService->run ="true";
                $payerAuthValidateService->authenticationTransactionID = $paData['paAuthTransactionId'];
                $request->payerAuthValidateService = $payerAuthValidateService;
                break;

            default:
                throw new Exception('Unknown paStep: ' . $paData['step']);
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function useWebsiteCurrency()
    {
        $defaultCurrencyType = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('default_currency');
        return $defaultCurrencyType == Cybersource_Cybersource_Model_SOPWebMobile_Source_Currency::DEFAULT_CURRENCY;
    }

    /**
     * @param string|int $num
     * @param int $pre
     * @return string
     */
    protected function formatNumber($num, $pre = 2)
    {
        return number_format($num, $pre, '.', '');
    }

    /**
     * @param string $phoneNumberIn
     * @return string|mixed
     */
    protected function cleanPhoneNum($phoneNumberIn)
    {
        return preg_replace("/[^0-9]/","", $phoneNumberIn);
    }
}
