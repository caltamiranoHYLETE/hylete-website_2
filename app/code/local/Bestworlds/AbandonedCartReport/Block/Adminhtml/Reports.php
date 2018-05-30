<?php

class Bestworlds_AbandonedCartReport_Block_Adminhtml_Reports extends Mage_Adminhtml_Block_Widget_Grid_Container{

    const CLOSETIME = 3600;

    public function __construct(){

        $this->_controller = 'adminhtml_reports';
        $this->_blockGroup = 'abandonedcartreport';
        $this->_headerText = Mage::helper('abandonedcartreport')->__('Abandoned Cart Reports Only');
        parent::__construct();
        $this->_removeButton('add');
    }

}