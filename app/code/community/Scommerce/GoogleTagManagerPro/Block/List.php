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
       return $this->getLayout()->getBlockSingleton('catalog/product_list')->getLoadedProductCollection();
    }
}