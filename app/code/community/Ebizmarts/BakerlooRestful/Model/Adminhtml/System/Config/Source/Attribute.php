<?php

class Ebizmarts_BakerlooRestful_Model_Adminhtml_System_Config_Source_Attribute {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {

        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('backend_type',
                array(
                    array('eq' => 'text'),
                    array('eq' => 'varchar'),
                    array('eq' => 'int'),
                    array('eq' => 'decimal'),
                    array('eq' => 'datetime'),
                    array('eq' => 'static'),
        ))
        ->setOrder('frontend_label', 'ASC');

        $options = array();

        $options []= array('value' => '','label' => '');

        foreach($attributes as $attribute) {

            if($attribute->getFrontendLabel()) {
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