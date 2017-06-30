<?php

class Ebizmarts_BakerlooReports_Block_Adminhtml_Pos_Reports extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct(){
        $this->_controller = 'adminhtml_pos_reports';
        $this->_blockGroup = 'bakerloo_reports';
        $this->_headerText = Mage::helper('bakerloo_reports')->__("POS Reports");

        parent::__construct();

        //$this->removeButton('add');
    }
}