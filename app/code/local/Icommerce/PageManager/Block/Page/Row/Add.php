<?php

class Icommerce_PageManager_Block_Page_Row_Add extends Mage_Adminhtml_Block_Widget_Container
{
    protected function _construct()
    {
        parent::_construct();
    }
    
    /**
     * Prepare button
     *
     */
    protected function _prepareLayout()
    {
        $this->_addButton('backButton', array(
                    'label'     => Mage::helper('adminhtml')->__('Back'),
                    'onclick'   => 'history.back()',
                    'class' => 'back'
        ));
        $this->_addButton('add_save', array(
            'label'   => Mage::helper('pagemanager')->__('Save'),
            'onclick' => 'addForm.submit()',
            'class'   => 'save'
        ));
        
    }
    
    public function getItemType()
    {
        return array('value'=>'image', 'label'=>Mage::helper('pagemanager')->__('Image'));
    }
   
}

