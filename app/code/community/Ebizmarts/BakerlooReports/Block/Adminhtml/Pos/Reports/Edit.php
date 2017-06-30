<?php

class Ebizmarts_BakerlooReports_Block_Adminhtml_Pos_Reports_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId   = 'id';
        $this->_controller = 'adminhtml_pos_reports';
        $this->_blockGroup = 'bakerloo_reports';
        $this->_headerText = Mage::helper('bakerloo_reports')->__('Edit report');

        parent::__construct();
    }

    public function getCurrentReport(){
        return Mage::registry('bakerloo_reports_current');
    }
}