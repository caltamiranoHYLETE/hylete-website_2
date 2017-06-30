<?php

class Icommerce_PageManager_Block_Page_Row_Item_Addwidget extends Mage_Adminhtml_Block_Widget_Container
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

        $this->_addButton('addwidget_save', array(
            'label'   => Mage::helper('pagemanager')->__('Save'),
            'onclick' => "$('addwidget_form').submit()",
            'class'   => 'save'
        ));

    }

    /**
     * Return form block HTML
     *
     * @return string
     */
    public function getForm()
    {
        return $this->getLayout()
            ->createBlock('pagemanager/page_row_item_addwidget_form')
            ->toHtml();
    }

}




