<?php

class Ebizmarts_BakerlooShipping_Model_Observer {

    public function inStorePickup($observer) {
        $request = $observer->getEvent()->getRequest();

        $pickupData = $request->getPost('pos_store_pickup_store', null);

        $session = Mage::getSingleton('checkout/session');

        if(!is_null($pickupData)) {
            $session->setPosInStorePickupDesc($pickupData);
        }
        else {
            $session->setPosInStorePickupDesc(null);
        }
    }

    public function inStorePickupReset($observer) {
        Mage::getSingleton('checkout/session')->setPosInStorePickupDesc(null);
    }

}