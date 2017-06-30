<?php
class Icommerce_Attributebinder_Block_Adminhtml_Attributebinder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_attributebinder';
        $this->_blockGroup = 'attributebinder';
        $this->_headerText = Mage::helper('attributebinder')->__('Attributebinder Manager');
        $this->_addButtonLabel = Mage::helper('attributebinder')->__('Add Attributebinder');
        parent::__construct();
    }
}