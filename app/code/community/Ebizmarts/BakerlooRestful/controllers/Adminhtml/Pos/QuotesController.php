<?php

class Ebizmarts_BakerlooRestful_Adminhtml_Pos_QuotesController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->_title($this->__('Quotes'))
            ->_title($this->__('POS'));

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_restful/adminhtml_pos_quotes_grid')->toHtml()
        );
    }

    public function deleteAction() {
        $quoteId = (int)$this->getRequest()->getParam('id');

        if ($quoteId) {
            $quote = Mage::getModel('bakerloo_restful/quote')
                ->load($quoteId);
            try {
                $quote->delete();
                $this->_getSession()->addSuccess($this->__('The record has been deleted.'));
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/pos_quotes/');

    }

    protected function _isAllowed() {

        switch ($this->getRequest()->getActionName()) {
            case 'delete':
                $acl = 'ebizmarts_pos/quotes/delete';
                break;
            default:
                $acl = 'ebizmarts_pos/quotes/list';
        }

        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }

}