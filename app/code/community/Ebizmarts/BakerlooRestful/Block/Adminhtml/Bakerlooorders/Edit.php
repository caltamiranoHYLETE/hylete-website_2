<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Bakerlooorders_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId   = 'id';
        $this->_controller = 'adminhtml_bakerlooorders';
        $this->_blockGroup = 'bakerloo_restful';

        parent::__construct();

        if(!$this->getBakerlooOrder()->getOrderId()) {
            $this->_addButton('retry', array(
                'label'     => Mage::helper('bakerloo_restful')->__('Try Again'),
                'class'     => 'add',
                'onclick'   => 'TryAgain(this, \'' . $this->getRetryUrl() . '\'); return false;',
            ));
        }
        else {
            $this->_removeButton('delete');
        }
    }

    public function getHeaderText() {
        if ($this->getBakerlooOrder()->getId()) {
            return Mage::helper('bakerloo_restful')->__("Edit Order #%s", $this->getBakerlooOrder()->getId());
        }
        else {
            return Mage::helper('bakerloo_restful')->__('New Order');
        }
    }

    /**
     * Get object from registry.
     *
     * @return Ebizmarts_BakerlooRestful_Model_Order
     */
    public function getBakerlooOrder() {
        return Mage::registry('bakerlooorder');
    }

    public function getRetryUrl() {
        return $this->getUrl('adminhtml/bakerlooorders/place', array('id' => $this->getRequest()->getParam($this->_objectId)));
    }

}