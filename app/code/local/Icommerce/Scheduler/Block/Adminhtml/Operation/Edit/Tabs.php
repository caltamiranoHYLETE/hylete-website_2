<?php

class Icommerce_Scheduler_Block_Adminhtml_Operation_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('operationTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('scheduler')->__('Scheduler Task'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('details', array(
            'label'     => Mage::helper('scheduler')->__('Details'),
            'title'     => Mage::helper('scheduler')->__('Details'),
            'content'   => $this->getLayout()->createBlock('scheduler/adminhtml_operation_edit_tab_details')->toHtml(),
        ));

        if (Mage::registry('operation_data')->getId()) {
            $this->addTab('history', array(
                'label' => Mage::helper('scheduler')->__('History'),
                'class' => 'ajax',
                'url' => $this->getUrl('*/*/history', array('_current' => true)),
            ));
        }

        if (isset($_SESSION['admin']['active_tab_id'])) {
            $this->setActiveTab($_SESSION['admin']['active_tab_id']);
        }

        return parent::_beforeToHtml();
    }
}