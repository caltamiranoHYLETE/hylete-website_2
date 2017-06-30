<?php

class Icommerce_PageManager_Block_Page_Row_Item_Edithtml extends Mage_Adminhtml_Block_Widget_Container
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
        $params = $this->getRequest()->getParams();
        $id = $params['id'];
        
        $this->_addButton('backButton', array(
                    'label'     => Mage::helper('adminhtml')->__('Back'),
                    'onclick'   => 'window.location.href=\''.$this->getUrl('*/pagemanager/edit/', array('id' =>$_SESSION['page_id'])).'\'',
                    'class' => 'back'
        ));
        
        $this->_addButton('deletehtml', array(
            'label'   => Mage::helper('pagemanager')->__('Delete'),
            'onclick'   => 'if(confirm(\''.Mage::helper('pagemanager')->__('Are you sure?').'\')){window.location.href=\''.$this->getUrl('*/pageitem/deletehtml/id/'.$id).'\';}',
            'class'   => 'delete'
        ));
        
        $this->_addButton('edithtml_save', array(
            'label'   => Mage::helper('pagemanager')->__('Save'),
            'onclick' => 'edithtmlForm.submit()',
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
            ->createBlock('pagemanager/page_row_item_edithtml_form')
            ->toHtml();
    }
    
}

