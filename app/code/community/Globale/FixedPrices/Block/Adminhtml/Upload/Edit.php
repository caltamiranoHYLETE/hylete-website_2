<?php
class Globale_FixedPrices_Block_Adminhtml_Upload_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct()
    {
        $this->_objectId = 'fixedprices';
        $this->_controller = 'adminhtml_upload';
        $this->_blockGroup = 'globale_fixedprices';

        parent::__construct();
    }

    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('core')->__('Mass Update Global-e Fixed Prices');
    }


}