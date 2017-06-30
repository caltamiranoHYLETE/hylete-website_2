<?php

class Ebizmarts_BakerlooRestful_Model_Adminhtml_System_Config_Source_Searchableattribute {

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
                    array('eq' => 'int'),
                    array('eq' => 'static'),
                    array('eq' => 'varchar')
        ))
        ->setOrder('frontend_label', 'ASC');

        $options = array();

        array_push($options, array('value' => '','label' => ''));

        foreach($attributes as $attribute) {

            if(!$attribute->getFrontendLabel())
                continue;

            $option = array(
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel(),
            );
            if(!is_null($option['label']) && ($option['value'] !== 'sku') && ($option['value'] !== 'name'))
                $options [] = $option;
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