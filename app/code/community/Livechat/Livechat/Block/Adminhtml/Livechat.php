<?php
class Livechat_Livechat_Block_Adminhtml_Livechat extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  
  public function __construct()
  {
    $this->_controller = 'adminhtml_livechat';
    $this->_blockGroup = 'livechat';
    $this->_headerText = Mage::helper('livechat')->__('LiveChat Configuration');
        
    parent::__construct();
    $this->_removeButton('add');
  }
}