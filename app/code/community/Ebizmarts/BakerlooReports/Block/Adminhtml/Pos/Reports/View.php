<?php

class Ebizmarts_BakerlooReports_Block_Adminhtml_Pos_Reports_View extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct(){

        $this->_controller = 'adminhtml_pos_reports_view';
        $this->_blockGroup = 'bakerloo_reports';
        $this->_headerText = $this->getCurrentReport()->getReportName(); //Mage::helper('bakerloo_reports')->__("Report");

        parent::__construct();

        $this->removeButton('add');
    }

    protected function _prepareLayout() {

        $this->_addButton('regenerate', array(
            'label'     =>  Mage::helper('bakerloo_reports')->__('Regenerate'),
            'onclick'   => "setLocation('{$this->getUrl('*/*/regenerate', array('report_id' => $this->getCurrentReport()->getId()))}')",
        ));

//        $this->_addButton('checkdups', array(
//            'label'     =>  Mage::helper('bakerloo_reports')->__('Check duplicates'),
//            'onclick'   => "setLocation('{$this->getUrl('*/*/check', array('report_id' => $this->getCurrentReport()->getId()))}')",
//        ));

        parent::_prepareLayout();
    }

    public function getCurrentReport(){
        return Mage::registry('bakerloo_reports_current');
    }
}