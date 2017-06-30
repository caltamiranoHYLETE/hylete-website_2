<?php

class Ebizmarts_BakerlooPayment_Block_Customersignature extends Mage_Adminhtml_Block_Template {

    protected $_posOrder = null;

    protected function _toHtml() {

        if($this->_canShowImage()) {
            return parent::_toHtml();
        }
        else {
            return '';
        }

    }

    public function getOrderId() {
        $order   = Mage::registry('current_order');

        if($order) {
            $orderId = $order->getId();
        }
        else {
            $orderId = null;
        }

        return $orderId;
    }

    public function getImageSource() {
        $orderId = $this->getOrderId();

        $posData = $this->_loadPosOrder($orderId);

        if($posData->getId()) {
            $image = $posData->getCustomerSignature();
        }

        return $image;
    }

    public function getImageMime() {
        return 'image/jpeg';
    }

    protected function _loadPosOrder($orderId) {

        if(is_null($this->_posOrder)) {
            $this->_posOrder = Mage::getModel('bakerloo_restful/order')->load($orderId, 'order_id');
        }

        return $this->_posOrder;
    }

    protected function _canShowImage() {

        $orderId = $this->getOrderId();
        $image   = null;

        if($orderId) {
            $posData = $this->_loadPosOrder($orderId);

            if($posData->getId()) {
                $image = $posData->getCustomerSignature();
            }
        }

        return ($orderId and !empty($image));
    }

}