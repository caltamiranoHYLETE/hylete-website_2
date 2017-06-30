<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Bakerlooorders_Edit_Tab_Items extends Mage_Adminhtml_Block_Widget_Grid_Container {

    /**
     * Block constructor
     */
    public function __construct() {
        $this->_controller = 'adminhtml_bakerlooorders_edit_tab_items';
        $this->_blockGroup = 'bakerloo_restful';
        $this->_headerText = Mage::helper('bakerloo_restful')->__('Order items');

        parent::__construct();

        $this->_removeButton('add');
    }


}