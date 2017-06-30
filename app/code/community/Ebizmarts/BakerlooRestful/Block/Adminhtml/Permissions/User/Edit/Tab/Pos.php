<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Permissions_User_Edit_Tab_Pos extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel() {
        return $this->__('POS');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->__('POS');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab() {
        return $this->_active() && $this->_user()->getId() && Mage::getSingleton('admin/session')->isAllowed('ebizmarts_pos/pincode_tab');
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden() {
        return !$this->_active() && !$this->_user()->getId();
    }

    private function _active() {
        return (boolean)Mage::helper("bakerloo_restful")->config("general/enabled");
    }

    public function showPincodeUrl() {
        return $this->getUrl('adminhtml/pos_index/pincode');
    }

    public function resetPincodeUrl() {
        return $this->getUrl('adminhtml/pos_index/pincode');
    }

    public function savePincodeUrl(){
        return $this->getUrl('adminhtml/pos_index/pincode');
    }

    public function pinCodePlaceholder() {
        return str_repeat("*", Mage::helper("bakerloo_restful")->config("general/pin_code_length"));
    }

    public function isPinCodeSet() {
        $pincode = Mage::getModel('bakerloo_restful/pincode')->load($this->_user()->getId(), 'admin_user_id')
                        ->getData('pincode');

        return !empty($pincode);
    }

    private function _user() {
        return Mage::registry('permissions_user');
    }

}