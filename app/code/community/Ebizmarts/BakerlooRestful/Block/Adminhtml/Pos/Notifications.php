<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Notifications extends Mage_Adminhtml_Block_Widget_Grid_Container {

    /**
     * Block constructor
     */
    public function __construct() {
        $this->_controller = 'adminhtml_pos_notifications';
        $this->_blockGroup = 'bakerloo_restful';
        $this->_headerText = Mage::helper('bakerloo_restful')->__('Notifications');

        parent::__construct();
    }

}