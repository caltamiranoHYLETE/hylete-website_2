<?php

class Ebizmarts_BakerlooShipping_Model_Adminhtml_System_Config_Source_Renderoptions {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {

        return array(
            array('value' => 'select', 'label' => Mage::helper('bakerloo_restful')->__('Drop Down')),
            array('value' => 'radio',  'label' => Mage::helper('bakerloo_restful')->__('Radio Buttons')),
        );

    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        return array();
    }

}