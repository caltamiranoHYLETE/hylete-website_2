<?php

class Ebizmarts_BakerlooRestful_Adminhtml_Pos_IndexController extends Mage_Adminhtml_Controller_Action {

    public function pincodeAction() {
        $userId = $this->getRequest()->getParam('customer_id', null);
        $pin    = Mage::getModel('bakerloo_restful/pincode')->load($userId, 'admin_user_id');

        if($this->getRequest()->isPost()) {
            if(!$pin->getId())
                $pin->setAdminUserId($userId);
            $pin->resetPincode();
            //Load user and update `modified` date.
            Mage::getModel('admin/user')->load($userId)->setModified(now())->save();
        }
        else {
            $this->getResponse()->setBody($pin->getPincode());
        }
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('ebizmarts_pos/pincode_tab');
    }

}