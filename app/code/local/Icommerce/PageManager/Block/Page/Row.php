<?php

class Icommerce_PageManager_Block_Page_Row extends Mage_Adminhtml_Block_Widget_Container
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

        $this->_addButton('add_new', array(
            'label'   => Mage::helper('pagemanager')->__('Add New Row'),
            'onclick'   => 'window.location.href=\''.$this->getUrl('*/pagerow/add/').'\'',
            'class'   => 'add'
        ));
    }

    public function getRowUrl($row)
    {
    	return $this->getUrl('*/pagerow/edit/', array('id' => $row));
    }

 	public function getItemUrl($row, $itemType)
    {
    	return $this->getUrl('*/pageitem/edit'.$itemType."/", array('id' => $row));
    }

	public function getRows(){
		return Mage::getModel('pagemanager/row')->getRows($_SESSION['page_id']);
	}

	public function getItems($rowId){
		return Mage::getModel('pagemanager/item')->getItems($rowId);
	}
}

