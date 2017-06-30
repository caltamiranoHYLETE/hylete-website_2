<?php

class Ebizmarts_BakerlooRestful_Adminhtml_BakerloodiscountController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {

        $this->_title($this->__('Discounts'))
             ->_title($this->__('POS'));

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_restful/adminhtml_bakerloodiscount_grid')->toHtml()
        );
    }

    /**
     * Create new CMS page
     */
    public function newAction() {
        //the same form is used to create and edit
        $this->_forward('edit');
    }

    public function editAction() {

        $id    = $this->getRequest()->getParam('id');
        $model = Mage::getModel('bakerloo_restful/discount');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->_getSession()->addError($this->__('This item no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $this->__("Editing discount #%s", $model->getId()) : $this->__('New Discount'));
        $this->_title($this->__('Discounts'))
             ->_title($this->__('POS'));

        // Restore previously entered form data from session
        $data = $this->_getSession()->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('bakerloodiscount', $model);

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');

        $this->renderLayout();

    }

    public function saveAction() {

        if($this->getRequest()->isPost()) {

            try {

                $postData = $this->getRequest()->getPost('discount');

                $id = isset($postData['id']) ? (int)$postData['id'] : null;

                $discount = Mage::getModel('bakerloo_restful/discount')->load($id);

                try {
                    $discount->addData($postData)->save();

                    $this->_getSession()->addSuccess(Mage::helper('bakerloo_restful')->__('The discount has been saved.'));
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }

                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setUserData($postData);

                $this->_redirect('*/*/edit/');

                return;
            }
        }

        $this->_redirect('adminhtml/bakerloodiscount/');
    }

    public function deleteAction() {

        $discountId = (int)$this->getRequest()->getParam('id');

        if ($discountId) {
            $discount = Mage::getModel('bakerloo_restful/discount')
                       ->load($discountId);
            try {
                $discount->delete();
                $this->_getSession()->addSuccess($this->__('The discount has been deleted.'));
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/bakerloodiscount/');

    }

    protected function _isAllowed() {
        switch ($this->getRequest()->getActionName()) {
            case 'delete':
                $acl = 'ebizmarts_pos/discounts/remove';
                break;
            case 'new':
            case 'save':
            case 'edit':
                $acl = 'ebizmarts_pos/discounts/add_edit';
                break;
            default:
                $acl = 'ebizmarts_pos/discounts/list';
        }
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }

}