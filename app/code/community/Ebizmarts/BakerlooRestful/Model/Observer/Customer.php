<?php

class Ebizmarts_BakerlooRestful_Model_Observer_Customer {

    /**
     * Customer delete handler
     *
     * @param Varien_Object $observer
     * @return Mage_Newsletter_Model_Observer
     */
    public function customerDeleted($observer) {
        $customer = $observer->getEvent()->getCustomer();

        if($customer->getId()) {

            $trash = Mage::getModel('bakerloo_restful/customertrash');
            $trash->setCustomerId($customer->getId());
            $trash->save();
        }

        return $this;
    }

}