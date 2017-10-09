<?php

class Globale_Order_Model_Rewrite_Sales_Order extends Mage_Sales_Model_Order
{
    public function isCurrencyDifferent() {

        $GlobaleOrder = Mage::getModel('globale_order/orders')->load($this->getIncrementId(),'order_id');

        if($GlobaleOrder->hasId()){
            return true;
        }

        return parent::isCurrencyDifferent();
    }
}