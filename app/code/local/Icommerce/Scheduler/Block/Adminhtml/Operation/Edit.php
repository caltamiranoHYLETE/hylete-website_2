<?php

class Icommerce_Scheduler_Block_Adminhtml_Operation_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'scheduler';
        $this->_controller = 'adminhtml_operation';

        if (Mage::getSingleton('admin/session')->isAllowed('icommerce/scheduler/operations/actions/save')) {
            $this->_updateButton('save', 'label', Mage::helper('scheduler')->__('Save'));
        } else {
            $this->_removeButton('save');
            $this->_removeButton('reset');
        }


        if (Mage::getSingleton('admin/session')->isAllowed('icommerce/scheduler/operations/actions/delete')) {
            $this->_updateButton('delete', 'label', Mage::helper('scheduler')->__('Delete'));
        } else {
            $this->_removeButton('delete');
        }
    }

    public function getHeaderText()
    {
        if( Mage::registry('operation_data') && Mage::registry('operation_data')->getId() )
            return Mage::helper('scheduler')->__("Edit Scheduler Task: '%s'", $this->escapeHtml(Mage::registry('operation_data')->getId()));
        else
            return Mage::helper('scheduler')->__('Add Scheduler Task');
    }
}