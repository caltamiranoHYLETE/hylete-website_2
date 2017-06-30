<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Bakerloodiscount extends Mage_Adminhtml_Block_Widget_Grid_Container {

    /**
     * Block constructor
     */
    public function __construct() {
        $this->_controller = 'adminhtml_bakerloodiscount';
        $this->_blockGroup = 'bakerloo_restful';
        $this->_headerText = Mage::helper('bakerloo_restful')->__('Discounts');

        parent::__construct();
    }

}