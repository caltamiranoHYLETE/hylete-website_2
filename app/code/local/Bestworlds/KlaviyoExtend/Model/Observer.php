<?php

class Bestworlds_KlaviyoExtend_Model_Observer
{
    const COOKIE_NAME = 'bw_klaviyo_email';

    public function getCookieName()
    {
        return self::COOKIE_NAME;
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
}
