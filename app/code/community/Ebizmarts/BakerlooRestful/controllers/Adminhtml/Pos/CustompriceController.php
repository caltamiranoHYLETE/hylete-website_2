<?php

class Ebizmarts_BakerlooRestful_Adminhtml_Pos_CustompriceController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_restful/adminhtml_pos_customprice_grid')->toHtml()
        );
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
//        $id    = $this->getRequest()->getParam('id');
//        $model = Mage::getModel('bakerloo_restful/customprice');
//
//        if ($id) {
//            $model->load($id);
//            if (!$model->getId()) {
//                $this->_getSession()->addError($this->__('This item no longer exists.'));
//                $this->_redirect('*/*/');
//                return;
//            }
//        }
//
//        $this->_title($model->getId() ? $this->__("Editing item #%s", $model->getId()) : $this->__('New discount'));
//        $this->_title($this->__('Notifications'))
//            ->_title($this->__('POS'));
//
//        // Restore previously entered form data from session
//        $data = $this->_getSession()->getUserData(true);
//        if (!empty($data)) {
//            $model->setData($data);
//        }
//
//        Mage::register('pos_notification', $model);
//
//        $this->loadLayout();
//        $this->_setActiveMenu('ebizmarts_pos');
//
//        $this->renderLayout();
    }

    public function saveAction() {
//        if($this->getRequest()->isPost()) {
//
//            try {
//
//                $postData = $this->getRequest()->getPost('notification');
//
//                $id = isset($postData['id']) ? (int)$postData['id'] : null;
//
//                $notification = Mage::getModel('bakerloo_restful/notification')->load($id);
//
//                try {
//
//                    $notification->addData($postData)->save();
//
//                    $this->_getSession()->addSuccess(Mage::helper('bakerloo_restful')->__('The message has been saved.'));
//                } catch (Exception $e) {
//                    $this->_getSession()->addError($e->getMessage());
//                }
//
//                $this->_redirect('*/*/');
//                return;
//            } catch (Mage_Core_Exception $e) {
//                $this->_getSession()->addError($e->getMessage());
//                $this->_getSession()->setUserData($postData);
//
//                $this->_redirect('*/*/edit/');
//
//                return;
//            }
//        }
//
//        $this->_redirect('adminhtml/pos_notifications/');
    }

    public function deleteAction() {
//        $id = $this->getRequest()->getParam('id', false);
//
//        try {
//            $notification = Mage::getModel('bakerloo_restful/notification')->load($id);
//
//            if($notification->getId()) {
//                $notification->setStores($notification->getStoreId());
//                $notification->setIsRemove(1)->save();
//            }
//            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The message has been deleted.'));
//        } catch (Exception $e) {
//            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occurred while deleting this message.'));
//        }
//
//        $this->_redirect("*/*/");
    }

    protected function _isAllowed() {
        switch ($this->getRequest()->getActionName()) {
            case 'delete':
            case 'new':
            case 'save':
            case 'edit':
                $acl = 'ebizmarts_pos/customprice/add_edit';
                break;
            default:
                $acl = 'ebizmarts_pos/customprice/list';
        }
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }

}