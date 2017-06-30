<?php

class Icommerce_PageManager_Block_Page_Edit extends Mage_Adminhtml_Block_Widget_Container
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
        $_SESSION['page_id'] = $id;

        $this->_addButton('backButton', array(
                    'label'     => Mage::helper('adminhtml')->__('Back'),
                    'onclick'   => 'window.location.href=\''.$this->getUrl('*/*/').'\'',
                    'class' => 'back'
        ));

        $this->_addButton('delete', array(
            'label'   => Mage::helper('pagemanager')->__('Delete'),
            'onclick'   => 'if(confirm(\''.Mage::helper('pagemanager')->__('Are you sure?').'\')){window.location.href=\''.$this->getUrl('*/pagemanager/delete/id/'.$id).'\';}',
            'class'   => 'delete'
        ));

        $this->_addButton('add_save', array(
            'label'   => Mage::helper('pagemanager')->__('Save'),
            'onclick' => 'addForm.submit()',
            'class'   => 'save'
        ));
    }
}

