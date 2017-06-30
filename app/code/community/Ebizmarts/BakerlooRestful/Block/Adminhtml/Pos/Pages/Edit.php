<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Pages_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId   = 'id';
        $this->_controller = 'adminhtml_pos_pages';
        $this->_blockGroup = 'bakerloo_restful';

        parent::__construct();

        $this->_addButton('generate', array(
            'label'     => Mage::helper('adminhtml')->__('Generate'),
            'onclick'   => 'generatePages();',
            'class'     => 'save',
        ), 1);

        $this->removeButton('save');
    }

    public function getHeaderText() {
        return Mage::helper('bakerloo_restful')->__('New Page');
    }

    /**
     * Get object from registry.
     *
     * @return Ebizmarts_BakerlooRestful_Model_Order
     */
    public function getPosPage() {
        return Mage::registry('pos_page');
    }

}