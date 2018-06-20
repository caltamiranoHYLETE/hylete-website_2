<?php
/**
 *
 * @category    Scommerce
 * @package     Scommerce_GoogleTagManagerPro
 * @author		Scommerce Mage (core@scommerce-mage.co.uk)
 */
class Scommerce_GoogleTagManagerPro_Block_List extends Mage_Core_Block_Template
{
    public function getProductCollection()
    {
    	//skennerly@hylete.com: This reorders all of the products in the category to a default that ignores what has been selected.
		//return $this->getLayout()->getBlockSingleton('catalog/product_list')->getLoadedProductCollection();
       	return $this->getLoadedProductCollection();
    }
}