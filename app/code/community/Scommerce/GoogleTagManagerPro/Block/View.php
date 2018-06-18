<?php
/**
 *
 * @category    Scommerce
 * @package     Scommerce_GoogleTagManagerPro
 * @author		Scommerce Mage (core@scommerce-mage.co.uk)
 */
class Scommerce_GoogleTagManagerPro_Block_View extends Mage_Core_Block_Template
{
    public function getProduct()
    {
        return Mage::registry('product');
    }

    public function getProducts($_productIds)
    {
        return Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect(array('name','sku','price'))
            ->addAttributeToFilter('entity_id',array('in' => $_productIds))
            ->addUrlRewrite();
    }
}