<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

class Hylete_AdminCategoryProductFilter_Model_Attribute
{
    /** @var array|null */
    private $optionsArray;

    /** @var array|null */
    private $attributeModel;

    /**
     * Retrieve Attribute Options
     *
     * @param $attributeName
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getAttributeOptions($attributeName)
    {
        if (empty($this->optionsArray[$attributeName])) {
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeName);
            $allOptions_array = $attribute->getSource()->getAllOptions(true, true);
            foreach ($allOptions_array as $instance) {
                if ($instance['value'] !== '') {
                    $this->optionsArray[$attributeName][$instance['value']] = $instance['label'];
                }
            }
        }

        return $this->optionsArray[$attributeName];
    }

    /**
     * Retrieve attribute
     *
     * @param $attributeCode
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getAttribute($attributeCode)
    {
        if (empty($this->attributeModel[$attributeCode])) {
            $this->attributeModel[$attributeCode] = Mage::getModel('eav/config')
                ->getAttribute('catalog_product', $attributeCode);
        }
        return $this->attributeModel[$attributeCode];
    }
}
