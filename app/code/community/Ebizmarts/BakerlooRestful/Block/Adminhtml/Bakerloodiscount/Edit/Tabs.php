<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Bakerloodiscount_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('bakerloodiscount_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('bakerloo_restful')->__('Discount Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('main_section', array(
            'label'     => Mage::helper('bakerloo_restful')->__('Discount data'),
            'title'     => Mage::helper('bakerloo_restful')->__('Discount information'),
            'content'   => $this->getLayout()->createBlock('bakerloo_restful/adminhtml_bakerloodiscount_edit_tab_main')->toHtml(),
            'active'    => true
        ));

        return parent::_beforeToHtml();
    }

}