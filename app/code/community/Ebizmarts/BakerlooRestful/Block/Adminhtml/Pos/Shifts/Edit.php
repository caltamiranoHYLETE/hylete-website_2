<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Shifts_Edit extends Mage_Adminhtml_Block_Abstract {

    public function __construct() {
        $this->_objectId   = 'id';
        $this->_controller = 'adminhtml_pos_shifts';
        $this->_blockGroup = 'bakerloo_restful';
        $this->_headerText = Mage::helper('bakerloo_restful')->__('Shift details');

        parent::__construct();
    }

    public function getBackUrl() {
        return $this->getUrl('*/*/');
    }
}