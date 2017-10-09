<?php
/**
 * @desc Observer for adding fixed prices attributes to products
 * Class Globale_BrowsingLight_Model_Observers_Product
 */
class Globale_BrowsingLite_Model_Observers_Product {

    /**
     * Observer for updating products collection with fixed prices attributes
     * Events => catalog_product_collection_load_after
     * @param Varien_Event_Observer $Observer
     * @access public
     */
    public function updateCollectionProductsFixedPrices(Varien_Event_Observer $Observer)
    {

        $Products = $Observer->getCollection();
		if(Mage::helper('core')->isModuleEnabled('Globale_FixedPrices')){
			/**@var $ProductModel Globale_FixedPrices_Model_Product */
			$ProductModel = Mage::getModel('globale_fixedprices/product');
			$ProductModel->updateProductsWithFixedPricesAttributes($Products);
		}

    }

    /** Observer for updating a product with fixed prices attributes
    * Events => catalog_product_view_config
    * @param Varien_Event_Observer $Observer
    * @access public
    */
    public function updateProductsFixedPrices(Varien_Event_Observer $Observer)
    {
        $Product = $Observer->getProduct();
        /**@var $ProductModel Globale_FixedPrices_Model_Product */
        $ProductModel = Mage::getModel('globale_fixedprices/product');
        $ProductModel->updateProductsWithFixedPricesAttributes(array($Product));
    }


}