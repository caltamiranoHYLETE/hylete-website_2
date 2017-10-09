<?php

class Globale_Order_Model_MyAccount extends Mage_Core_Model_Abstract {

    /**
     * My account - Update Order totals prices
     * Event ==> sales_order_load_after
     * @param Mage_Sales_Model_Order $Order
     * @access public
     */
    public function changeOrderTotals(Mage_Sales_Model_Order $Order) {

        // Apply totals changes only on logged in user
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {

            // Get only global-e orders
            $GlobaleOrder = $this->getGlobaleOrder($Order->getIncrementId());
            if ($GlobaleOrder) {
                Mage::unregister('globale_my_account_order');
                Mage::register('globale_my_account_order',true);
                // Get globale order details
                $OrderInfo = $this->getInternationalDetails($Order->getIncrementId());
                // Set currency code and base currency code, in order to remove the label: 'Grand Total to be Charged'
                $Order->setOrderCurrencyCode($OrderInfo['details']['customer_currency_code']);
                $Order->setBaseCurrencyCode($OrderInfo['details']['transaction_currency_code']);
                // Update Grand total price
                $Order->setGrandTotal($OrderInfo['details']['total_price']);
                $Order->setBaseGrandTotal(null);
                // Update Shipping & Handling price
                $Order->setShippingInclTax($OrderInfo['shipping']['total_price'] - $OrderInfo['discounts']['total_shipping_discount_price']);
                $Order->setShippingDescription($OrderInfo['shipping']['shipping_method_type_name']);
                $Order->setTotalDutiesPrice($OrderInfo['details']['total_duties_price']);
                //in order to remove the 'Your credit card will be charged for' line from My Account
                $Order->setBaseCurrencyCode($Order->getOrderCurrencyCode());
                $Order->setIsGlobaleOrder(true);
            }else{
                Mage::unregister('globale_my_account_order');
                Mage::register('globale_my_account_order',false);
            }
        }
    }

    /**
     * Check if the current order is an global-e order or magento order
     * @param $IncrementId
     * @return array/bool
     */
    public function getGlobaleOrder($IncrementId) {

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

    /**
     * Get Global-e order international details from database by magento increment id
     * @param string $IncrementId
     * @return array $InternationalDetails
     * @access private
     */
    public function getInternationalDetails($IncrementId) {

        $OrderInfo = array();
        // Get Globale order details
        $OrderDetails = Mage::getModel('globale_order/details');
        $InternationalOrderDetails = $OrderDetails->getCollection()->addFieldToFilter('order_id', $IncrementId);
        $InternationalOrderDetails = $InternationalOrderDetails->getData();
        if(count($InternationalOrderDetails)) {
            $OrderInfo['details'] = $InternationalOrderDetails[0];
        }

        // Get Globale order shipping details
        $OrderShipping = Mage::getModel('globale_order/shipping');
        $InternationalShipping = $OrderShipping->getCollection()->addFieldToFilter('order_id', $IncrementId);
        $InternationalShipping = $InternationalShipping->getData();
        if(count($InternationalShipping)) {
            $OrderInfo['shipping'] = $InternationalShipping[0];
        }

        // Get Globale order discounts details
        $OrderDiscounts = Mage::getModel('globale_order/discounts');
        $OrderModel = Mage::getModel('globale_order/observers_order');
        $InternationalDiscounts = $OrderDiscounts->getCollection()->addFieldToFilter('order_id', $IncrementId);
        $InternationalDiscounts = $InternationalDiscounts->getData();
        $TotalShippingDiscountPrice = 0;
        $BaseTotalShippingDiscountPrice = 0;
        if(count($InternationalDiscounts)) {
            foreach ($InternationalDiscounts as $Discount) {
                // Calculate all shipping discounts price for the order
                if($Discount['discount_type'] == $OrderModel::SHIPPING_DISCOUNT) {
                    $TotalShippingDiscountPrice += $Discount['international_price'];
                    $BaseTotalShippingDiscountPrice += $Discount['price'];
                }
            }
        }
        $OrderInfo['discounts']['total_shipping_discount_price'] = $TotalShippingDiscountPrice;
        $OrderInfo['discounts']['base_shipping_discount_price']  = $BaseTotalShippingDiscountPrice;
        return $OrderInfo;
    }

    /**
     * Get Global-e order international payment from database
     * @param string $IncrementId
     * @return array $InternationalDetails
     * @TODO CHECK USAGE
     * @access private
     */
    public function getInternationalPayment()
    {
        $Order = Mage::registry('current_order');
        // Get Globale order payment details
        $OrderPayment = Mage::getModel('globale_order/payment');
        $InternationalPayment = $OrderPayment->getCollection()->addFieldToFilter('order_id', $Order->getIncrementId());
        $InternationalPayment = $InternationalPayment->getData();
        $Payment = array();
        if (count($InternationalPayment)) {
            $Payment = $InternationalPayment[0];
        }
        return $Payment;
    }
}