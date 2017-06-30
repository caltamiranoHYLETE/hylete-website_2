<?php

class Ebizmarts_BakerlooPayment_Block_Adminhtml_Sales_Order_View_Tab_Installments_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    protected function _prepareCollection(){

        $order = $this->getOrder();
        if($order->getId()) {
            $collection = Mage::getModel('bakerloo_payment/installment')
                ->getCollection()
                ->addFieldToFilter('order_id', array('eq' => $order->getId()));

            $this->setCollection($collection);
        }
        return parent::_prepareCollection();
    }

    protected function _prepareColumns(){
        $helper = Mage::helper('bakerloo_payment');

        $this->addColumn('id', array(
            'header'    => $helper->__('Installment #'),
            'index'     => 'id'
        ));

//        $this->addColumn('order_id', array(
//            'header'    => $helper->__('Order #'),
//            'index'     => 'order_increment_id'
//        ));

        $this->addColumn('amount_paid', array(
            'header'    => $helper->__('Amount Paid'),
            'index'     => 'amount_paid',
            'type'  => 'currency',
            'currency' => 'order_currency_code'
        ));

        $this->addColumn('amount_refunded', array(
            'header'    => $helper->__('Amount Refunded'),
            'index'     => 'amount_refunded',
            'type'  => 'currency',
            'currency' => 'order_currency_code'
        ));

        $this->addColumn('currency', array(
            'header'    => $helper->__('Currency'),
            'index'     => 'currency'
        ));

        $this->addColumn('payment_method', array(
            'header'    => $helper->__('Payment Method'),
            'index'     => 'payment_method'
        ));

        $this->addColumn('created_at', array(
            'header'    => $helper->__('Date'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('updated_at', array(
            'header'    => $helper->__('Updated'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
        ));

        return parent::_prepareColumns();
    }

    public function getOrder(){
        return Mage::registry('sales_order');
    }
}