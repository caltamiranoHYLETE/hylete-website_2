<?php

class Ebizmarts_BakerlooRestful_Adminhtml_Bakerlooorders_Edit_Tab_ItemsController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {

        $this->_title($this->__('Order Items'));

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_restful/adminhtml_bakerlooorders_edit_tab_items_grid')->toHtml()
        );
    }




}