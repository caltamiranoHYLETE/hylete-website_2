<?php

class Icommerce_Scheduler_Block_Adminhtml_History_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    private $_history;

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'scheduler';
        $this->_controller = 'adminhtml_history';

        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
    }

    protected function getHistory()
    {
        if (!$this->_history) {
            $this->_history = Mage::registry('current_scheduler_history');
        }

        return $this->_history;
    }

    public function getHeaderText()
    {
        return Mage::helper('scheduler')->__('History') . ' | ' . $this->formatDate($this->getHistory()->getCreatedAt(), 'medium', true);
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/edit', array('id' => $this->getHistory()->getOperationId()));
    }

}