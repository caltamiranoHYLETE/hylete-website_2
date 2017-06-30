<?php

class Ebizmarts_BakerlooRestful_Model_Adminhtml_System_Config_Source_Description {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 'description', 'label' => Mage::helper('adminhtml')->__('Description')),
            array('value' => 'short_description', 'label' => Mage::helper('adminhtml')->__('Short Description')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        return array(
            'description' => Mage::helper('adminhtml')->__('Description'),
            'short_description' => Mage::helper('adminhtml')->__('Short Description'),
        );
    }

}