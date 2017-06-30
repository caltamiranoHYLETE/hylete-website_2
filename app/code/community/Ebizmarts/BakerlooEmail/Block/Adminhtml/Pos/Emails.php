<?php

class Ebizmarts_BakerlooEmail_Block_Adminhtml_Pos_Emails extends Mage_Adminhtml_Block_Widget_Grid_Container {

    /**
     * Block constructor
     */
    public function __construct() {
        $this->_controller = 'adminhtml_pos_emails';
        $this->_blockGroup = 'bakerloo_email';
        $this->_headerText = Mage::helper('bakerloo_email')->__('Emails');

        parent::__construct();

        $this->_removeButton('add');
    }

}