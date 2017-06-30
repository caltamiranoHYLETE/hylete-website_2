<?php

class Icommerce_PageManager_Block_Page_Row_Item_Addheading extends Mage_Adminhtml_Block_Widget_Container
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
        $this->_addButton('addheading_save', array(
            'label'   => Mage::helper('pagemanager')->__('Save'),
            'onclick' => 'addheadingForm.submit()',
            'class'   => 'save'
        ));
        
    }
    
    public function getItemType()
    {
        return array('value'=>'heading', 'label'=>Mage::helper('pagemanager')->__('H1'));
    }
   
}

