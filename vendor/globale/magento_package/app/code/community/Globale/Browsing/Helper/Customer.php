<?php

/**
 * Helper for set Global-e templates and remove the paypal templates
 * Class Globale_Browsing_Helper_Customer
 */
class Globale_Browsing_Helper_Customer extends Mage_Core_Helper_Abstract {

    /**
     * Check if the current order is an global-e order or magento order
     * @param $IncrementId
     * @return array/bool
     */
    private function getGlobaleOrder($IncrementId) {

        // Get Globale order
        $GlobaleOrder = Mage::getModel('globale_order/orders');
        $GlobaleOrderInfo = $GlobaleOrder->getCollection()->addFieldToFilter('order_id', $IncrementId);
        $GlobaleOrderInfo = $GlobaleOrderInfo->getData();
        if(count($GlobaleOrderInfo)) {
            $Order = $GlobaleOrderInfo[0];
            return $Order;
        }else{
            return false;
        }
    }
}