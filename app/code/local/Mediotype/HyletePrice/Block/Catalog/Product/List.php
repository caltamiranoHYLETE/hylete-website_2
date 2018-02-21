<?php

/**
 * Class Mediotype_HyletePrice_Block_Catalog_Product_List
 */
class Mediotype_HyletePrice_Block_Catalog_Product_List extends Vaimo_Carbon_Block_Catalog_Product_List
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
}
