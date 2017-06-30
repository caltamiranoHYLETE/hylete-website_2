<?php

/**
 * Grid to show call API calls.
 */

class Ebizmarts_BakerlooRestful_Adminhtml_Pos_DebugController extends Mage_Adminhtml_Controller_Action {

    protected function _initDebug($id) {

        $log = Mage::getModel('bakerloo_restful/debug');

        if ($id)
            $log->load($id);

        Mage::register('current_log', $log);
        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('Debug'))
            ->_title($this->__('POS'));

        $this->loadLayout();

        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_restful/adminhtml_pos_debug_grid')->toHtml()
        );
    }

    /**
     * View additional data for the request.
     */
    public function viewAction() {
        $this->_title($this->__('View log'))
            ->_title($this->__('POS API'));

        $id = $this->getRequest()->getParam('id');

        $this->_initDebug($id);
        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');

        $log = Mage::registry('current_log');

        if(!$log->getId()) {
            $this->_getSession()->addError($this->__('Entry does not exist.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->renderLayout();

    }

    public function truncateAction() {

        Mage::getResourceModel('bakerloo_restful/debug')->truncateTable();

        $this->_redirect('*/*/');
        return;
    }

    protected function _isAllowed() {
        switch ($this->getRequest()->getActionName()) {
            case 'truncate':
                $acl = 'ebizmarts_pos/debug/truncate';
                break;
            case 'view':
                $acl = 'ebizmarts_pos/debug/view';
                break;
            case 'list':
                $acl = 'ebizmarts_pos/debug/list';
                break;
            default:
                $acl = 'ebizmarts_pos/debug/list';
        }
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }

}