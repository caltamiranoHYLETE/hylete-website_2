<?php

class Icommerce_PageManager_Block_Page extends Mage_Adminhtml_Block_Widget_Container
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
        $this->_addButton('add_new', array(
            'label'   => Mage::helper('pagemanager')->__('Add Page'),
            'onclick' => "setLocation('{$this->getUrl('*/*/add')}')",
            'class'   => 'add'
        ));
    }
    
    public function getRowUrl($id)
    {
    	return $this->getUrl('*/*/edit', array('id' => $id));
    }
	
	public function getPages(){
		return Mage::getModel('pagemanager/page')->getPages();
	}

	public function getPageRows($pageId){
		return Mage::getModel('pagemanager/row')->getPageRows($pageId);
	}
	
	public function getPageItems($rowId){
		return Mage::getModel('pagemanager/item')->getPageitems($rowId);
	}

    /** Overridden to get access to pagemanager from other places.
     * @return string
     */
    protected function _toHtml()
    {
        if(!Mage::registry('current_pagemanager_page')){
            Mage::register('current_pagemanager_page', $this);
        }
        return parent::_toHtml();
    }
}

