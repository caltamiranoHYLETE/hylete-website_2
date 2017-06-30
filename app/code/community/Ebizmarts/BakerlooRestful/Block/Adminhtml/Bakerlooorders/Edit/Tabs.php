<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Bakerlooorders_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('bakerlooorders_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('bakerloo_restful')->__('Order Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('main_section', array(
            'label'     => Mage::helper('bakerloo_restful')->__('Order data'),
            'title'     => Mage::helper('bakerloo_restful')->__('Order information'),
            'content'   => $this->getLayout()->createBlock('bakerloo_restful/adminhtml_bakerlooorders_edit_tab_main')->toHtml(),
            'active'    => true
        ));

        $this->addTab('order_items', array(
            'label'     => Mage::helper('bakerloo_restful')->__('Order items'),
            'title'     => Mage::helper('bakerloo_restful')->__('Order items'),
            'content'   => $this->getLayout()->createBlock('bakerloo_restful/adminhtml_bakerlooorders_edit_tab_items_grid')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}