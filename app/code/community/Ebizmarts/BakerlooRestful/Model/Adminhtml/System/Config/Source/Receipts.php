<?php

class Ebizmarts_BakerlooRestful_Model_Adminhtml_System_Config_Source_Receipts {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 'magento', 'label' => Mage::helper('adminhtml')->__('Magento Template only')),
            array('value' => 'receipt', 'label' => Mage::helper('adminhtml')->__('POS Receipt only')),
            array('value' => 'both',    'label' => Mage::helper('adminhtml')->__('Magento Template & POS Receipt')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        return array(
            'magento' => Mage::helper('adminhtml')->__('Magento Template only'),
            'receipt' => Mage::helper('adminhtml')->__('POS Receipt only'),
            'both'    => Mage::helper('adminhtml')->__('Magento Template & POS Receipt'),
        );
    }

}