<?php

class Ebizmarts_BakerlooEmail_Adminhtml_Pos_UnsentController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->_title($this->__('Unsent'))
            ->_title($this->__('POS'));

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_email/adminhtml_pos_unsent_grid')->toHtml()
        );
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('ebizmarts_pos/orders/unsent');
    }

    public function processallAction() {
        try {

            Mage::getModel('bakerloo_restful/api_orders')->processUnsentEmails();

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('bakerloo_email')->__("Emails sent successfully.")
            );
            $this->_redirectReferer();

        }
        catch(Exception $e) {
            Mage::logException($e);

            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('bakerloo_email')->__("Failed to process email queue. " . $e->getMessage())
            );

            $this->_redirectReferer();
        }

    }
}