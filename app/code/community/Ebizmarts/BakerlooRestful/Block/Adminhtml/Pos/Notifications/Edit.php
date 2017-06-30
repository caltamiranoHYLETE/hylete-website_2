<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Notifications_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId   = 'id';
        $this->_controller = 'adminhtml_pos_notifications';
        $this->_blockGroup = 'bakerloo_restful';

        parent::__construct();
    }

    public function getHeaderText() {
        if ($this->getPosNotification()->getId()) {
            return Mage::helper('bakerloo_restful')->__("Edit Message");
        }
        else {
            return Mage::helper('bakerloo_restful')->__('New Message');
        }
    }

    /**
     * Get object from registry.
     *
     * @return Ebizmarts_BakerlooRestful_Model_Order
     */
    public function getPosNotification() {
        return Mage::registry('pos_notification');
    }

}