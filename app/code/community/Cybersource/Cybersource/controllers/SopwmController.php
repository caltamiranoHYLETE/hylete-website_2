<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_SopwmController extends Mage_Core_Controller_Front_Action
{
    /**
     * Cybersource request object
     * @var array
     */
    private $_cyberResponse = null;

    /**
     * used to hold orders based on the status of the payment
     * @var bool
     */
    private $_holdOrder = false;

    /**
     * 
     * Ajax action method for generating Sign key and other fields
     * 
     */
    public function loadSignedFieldsAction()
    {
        $result = array(
            'isValid' => false,
            'message' => Mage::helper('cybersourcesop')->__('Something went wrong. Try again later.')
        );

        if (!Mage::app()->getRequest()->isPost() || !$this->_validateFormKey() || $this->_expireAjax()) {
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($result));
            return;
        }

        $tokenize = $this->getRequest()->getPost('tokenize', false);
        $paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
        $requestBuilder = Mage::getModel('cybersourcesop/sopwm_requestBuilder');

        // this flag represents if customer wants to save token
        $this->getOnepage()->getCheckout()->setTokenizeFlag($tokenize);

        try {
            if ($paymentMethod == Cybersource_Cybersource_Model_SOPWebMobile_Payment_Cc::CODE) {

                $formFields = Mage::helper('cybersourcesop')->useSoapForTransactions()
                    ? $requestBuilder->getCreateTokenFields()
                    : $requestBuilder->getCcFields();

                $formFields['transaction_type'] = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCyberTransactionType($tokenize);
            } else {
                $formFields = $requestBuilder->getEcheckFields();
                $formFields['transaction_type'] = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CS_ACTION_CAPTURE;
            }

            $formFields['signature'] = Mage::helper('cybersourcesop/security')->sign($formFields, $this->getSecretKey());

            $result['formFields'] = $formFields;
            $result['isValid'] = true;
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            Mage::helper('cybersourcesop')->log('failed to build form fields: ' . $e->getMessage(), true);
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
	}

    /**
     * Handle the response from Cybersource
     *
     */
    public function receiptAction()
    {
        $this->_cyberResponse = $this->getRequest()->getPost();

        Mage::helper('cybersourcesop')->log($this->_cyberResponse);

        $sopWmStatus = $this->_cyberResponse['reason_code'];

        try {
            if (! Mage::helper('cybersourcesop/security')->validateResponse($this->getSecretKey(), $this->_cyberResponse)) {
                throw new Exception('CyberSource signature is invalid.');
            }

            if (! in_array($sopWmStatus, Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSuccessCodes())) {
                Mage::helper('cybersourcesop')->log('CyberSource error code: ' . $sopWmStatus, true);
                throw new Exception('Unable to complete your payment.');
            }

            if ($this->_cyberResponse['req_reference_number'] != $this->getOnepage()->getQuote()->getReservedOrderId()) {
                Mage::helper('cybersourcesop')->log('Reference #s are not equal. Aborting.', true);
                throw new Exception('Unable to complete your payment.');
            }

            // legacy SOP|WM way processing, payment already occured
            if (!$this->isCardTransaction() || !Mage::helper('cybersourcesop')->useSoapForTransactions()) {

                Mage::register('cyber_payment_occurred', true);

                $orderAmount = $this->useWebsiteCurrency() ?
                    $this->getOnepage()->getQuote()->getGrandTotal()
                    : $this->getOnepage()->getQuote()->getBaseGrandTotal();

                $this->_cyberResponse['is_fraud_detected'] = $orderAmount != $this->_cyberResponse['req_amount'];
            }

            $this->verifyAvs();
            $this->verifyCvn();

            $token = $this->processToken();

            $this->getOnepage()->getCheckout()->setSkipTokenValidation(1);
            $this->getOnepage()->getCheckout()->setCsToken($token);

            // proceed to PA if it's enabled and legacy mode is off
            // otherwise transaction is already completed
            if ($this->isCardTransaction()
                && Mage::helper('cybersourcesop')->isPaEnabled()
                && Mage::helper('cybersourcesop')->useSoapForTransactions()
            ) {
                // forward to cardinal setup page with obtained payment token
                $this->getRequest()->setPost(array('payment' => array('cs_token' => $token)));
                $this->_forward('initPayerAuth');

                return $this;
            }

            Mage::register('cyber_response', $this->_cyberResponse);

            $this->getOnepage()->getQuote()->collectTotals();
            $this->getOnepage()->saveOrder();

            // clean session
            $this->getOnepage()->getCheckout()->unsCsToken();
            $this->getOnepage()->getCheckout()->unsSkipTokenValidation();

            $order = $this->getOnepage()->getCheckout()->getLastRealOrder();

            if (! $order->getId()) {
                throw new Exception('Failed to retrieve last real order');
            }
        } catch (Exception $e) {
            $this->_errorAction($e);
            return $this;
        }

        if ($this->_holdOrder) {
            $this->holdOrder($order);
        }

        if ($sopWmStatus == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_DM_REVIEW) {
            $reviewMessage = Mage::helper('cybersource_core')->getDmReviewMessage()
                ? Mage::helper('cybersource_core')->getDmReviewMessage()
                : "Your order is currently under review.";

            $this->getOnepage()->getCheckout()->addSuccess($reviewMessage);
        }

        $this->getOnepage()->getQuote()->setIsActive(0)->save();
        $this->_redirect('checkout/onepage/success');

        return $this;
    }

    // render page with Cardinal SongbirdJS
    public function initPayerAuthAction()
    {
        try {
            if (! Mage::helper('cybersourcesop')->isPaEnabled()) {
                throw new Exception('PA is not required.');
            }

            $paymentData = $this->getRequest()->getPost('payment');
            if (! isset($paymentData['cs_token'])) {
                throw new Exception('Token was not provided.');
            }

            $token = $paymentData['cs_token'];
            $tokenCvv = isset($paymentData['cs_token_cvv']) ? $paymentData['cs_token_cvv'] : false;

            $referenceId = uniqid('ref_');

            // saving necessary data to session
            $this->getOnepage()->getCheckout()->setCsToken($token);
            $this->getOnepage()->getCheckout()->setCsTokenCvv($tokenCvv);
            $this->getOnepage()->getCheckout()->setPaReferenceId($referenceId);
            $this->getOnepage()->getCheckout()->setPaInitialized(1);

            $jwt = Mage::getModel('cybersourcesop/jwt_manager')->generate(
                $referenceId,
                $this->getOnepage()->getQuote(),
                $this->retrieveCardBin($token)
            );

            $this->loadLayout();

            /** @var $songBirdJsBlock \Cybersource_Cybersource_Block_SOPWebMobile_SongbirdJs */
            $songBirdJsBlock = $this->getLayout()->getBlock('cybersource_songbird');
            $songBirdJsBlock->setJwt($jwt);

            $this->renderLayout();

        } catch (Exception $e) {
            $this->getOnepage()->getCheckout()->addError($e->getMessage());
            $this->_redirect('checkout/cart');
        }

        return $this;
    }

    public function payWithPayerAuthAction()
    {
        $result = array('success' => false, 'message' => 'PA failed.');

        try {
            if (! $this->getOnepage()->getCheckout()->getPaInitialized()) {
                throw new Exception('PA is not initialized.');
            }

            $paStep = $this->getRequest()->getPost('paStep');

            // this data will be used to build PA service
            $paData = array('paStep' => $paStep);

            // preparing PA data depending on current PA step
            switch ($paStep) {
                case 'pa_enroll': // request PA enrollment service
                    $paData['paReferenceId'] = $this->getOnepage()->getCheckout()->getPaReferenceId();
                    break;

                case 'pa_validate': // request PA validate service
                    $jwtManager = Mage::getModel('cybersourcesop/jwt_manager');

                    $inputJwt = $this->getRequest()->getPost('jwt');
                    $parsedInputJwt = $jwtManager->parse($inputJwt);

                    if (! $jwtManager->validate($parsedInputJwt)) {
                        throw new Exception('JWT validation failed.');
                    }

                    $payload = $parsedInputJwt->getClaim('Payload');
                    $paData['paAuthTransactionId'] = $payload->Payment->ProcessorTransactionId;
                    break;

                default:
                    throw new Exception('Unknown PA step: ' . $paStep);
            }

            Mage::register('pa_data', $paData);

            $this->getOnepage()->getQuote()->collectTotals();
            $this->getOnepage()->saveOrder();

            $order = $this->getOnepage()->getCheckout()->getLastRealOrder();

            if (! $order->getId()) {
                throw new Exception('Failed to retrieve last real order');
            }

            $this->getOnepage()->getQuote()->setIsActive(0)->save();

            // clean session
            $this->getOnepage()->getCheckout()->unsPaReferenceId();
            $this->getOnepage()->getCheckout()->unsPaInitialized();
            $this->getOnepage()->getCheckout()->unsCsToken();
            $this->getOnepage()->getCheckout()->unsCsTokenCvv();
            $this->getOnepage()->getCheckout()->unsSkipTokenValidation();

            $result['success'] = true;
            $result['redirect'] = Mage::getUrl('checkout/onepage/success');
        } catch (Cybersource_Cybersource_Model_SOPWebMobile_PaEnrolledException $e) {
            $result['success'] = true;
            $result['data'] = $e->getDetails();
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            $result['redirect'] = Mage::getUrl('checkout/cart');
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }

    /**
     * Retrieves the key
     * @return mixed
     */
    private function getSecretKey()
    {
        $sysConfig = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig();
        if (Mage::helper('cybersourcesop')->isMobile()) {
            $secretKey = $sysConfig['mobile_merchant_secret_key'];
        } else {
            $secretKey = $sysConfig['secret_key'];
        }
        return $secretKey;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    private function processToken()
    {
        if (! $this->isCardTransaction()) {
            return null;
        }

        if (! isset($this->_cyberResponse['payment_token'])) {

            if (Mage::helper('cybersourcesop')->useSoapForTransactions()) {
                throw new Exception('token is undefined. Aborting.');
            }

            return null;
        }

        $token = $this->_cyberResponse['payment_token'];

        if (! $this->getOnepage()->getCheckout()->getTokenizeFlag()) {
            return $token;
        }

        if (! $customerId = $this->getCustomer()->getId()) {
            return $token;
        }

        $tokenModel = Mage::getModel('cybersourcesop/token')->load($token,'token_id');
        if (! $tokenModel->getId()) {
            $tokenModel->setTokenId($token)
                ->setCustomerId($customerId)
                ->setCcNumber($this->_cyberResponse['req_card_number'])
                ->setCcExpiration($this->_cyberResponse['req_card_expiry_date'])
                ->setCcType($this->_cyberResponse['req_card_type'])
                ->setMerchantRef($this->_cyberResponse['req_reference_number'])
                ->save();
        }

        return $token;
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function verifyAvs()
    {
        if (! $this->isCardTransaction()) {
            return $this;
        }

        $action = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('forceavs');

        $successCodes = explode(',',str_replace(' ','', Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('forceavs_codes')));
        $successCodes = count($successCodes) ? $successCodes : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getAvsSuccessVals();

        if (! isset($this->_cyberResponse['auth_avs_code'])) {
            return $this;
        }

        if (in_array($this->_cyberResponse['auth_avs_code'], $successCodes)) {
            return $this;
        }

        if ($action == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_DECLINE) {
            throw new Exception(Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getAVSErrorCode($this->_cyberResponse['auth_avs_code']));
        }

        if ($action == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_ACCEPT_HOLD) {
            $this->_holdOrder = true;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function verifyCvn()
    {
        if (! $this->isCardTransaction()) {
            return $this;
        }

        $action = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('forcecvn');

        $successCodes = explode(',',str_replace(' ','', Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('forcecvn_codes')));
        $successCodes = count($successCodes) ? $successCodes : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCvnSuccessVals();

        if (! isset($this->_cyberResponse['auth_cv_result'])) {
            return $this;
        }

        if (in_array($this->_cyberResponse['auth_cv_result'], $successCodes)) {
            return $this;
        }

        if ($action == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_DECLINE) {
            throw new Exception(Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCVNErrorCode($this->_cyberResponse['auth_cv_result']));
        }

        if ($action == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CONFIG_CARDCHECK_ACCEPT_HOLD) {
            $this->_holdOrder = true;
        }

        return $this;
    }

    /**
     * Cybersource General Error Action
     *
     * Called when a request back from cybersource has the error decision
     * Tries to retrieve the inital quote, cancels the order and redirects back to checkout
     * Sets a more useful error message to the customer based on the cybersource response
     *
     * @param Exception|null $e
     * @return Cybersource_Cybersource_SopwmController
     */
    private function _errorAction(Exception $e = null)
    {
        $this->revertCyberSourceTransaction();

        $session = $this->getOnepage()->getCheckout();

        if (isset($this->_cyberResponse['req_reference_number'])) {
            $orderid = $this->_cyberResponse['req_reference_number'];
        } else {
            $orderid = $session->getLastRealOrderId();
        }

        if ($orderid) {
            //attemptes to cancel the order and restore the quote so customer can try again
            $this->_cancelOrderAndRestoreQuote($orderid);
        }

        if (! $e) {
            $errorCode = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getErrorCode($this->_cyberResponse['reason_code']);
            $message = Mage::helper('cybersourcesop')->__("There was an error submitting your payment. %s", $errorCode);
        } else {
            $message = $e->getMessage();
        }

        Mage::helper('cybersourcesop')->log($message, true);

        $session->addError($message);
        $session->unsLastRealOrderId();

        $this->_redirectUrl(Mage::getUrl('checkout/cart'));

        return $this;
    }

    /**
     * Cancel the order id and restore the quote to the users session
     *
     * @param mixed $orderidin
     * @return Cybersource_Cybersource_SopwmController
     */

    private function _cancelOrderAndRestoreQuote($orderidin)
    {
        $session = $this->getOnepage()->getCheckout();

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderidin);

        if ($order->getId()) {
            try {
                //Cancel order
                if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
                    $order->registerCancellation(Mage::helper('cybersourcesop')->__('Unable to complete payment.'))->save();
                }

                $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                //Return quote
                if ($quote->getId()) {
                    $quote->setIsActive(1)->unsReservedOrderId()->save();
                    $session->replaceQuote($quote);
                }
                Mage::helper('cybersourcesop')->log('Retrieved quote succesfully from order: ' . $orderidin);
            } catch (Exception $e) {
                //set the error message
                Mage::helper('cybersourcesop')->log("Error restoring quote:" . $e->getMessage());
            }
        } else {
            //we have no information available so just log and display error
            Mage::helper('cybersourcesop')->log("Error restoring quote: last order id:". $orderidin);
        }

        return $this;
    }

    private function holdOrder($order)
    {
        if ($order->getId() && $order->canHold()) {
            $order->hold()->save();
        }
    }

    /**
     * Token action method
     */
    public function tokenAction()
    {
        if (! Mage::getSingleton('customer/session')->authenticate($this)) {
            return $this;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Save default token action
     */
    public function saveDefaultTokenAction()
    {
        $session = Mage::getSingleton('core/session');

        if (! $checkboxes = $this->getRequest()->getParam('checkbox')) {
            $session->addError(Mage::helper('cybersourcesop')->__("Select the token to update."));
            $this->_redirect('cybersource/sopwm/token');

            return $this;
        }

        foreach ($checkboxes as $id => $state) {

            if ($state != 'on') {
                continue;
            }

            try {
                /** @var $token Cybersource_Cybersource_Model_SOPWebMobile_Token */
                $token = Mage::getModel('cybersourcesop/token')->load($id);
                if (!$token->getId() || $token->getCustomerId() != $this->getCustomer()->getId()) {
                    throw new Exception(Mage::helper('cybersourcesop')->__('You are not allowed to take this action.'));
                }

                $token->setAsDefault();

            } catch (Exception $e) {
                $session->addError(Mage::helper('cybersourcesop')->__("An error occurred while updating your default credit card token."));
                Mage::helper('cybersourcesop')->log('Token Save Default Error: ' . $e->getMessage());
                break;
            }
        }

        $session->addSuccess(Mage::helper('cybersourcesop')->__("Default credit card token updated successfully."));
        $this->_redirect('cybersource/sopwm/token');
    }

    /**
     * Deletes token action
     */
    public function deleteAction()
    {
        $session = Mage::getSingleton('core/session');
        $params = $this->_request->getParams();

        //Get Token.
        $token = Mage::getModel('cybersourcesop/token')->getTokenValue($params['token_id']);
        if (! Mage::helper('cybersourcesop')->isValidToken($token->getTokenId())) {
            $session->addError(Mage::helper('cybersourcesop')->__('You are not allowed to take this action.'));
            $this->_redirect('cybersource/sopwm/token');

            return $this;
        }

        $tokenId = $token->getTokenId();
        $merchantRef = $token->getMerchantRef();
        $result = Mage::getModel('cybersourcesop/token')->createDeleteRequest($tokenId, $merchantRef);

        if ($result) {
            $session->addSuccess(Mage::helper('cybersourcesop')->__("Saved Card sucessfully deleted."));
        } else {
            $session->addError(Mage::helper('cybersourcesop')->__("There was an error deleting your Saved Card."));
        }
        $this->_redirect('cybersource/sopwm/token');
    }

    private function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    private function revertCyberSourceTransaction()
    {
        if (! Mage::registry('cyber_payment_occurred')) {
            return $this;
        }

        if (! $cyberResponse = Mage::registry('cyber_response')) {
            return $this;
        }

        $fallbackModel = Mage::getModel('cybersourcesop/soapapi_fallback');

        try {
            $amount = $cyberResponse['req_amount'];
            $transactionId = $cyberResponse['transaction_id'];
            $isCardTransaction = $cyberResponse['req_payment_method'] == 'card';
            $isSale = substr($cyberResponse['req_transaction_type'], 0, 4) == 'sale';

            if ($isCardTransaction) {
                if ($isSale) {
                    $fallbackModel->processVoid($transactionId);
                    Mage::helper('cybersourcesop')->log("Voided transaction #" . $transactionId, true);
                }

                $fallbackModel->processReversal($transactionId, $amount);
                Mage::helper('cybersourcesop')->log("Reversed transaction #" . $transactionId, true);
            } else {
                $fallbackModel->processVoid($transactionId);
                Mage::helper('cybersourcesop')->log("Voided transaction #" . $transactionId, true);
            }
        } catch (\Exception $e) {
            Mage::helper('cybersourcesop')->log($e->getMessage() . " (transaction #" . $transactionId . ")", true);
        }

    }

    private function retrieveCardBin($token)
    {
        $cardBin = '';
        try {
            $tokenDetails = Mage::getModel('cybersourcesop/soapapi_tokenDetails')->process($token);
            $cardBin = substr($tokenDetails->cardAccountNumber, 0, 6);
        } catch (Exception $e) {
            // no action
        }
        return $cardBin;
    }

    private function useWebsiteCurrency()
    {
        $defaultCurrencyType = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('default_currency');
        return $defaultCurrencyType == Cybersource_Cybersource_Model_SOPWebMobile_Source_Currency::DEFAULT_CURRENCY;
    }

    private function isCardTransaction()
    {
        return $this->_cyberResponse['req_payment_method'] == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::PAY_METHOD_CARD;
    }

    /**
     * Validate ajax request
     *
     * @return bool
     */
    private function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    private function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
}
