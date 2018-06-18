<?php
/**
 * 
 * This class is required for extracting product attribute codes.
 * @category    Scommerce
 * @package     Scommerce_GoogleTagManagerPro
 * @author		Scommerce Mage (core@scommerce-mage.co.uk)
 */
 
class Scommerce_GoogleTagManagerPro_Model_Source_Brand_Dropdown
{
    public function toOptionArray()
    {
        $attributes = Mage::getModel('catalog/product')->getAttributes();
        $attributeArray = array(0 => array('label' => '', 'value' => ''));

        foreach($attributes as $a){
            foreach ($a->getEntityType()->getAttributeCodes() as $attributeName) {
                $attributeArray[] = array(
                    'label' => $attributeName,
                    'value' => $attributeName
                );
            }
            break;
        }

        return $attributeArray;
    }
}