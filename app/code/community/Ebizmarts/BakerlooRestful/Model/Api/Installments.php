<?php

class Ebizmarts_BakerlooRestful_Model_Api_Installments extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'pos_api_installments';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'installment';

    protected $_model = 'bakerloo_payment/installment';


    public function _createDataObject($id = null, $data = null){
        /* @var $installment Ebizmarts_BakerlooPayment_Model_Installment */
        $installment = !is_null($data) ? $data : Mage::getModel($this->_model)->load($id);

        $result = null;
        if($installment->getId()){

            $result['installment_id'] = $installment->getId();
            $result['order_id'] = $installment->getOrderId();
            $result['pos_order_id'] = $installment->getPosOrderId();
            $result['payment_id'] = $installment->getPaymentId();
            $result['amount_paid'] = $installment->getAmountPaid();
            $result['currency'] = $installment->getCurrency();
            $result['payment_method'] = $installment->getPaymentMethod();
            $result['created_at'] = $installment->getCreatedAt();
            $result['updated_at'] = $installment->getUpdatedAt();
        }

        return $this->returnDataObject($result);
    }

    public function orderReturnBefore($orderObject){

        $payments = isset($orderObject['pos_order']->payment) ? $orderObject['pos_order']->payment : array();
        return $payments;
    }

    public function post(){
        parent::post();

        $orderData = $this->getJsonPayload();
        $orderId = $this->_getIdentifier() ? $this->_getIdentifier() : $orderData->entity_id; //isset($orderData->entity_id) ? $orderData->entity_id : null;
        if(!$orderId)
            Mage::throwException(Mage::helper('bakerloo_restful')->__("Please specify an order ID. "));

        $order = Mage::getModel('sales/order')->load($orderId);
        if(!$order->getId())
            Mage::throwException(Mage::helper('bakerloo_restful')->__("The order no longer exists."));

        $updatedOrder = Mage::getModel('bakerloo_payment/layaway')->processInstallments($order, $orderData);
        $updatedOrder->entity_id = $orderId;

        $posOrder = Mage::getModel('bakerloo_restful/order')->load($order->getId(), 'order_id');
        if($posOrder->getId())
            $posOrder->setJsonPayload(json_encode($updatedOrder))
                ->save();

        $result = array(
            'order_id' => $order->getId(),
            'order_number' => $order->getIncrementId(),
            'order_state'  => $order->getState(),
            'order_status' => $order->getStatusLabel(),
            'order_data' => Mage::getModel('bakerloo_restful/api_orders')->_createDataObject($order->getId())
        );
        return $result;
    }

    public function checkPostPermissions() {
        Mage::getModel('bakerloo_restful/api_orders')->checkPostPermissions();
    }

    protected function _getIndexId() {
        return 'id';
    }
}