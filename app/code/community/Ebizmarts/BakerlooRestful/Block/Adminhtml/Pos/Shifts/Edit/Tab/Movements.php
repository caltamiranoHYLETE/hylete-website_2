<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Shifts_Edit_Tab_Movements extends Mage_Adminhtml_Block_Widget_Grid_Container {

    /**
     * Block constructor
     */
    public function __construct() {
        $this->_controller = 'adminhtml_pos_shifts';
        $this->_blockGroup = 'bakerloo_restful';
        $this->_headerText = Mage::helper('bakerloo_restful')->__('Shift History');

        parent::__construct();

        $this->_removeButton('add');
    }


}