<?php

class Ebizmarts_BakerlooRestful_Model_Adminhtml_System_Config_Source_CustomerEditableAttribute {

    private $_allowedTypes = array('text', 'date');
    private $_bannedCodes  = array('firstname', 'lastname', 'email', 'default_shipping', 'default_billing');

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {

        $attributes = Mage::getModel('customer/customer')
            ->getAttributes();

        $options = array();

        $options []= array('value' => '','label' => '');

        foreach($attributes as $attribute) {

            //@TODO: multiselect, select, boolean coming soon.

            if( in_array($attribute->getFrontendInput(), $this->_allowedTypes)
                and !in_array($attribute->getAttributeCode(), $this->_bannedCodes)
                and $attribute->getFrontendLabel()) {
                $options []= array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontendLabel(),
                );
            }
        }

        return $options;
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