<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_BtController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $quote = $this->getOnepage()->getQuote();
        $payment = $quote->getPayment();

        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }

        try {
            $quote->reserveOrderId()->save();

            $response = Mage::getModel('cybersourcebanktransfer/soapapi_banktransfer')->requestSale();

            $payment->setAdditionalInformation('merchantReferenceCode', $response->merchantReferenceCode);
            $payment->setAdditionalInformation('saleRequestID', $response->requestID);
            $payment->setAdditionalInformation('saleRequestToken', $response->requestToken);

            $payment->save();

            if ($redirectUrl = $response->apSaleReply->merchantURL) {
                $this->_redirectUrl($redirectUrl);
                return;
            }

            throw new Exception(Mage::helper('cybersourcebanktransfer')->__('Unable to perform bank transfer sale. Please try again or contact us.'));
        } catch (Exception $e) {
            $this->getOnepage()->getCheckout()->addError($this->__('Unable to perform bank transfer sale.'));
            Mage::helper('cybersourcebanktransfer')->log($e->getMessage(), true);

            $this->_redirect('checkout/cart');
        }
    }

    public function returnAction()
    {
        try {
            $this->getOnepage()->getQuote()->collectTotals();
            $this->getOnepage()->saveOrder();

            $this->getOnepage()->getQuote()->setIsActive(0)->save();
            $this->_redirect('checkout/onepage/success');
        } catch (Exception $e) {
            $this->getOnepage()->getCheckout()->addError($e->getMessage());
            Mage::helper('cybersourcebanktransfer')->log($e->getMessage(), true);

            $this->_redirect('checkout/cart');
        }
    }

    public function cancelAction()
    {
        $this->getOnepage()->getCheckout()->addSuccess('Payment canceled.');
        $this->_redirect('checkout/cart');;
    }

    public function failureAction()
    {
        $this->getOnepage()->getCheckout()->addError('Payment failed.');
        $this->_redirect('checkout/cart');;
    }

    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    private function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
}
