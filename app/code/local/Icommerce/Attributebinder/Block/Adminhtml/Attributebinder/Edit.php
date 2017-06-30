<?php

class Icommerce_Attributebinder_Block_Adminhtml_Attributebinder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'attributebinder';
        $this->_controller = 'adminhtml_attributebinder';

        $this->_updateButton('save', 'label', Mage::helper('attributebinder')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('attributebinder')->__('Delete'));
    }

    public function getHeaderText()
    {
        if( Mage::registry('attributebinder_data') && Mage::registry('attributebinder_data')->getId() )
            return Mage::helper('attributebinder')->__("Edit Binding: '%s'", $this->htmlEscape(Mage::registry('attributebinder_data')->getId()));
        else
            return Mage::helper('attributebinder')->__('Add Binding');
    }
}