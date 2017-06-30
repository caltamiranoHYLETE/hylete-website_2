<?php

class Icommerce_Style_Model_Product_Type_Style extends Mage_Catalog_Model_Product_Type_Abstract
{
    const TYPE_STYLE      = 'style';
    public function isSalable($product = null) {
        return true;
    }
    public function prepareForCart(Varien_Object $buyRequest, $product = null)
    {
        $product->getId();
    }

}

