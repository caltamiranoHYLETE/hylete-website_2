<?php

class Ebizmarts_BakerlooEmail_Adminhtml_Pos_EmailsController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->_title($this->__('Emails'))
            ->_title($this->__('POS'));

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_email/adminhtml_pos_emails_grid')->toHtml()
        );
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('ebizmarts_pos/orders/emails');
    }

}