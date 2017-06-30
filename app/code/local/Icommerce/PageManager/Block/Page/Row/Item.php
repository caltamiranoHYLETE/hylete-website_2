<?php

class Icommerce_PageManager_Block_Page_Row_Item extends Mage_Adminhtml_Block_Widget_Container
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
    	$helper = Mage::helper('pagemanager');
    	
    	if($helper->isHtmlActive()){
	    	$this->_addButton('add_new_html', array(
	            'label'   => Mage::helper('pagemanager')->__('Add New HTML block'),
	            'onclick'   => 'window.location.href=\''.$this->getUrl('*/pageitem/addhtml/').'\'',
	            'class'   => 'add'
	        ));
        }
        $this->_addButton('add_new', array(
            'label'   => Mage::helper('pagemanager')->__('Add New Image'),
            'onclick'   => 'window.location.href=\''.$this->getUrl('*/pageitem/add/').'\'',
            'class'   => 'add'
        ));     
    }
    
    public function getItemUrl($row, $itemType)
    {
    	return $this->getUrl('*/pageitem/edit'.$itemType."/", array('id' => $row));
    }
	
	public function getItems(){
		return Mage::getModel('pagemanager/item')->getItems($_SESSION['page_id']);
	}
}

