<?php

class Ebizmarts_BakerlooRestful_Model_Adminhtml_System_Config_Source_CustomerAttributeOnline {

    private $_allowedTypes = array('text');
    private $_bannedCodes  = array(
                                    'default_shipping', 'default_billing', 'created_in', 'confirmation', 'vat_is_valid',
                                    'vat_request_id', 'vat_request_date', 'vat_request_success', 'vat_id'
                             );

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {

        $h = Mage::helper('bakerloo_restful');

        $options  = array();
        $options []= array('value' => '','label' => '');

        $customerAttributes = Mage::getModel('customer/customer')->getAttributes();
        $options []= array(
            'label' => $h->__('Customer Attributes'),
            'value' => $this->attributeValues($customerAttributes, '')
        );

        $attributesAddress = Mage::getModel('customer/address')->getAttributes();
        $options []= array(
            'label' => $h->__('Default Address Attributes'),
            'value' => $this->attributeValues($attributesAddress, 'address_')
        );

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

    private function attributeValues($attributes, $codePrefix) {
        $ret = array();

        foreach($attributes as $attribute) {

            if(in_array($attribute->getFrontendInput(), $this->_allowedTypes)
                and !in_array($attribute->getAttributeCode(), $this->_bannedCodes)
                and $attribute->getFrontendLabel()) {
                $ret[]= array(
                    'value' => $codePrefix . $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontendLabel(),
                );
            }
        }

        return $ret;
    }

}