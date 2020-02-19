<?php

class Bestworlds_KlaviyoExtend_Model_Observer
{
    const COOKIE_NAME = 'bw_klaviyo_email';

    public function getCookieName()
    {
        return self::COOKIE_NAME;
    }

    public function getPrivateApiKey()
    {
        return Mage::getStoreConfig('klaviyoextend/basic/sc_custom_object_api_key');
    }

    public function getKlaviyoCartApi()
    {
        return Mage::getSingleton('klaviyoextend/customObject_Cart_KlaviyoCartApi', array('api_key' => $this->getPrivateApiKey())); //new Bestworlds_KlaviyoExtend_Model_CustomObject_Cart_KlaviyoCartApi($private_api_key);
    }

    public function checkoutCartSaveAfter(Varien_Event_Observer $observer)
    {
        $cookie = Mage::getModel('core/cookie')->get(self::COOKIE_NAME);
        $cookie = Mage::helper('klaviyoextend')->decryptMe($cookie);
        if ($cookie) {
            $cart = $observer->getEvent()->getCart();
            $quote = $cart->getQuote();
            if ($quote->getId() && !$quote->getCustomerEmail()) {
                $quote->setData('customer_email', $cookie);
                $quote->save();
                //fire klaviyo's flow
                $klaviyoModel = Mage::getModel('klaviyoextend/klaviyo');
                $klaviyoModel->sendKlaviyoTrack($quote);
            }
        }
    }

    protected function updateKlaviyoCart($quote)
    {
        if (!Mage::getStoreConfigFlag('klaviyoextend/basic/enable') && !Mage::getStoreConfigFlag('klaviyoextend/basic/send_cart_custom_object')) {
            return $this;
        }
        $this->getKlaviyoCartApi()->cartUpdate($quote);
    }

    protected function deleteKlaviyoCart($quote)
    {
        if (!Mage::getStoreConfigFlag('klaviyoextend/basic/enable') && !Mage::getStoreConfigFlag('klaviyoextend/basic/send_cart_custom_object')) {
            return $this;
        }
        $this->getKlaviyoCartApi()->cartDelete($quote);
    }

    public function checkoutCartAddProductComplete(Varien_Event_Observer $observer)
    {
        $cart = Mage::getSingleton('checkout/cart');
        $this->updateKlaviyoCart($cart->getQuote());
    }

    public function checkoutCartUpdateItemsAfter(Varien_Event_Observer $observer)
    {
        $cart = $observer->getEvent()->getCart();
        $this->updateKlaviyoCart($cart->getQuote());
    }

    public function postdispatchCheckoutCartUpdatePost(Varien_Event_Observer $observer)
    {
        $controllerAction = $observer->getEvent()->getControllerAction();
        $updateAction = (string)$controllerAction->getRequest()->getParam('update_cart_action');
        if ($updateAction == 'empty_cart') {
            $cart = Mage::getSingleton('checkout/cart');
            $this->updateKlaviyoCart($cart->getQuote());
        }
    }

    public function postdispatchAddtoCartAjaxIndexRemove(Varien_Event_Observer $observer)
    {
        $cart = Mage::getSingleton('checkout/cart');
        $this->updateKlaviyoCart($cart->getQuote());
    }

    public function orderPlaceAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quoteId = $order->getQuoteId();
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        $this->deleteKlaviyoCart($quote);
    }
}
