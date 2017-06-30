<?php

class Icommerce_PageManager_Block_Page_Row_Item_Copyimagewithoverlay extends Mage_Adminhtml_Block_Widget_Container
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
        
        $this->_addButton('add_save', array(
            'label'   => Mage::helper('pagemanager')->__('Copy'),
            'onclick' => 'addimagewithoverlayform.submit()',
            'class'   => 'copy'
        ));
    }
    
    public function getRows(){
		return Mage::getModel('pagemanager/row')->getRows($_SESSION['page_id']);
	}
    
    public function getItemType()
    {
        return array('value'=>'image', 'label'=>Mage::helper('pagemanager')->__('Image'));
    }
    
}

