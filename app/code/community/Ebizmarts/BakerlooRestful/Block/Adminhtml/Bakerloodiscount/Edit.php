<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Bakerloodiscount_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId   = 'id';
        $this->_controller = 'adminhtml_bakerloodiscount';
        $this->_blockGroup = 'bakerloo_restful';

        parent::__construct();
    }

    public function getHeaderText() {
        if ($this->getBakerlooOrder()->getId()) {
            return Mage::helper('bakerloo_restful')->__("Edit Discount");
        }
        else {
            return Mage::helper('bakerloo_restful')->__('New Discount');
        }
    }

    /**
     * Get object from registry.
     *
     * @return Ebizmarts_BakerlooRestful_Model_Order
     */
    public function getBakerlooOrder() {
        return Mage::registry('bakerloodiscount');
    }

}