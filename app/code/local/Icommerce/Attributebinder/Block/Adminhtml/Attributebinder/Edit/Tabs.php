<?php

class Icommerce_Attributebinder_Block_Adminhtml_Attributebinder_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('attributebinder_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('attributebinder')->__('Binding Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general_section', array(
            'label'     => Mage::helper('attributebinder')->__('Attributes'),
            'title'     => Mage::helper('attributebinder')->__('Attributes'),
            'content'   => $this->getLayout()->createBlock('attributebinder/adminhtml_attributebinder_edit_tab_attributes')->toHtml(),
        ));

        //only if saved once.
        if($this->getRequest()->getParam('id') != ""){
            $this->addTab('bindings_section', array(
                'label'     => Mage::helper('attributebinder')->__('Bindings'),
                'title'     => Mage::helper('attributebinder')->__('Bindings'),
                'content'   => $this->getLayout()->createBlock('attributebinder/adminhtml_attributebinder_edit_tab_bindings')->toHtml(),
            ));
        }
        
        return parent::_beforeToHtml();
    }
}