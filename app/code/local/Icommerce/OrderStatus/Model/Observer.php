<?php

class Icommerce_OrderStatus_Model_Observer
{
    public function cancelOrdersPendingThirdPartyPayment($e)
    {
        $min = (int)Mage::getStoreConfig('orderstatus/settings/max_age_of_pending_orders') * 60; //in minutes
        if ($min<3600) $min = 3600; //Changed to be one hour for dibs, everyone else has to comply.

        $t = date('Y-m-d H:i:s', time() - $min);
        $collection = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('state',array('in' => array('pending_payment')))
                ->addAttributeToFilter('created_at',array('lt' => $t))
                ->addAttributeToFilter('updated_at',array('lt' => $t))
                ->load();

        foreach ($collection as $order) {

            //check if order can be canceled.
            if($this->canCancel($order)){

                //cancel normally
                $canceled = $this->cancelNormal($order);

                //if not canceled and setting active, try cancel with void.
                if($canceled == false)
                    $canceled = $this->cancelByVoid($order);

                //if not canceled and setting active, force cancel by state and status.
                if($canceled == false)
                    $canceled = $this->forceCancelByStateChange($order);

                //if canceled log to history, else log.
                if($canceled == true){
                    $this->addHistoryToOrder($order, $min);
                }else{
                    Mage::log("Failed to cancel order, orderId: ".$order->getId(), Zend_Log::INFO, "icommerce_orderstatus.log", true);
                }

            }
        }
    }

    /** Checking if cancel is possible.
     * @param $order
     * @return bool
     */
    private function canCancel($order){
        $canCancel = false;
        if (Mage::getStoreConfig('orderstatus/settings/cancel_old_pending_orders',$order->getStore())=='1') {
            if($order->canCancel()){
                $id = $order->getId();
                if (Icommerce_Db::getValue("SELECT state FROM sales_flat_order WHERE entity_id=$id")!="canceled" ) {
                    $canCancel = true;
                }
            }
        }
        return $canCancel;
    }

    /** Cancel order
     * @param $order
     * @return bool
     */
    private function cancelNormal($order){
        $successful_cancellation = false;
        try {

            $order->cancel();
            $order->save();
            $successful_cancellation = true;

        } catch(Exception $e){
            Mage::log("Exception on order cancel, orderId: ".$order->getId().", message:" . $e->getMessage(), Zend_Log::ERR, "icommerce_orderstatus.log", true);
        }
        return $successful_cancellation;
    }

    /** Cancel by using void function on order. Optional by config.
     * @param $order
     * @return bool
     */
    private function cancelByVoid($order){
        $successful_cancellation = false;
        try {
            if(Mage::getStoreConfig('orderstatus/settings/allow_void_cancel',$order->getStore())=='1'){
                $order->void();
                $order->save();
                $successful_cancellation = true;
            }
        } catch(Exception $e){
            Mage::log("Exception on order cancel, orderId: ".$order->getId().", message:" . $e->getMessage(), Zend_Log::ERR, "icommerce_orderstatus.log", true);
        }
        return $successful_cancellation;
    }

    /** Forcing cancel by change state and status in DB. Optional by config.
     * @param $order
     * @return bool
     */
    private function forceCancelByStateChange($order){
        $successful_cancellation = false;
        try {
            if(Mage::getStoreConfig('orderstatus/settings/allow_force_cancel',$order->getStore())=='1'){
                $order->setState(Mage_Sales_Model_Order::STATE_CANCELED);
                $order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED);
                $order->save();
                $successful_cancellation = true;
            }
        } catch(Exception $e){
            Mage::log("Exception on order cancel, orderId: ".$order->getId().", message:" . $e->getMessage(), Zend_Log::ERR, "icommerce_orderstatus.log", true);
        }
        return $successful_cancellation;
    }

    /** Adding history to order
     * @param $order
     * @param $min
     */
    private function addHistoryToOrder($order, $min){
        if ( method_exists( $order, 'addStatusHistoryComment' ) ) { // Doesn't exist in 1.3...
            $order->addStatusHistoryComment('Order canceled after pending time exceeded max age ('.($min/60).'min)');
            $order->save();
        }
    }
}