<?php
class TroopID_Connect_Model_Cart_Observer {

    public function clearAffiliation(Varien_Event_Observer $observer) {

        if (!Mage::helper("troopid_connect")->isOperational())
            return;

        $cart   = $observer->getEvent()->getCart();
        $value  = $cart->getQuote()->getTroopidUid();

        if (isset($value) && $cart->getItemsCount() == 0) {
            $cart->getQuote()->setTroopidUid(NULL);
            $cart->getQuote()->setTroopidScope(NULL);
            $cart->getQuote()->setTroopidAffiliation(NULL);
            $cart->getQuote()->save();
        }

    }

}