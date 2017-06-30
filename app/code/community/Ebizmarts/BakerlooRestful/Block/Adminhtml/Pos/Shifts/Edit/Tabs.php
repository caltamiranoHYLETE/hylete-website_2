<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Shifts_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('pos_shifts_tabs');
    }

    protected function _beforeToHtml() {
        $this->addTab('summary', array(
        'label'     => Mage::helper('bakerloo_restful')->__('Summary'),
            'title'     => Mage::helper('bakerloo_restful')->__('Summary'),
            'content'   => $this->getLayout()->createBlock('bakerloo_restful/adminhtml_pos_shifts_edit_tab_main')->toHtml(),
            'active'    => true
        ));

        $this->addTab('movements', array(
            'label'     => Mage::helper('bakerloo_restful')->__('Movements'),
            'title'     => Mage::helper('bakerloo_restful')->__('Movements'),
            'content'   => $this->getLayout()->createBlock('bakerloo_restful/adminhtml_pos_shifts_edit_tab_movements_grid')->toHtml(),
        ));

        $this->addTab('transactions', array(
            'label'     => Mage::helper('bakerloo_restful')->__('Transactions'),
            'title'     => Mage::helper('bakerloo_restful')->__('Transactions'),
            'content'   => $this->getLayout()->createBlock('bakerloo_restful/adminhtml_pos_shifts_edit_tab_transactions_grid')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}