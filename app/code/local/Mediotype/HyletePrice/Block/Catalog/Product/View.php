<?php

/**
 * Class Mediotype_HyletePrice_Block_Catalog_Product_View
 */
class Mediotype_HyletePrice_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View
{
    /**
     * @param Mage_Catalog_Model_Product $product
     * @param bool $displayMinimalPrice
     * @param string $idSuffix
     * @return string
     */
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '')
    {
        return parent::getPriceHtml($product, $displayMinimalPrice, $idSuffix);
    }

    /**
     * Determine whether the given product has an active special price.
     * @param  Mage_Catalog_Model_Product $product The product model.
     * @return boolean
     */
    public function hasSpecialPrice(Mage_Catalog_Model_Product $product)
    {
        return $this->helper('mediotype_hyleteprice')->hasSpecialPrice($product);
    }
}
