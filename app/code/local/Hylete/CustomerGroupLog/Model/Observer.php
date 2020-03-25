<?php

class Hylete_CustomerGroupLog_Model_Observer
{

    public function customer_save_before( $observer )
    {

        try {

            $customer = $observer->getCustomer();
            $origCustomerGroup = $customer->getOrigData('group_id');
            $newCustomerGroup = $customer->getData('group_id');

            if($origCustomerGroup != $newCustomerGroup) {
                $customerId = $customer->getData('entity_id');
                $customerGroupLogModel = Mage::getModel('customergrouplog/customergrouplog');
                $customerGroupLogModel->load();
                $customerGroupLogModel->setCustomerId($customerId);
                $customerGroupLogModel->setOldCustomerGroup($origCustomerGroup);
                $customerGroupLogModel->setNewCustomerGroup($newCustomerGroup);
                $customerGroupLogModel->save();
            }


        } catch ( Exception $e ) {
            Mage::log( "customer_save_before observer failed: " . $e->getMessage(),'customergrouplog.log', true );
        }
    }
}