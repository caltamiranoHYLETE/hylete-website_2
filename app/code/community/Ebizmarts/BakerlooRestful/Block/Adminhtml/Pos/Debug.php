<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Debug extends Mage_Adminhtml_Block_Widget_Grid_Container {

    /**
     * Block constructor
     */
    public function __construct() {
        $this->_controller = 'adminhtml_pos_debug';
        $this->_blockGroup = 'bakerloo_restful';
        $this->_headerText = Mage::helper('bakerloo_restful')->__('Debug');

        parent::__construct();

        $url = Mage::getSingleton('adminhtml/url')
            ->getUrl('*/*/truncate');
        $msg = Mage::helper('bakerloo_restful')->__('Are you sure?');
        $this->updateButton('add', null, array(
            'label' => Mage::helper('bakerloo_restful')->__('Remove ALL'),
            'sort_order' => 0,
            'onclick' => "pos_debug_truncate('" . $msg . "', '" . $url . "')",
            'class'     => 'delete'
        ));

    }

}