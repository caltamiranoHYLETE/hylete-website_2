<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_PaypalController extends Mage_Core_Controller_Front_Action
{
    /**
     * @throws Exception
     */
    public function initAction()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = $this->getOnepage()->getCheckout();
        $quote = $session->getQuote();

        try {
            $quote->reserveOrderId()->save();

            //Set Paypal Service via request to CyberSource.
            $response = Mage::getModel('cybersourcepaypal/soapapi_paypal')->payPalEcSetService($quote);

            //Set PayPal EcSet Reply values to quote_payment.
            $payment = $quote->getPayment();

            $payment->setAdditionalInformation('merchantReferenceCode', $response->merchantReferenceCode);
            $payment->setAdditionalInformation('initRequestID', $response->requestID);
            $payment->setAdditionalInformation('initRequestToken', $response->requestToken);

            $payment->save();

            if ($token = $response->payPalEcSetReply->paypalToken) {
                $this->_redirectUrl($this->getPayPalRedirectUrl($token));
                return;
            }

            throw new Exception(Mage::helper('cybersourcepaypal')->__('Unable to start Express Checkout. Please try again or contact us.'));
        } catch (Exception $e) {
            $session->addError($this->__('Unable to start Express Checkout.'));
            Mage::helper('cybersourcepaypal')->log($e->getMessage(), true);

            $this->_redirect('checkout/cart');
        }
    }

    /**
     * @throws Exception
     */
    public function returnAction()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = $this->getOnepage()->getCheckout();
        $quote = $session->getQuote();

        try {
            if (! $token = $this->getRequest()->getParam('token')) {
                throw new Exception('Token is undefined.');
            }

            $response = Mage::getModel('cybersourcepaypal/soapapi_paypal')->payPalEcGetDetailsService($quote, $token);

            $quote->getPayment()->setAdditionalInformation('merchantReferenceCode', $response->merchantReferenceCode);
            $quote->getPayment()->setAdditionalInformation('requestID', $response->requestID);
            $quote->getPayment()->setAdditionalInformation('requestToken', $response->requestToken);
            $quote->getPayment()->setAdditionalInformation('paypalToken', $response->payPalEcGetDetailsReply->paypalToken);
            $quote->getPayment()->setAdditionalInformation('payerId', $response->payPalEcGetDetailsReply->payerId);
            $quote->getPayment()->setAdditionalInformation('payerEmail', $response->payPalEcGetDetailsReply->payer);
            $quote->getPayment()->setAdditionalInformation('correlationID', $response->payPalEcGetDetailsReply->correlationID);

            $quote->collectTotals();
            $this->getOnepage()->saveOrder();

            $this->getOnepage()->getQuote()->setIsActive(0)->save();
            $this->_redirect('checkout/onepage/success');
        } catch (Exception $e) {
            $session->addError($e->getMessage());
            Mage::helper('cybersourcepaypal')->log($e->getMessage(), true);

            $this->_redirect('checkout/cart');
        }
    }

    /**
     * @throws Exception
     */
    public function cancelAction()
    {
        $this->getOnepage()->getCheckout()->addSuccess('You canceled payment.');
        $this->_redirect('checkout/cart');;
    }

    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    private function getPayPalRedirectUrl($token)
    {
        $isTestMode = Mage::helper('cybersource_core')->getIsTestMode();
        return 'https://www.' . ($isTestMode ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $token;
    }
}
