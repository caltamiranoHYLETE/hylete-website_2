<?php
/**
 * Observer for products.
 * Class Globale_Base_Model_Observers_Product
 */
class Globale_Base_Model_Observers_Product {
    
    /**
     * Will be called after product saved(event: catalog_product_save_after).
     * @param Varien_Event_Observer $Observer
     */
    public function saveProductsList(Varien_Event_Observer $Observer) {

        /** @var GlobalE\SDK\SDK $GlobaleSDK */
        $GlobaleSDK = Mage::registry('globale_sdk');

        /** @var Globale_Base_Model_Product $ProductModel */
        $ProductModel = Mage::getModel('globale_base/product');
        $CommonProducts = $ProductModel->createProductCommonData(array($Observer->getProduct()));
        $GlobaleSDK->Admin()->SaveProductsList($CommonProducts);
    }

    /**
     * Will be called after product saved(event: catalog_product_attribute_update_after).
     * @param Varien_Event_Observer $Observer
     */
    public function massSaveProductsList(Varien_Event_Observer $Observer) {

        /** @var GlobalE\SDK\SDK $GlobaleSDK */
        $GlobaleSDK = Mage::registry('globale_sdk');

        /** @var Globale_Base_Model_Product $ProductModel */
        $ProductModel = Mage::getModel('globale_base/product');
        $ProductsIds = $Observer->getProductIds();
        $Products = Mage::getModel('catalog/product')->
                            getCollection()->
                            addAttributeToSelect('*')->
                            addAttributeToFilter('entity_id', array('in' => $ProductsIds));

        $CommonProducts = $ProductModel->createProductCommonData($Products);
        $GlobaleSDK->Admin()->SaveProductsList($CommonProducts);
    }
}