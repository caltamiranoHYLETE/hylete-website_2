<?php

class Ebizmarts_BakerlooPayment_Adminhtml_Sales_Order_View_InstallmentsController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {

        $this->_title($this->__('Installments'));

        $this->loadLayout();
        $this->_setActiveMenu('sales/order');
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_payment/adminhtml_sales_order_view_tab_installments_grid')->toHtml()
        );
    }
}