<?php

class Ebizmarts_BakerlooPayment_Block_Adminhtml_Sales_Order_View_Tab_Installments extends Mage_Adminhtml_Block_Widget_Grid_Container
    implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    /**
     * Block constructor
     */
    public function __construct() {
        $this->_controller = 'adminhtml_sales_order_view_tab_installments';
        $this->_blockGroup = 'bakerloo_payment';
        $this->_headerText = Mage::helper('bakerloo_payment')->__('Installments');

        parent::__construct();

        $this->_removeButton('add');
    }


    public function getTabLabel(){
        return $this->__("Installments");
    }

    public function getTabTitle(){
        return $this->__("Installments");
    }

    public function canShowTab(){
        return $this->_isActive();
    }

    public function isHidden(){
        return !$this->_isActive();
    }

    private function _isActive(){
        return (boolean)Mage::helper("bakerloo_restful")->config("general/enabled");
    }
}