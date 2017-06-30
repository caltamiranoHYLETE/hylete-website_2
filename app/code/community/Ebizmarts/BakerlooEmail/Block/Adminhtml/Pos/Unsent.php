<?php

class Ebizmarts_BakerlooEmail_Block_Adminhtml_Pos_Unsent extends Mage_Adminhtml_Block_Widget_Grid_Container {

    /**
     * Block constructor
     */
    public function __construct() {
        $this->_controller = 'adminhtml_pos_unsent';
        $this->_blockGroup = 'bakerloo_email';
        $this->_headerText = Mage::helper('bakerloo_email')->__('Unsent Emails');

        parent::__construct();

        $this->_removeButton('add');
    }

}