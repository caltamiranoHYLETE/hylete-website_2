<?php

class Icommerce_PageManager_Block_Page_Row_Item_Addhtml extends Mage_Adminhtml_Block_Widget_Container
{
    protected function _construct()
    {
        parent::_construct();
    }
    
    /**
     * Prepare button
     * Load Wysiwyg on demand and Prepare layout
     */
    protected function _prepareLayout()
    {
        $this->_addButton('backButton', array(
                    'label'     => Mage::helper('adminhtml')->__('Back'),
                    'onclick'   => 'history.back()',
                    'class' => 'back'
        ));
        $this->_addButton('addhtml_save', array(
            'label'   => Mage::helper('pagemanager')->__('Save'),
            'onclick' => 'addhtmlForm.submit()',
            'class'   => 'save'
        ));
        
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        
    }
    
    /**
     * Return form block HTML
     *
     * @return string
     */
    public function getForm()
    {
        return $this->getLayout()
            ->createBlock('pagemanager/page_row_item_addhtml_form')
            ->toHtml();
    }
    

    
    
}




