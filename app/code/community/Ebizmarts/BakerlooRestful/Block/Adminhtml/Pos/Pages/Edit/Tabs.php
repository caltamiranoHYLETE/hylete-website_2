<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Pages_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('pos_pages_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('bakerloo_restful')->__('Page Configuration'));
    }

    protected function _beforeToHtml() {
        $this->addTab('main_section', array(
            'label'     => Mage::helper('bakerloo_restful')->__('Configuration'),
            'title'     => Mage::helper('bakerloo_restful')->__('Page Configuration'),
            'content'   => $this->getLayout()->createBlock('bakerloo_restful/adminhtml_pos_pages_edit_tab_main')->toHtml(),
            'active'    => true
        ));
        return parent::_beforeToHtml();
    }

}