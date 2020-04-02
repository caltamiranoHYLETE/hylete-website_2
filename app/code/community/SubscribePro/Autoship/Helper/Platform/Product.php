<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

class SubscribePro_Autoship_Helper_Platform_Product extends SubscribePro_Autoship_Helper_Platform_Abstract
{

    private $products = array();


    /**
     * Retrieve product profile info from platform (eligible intervals, min & max qty, discount, etc)
     *
     * @param string $sku
     * @return bool|\SubscribePro\Service\Product\ProductInterface Returns false or the product
     */
    public function getPlatformProductBySku($sku)
    {
        // Do API query by SKU for product info
        return $this->fetchProductBySku($sku);
    }

    /**
     * Retrieve subscription product profile info from platform (eligible intervals, min & max qty, discount, etc)
     *
     * @param Mage_Catalog_Model_Product $product Magento product object
     * @return bool|\SubscribePro\Service\Product\ProductInterface Returns false or the product
     */
    public function getPlatformProduct(Mage_Catalog_Model_Product $product)
    {
        /** @var SubscribePro_Autoship_Helper_Product $productHelper */
        $productHelper = Mage::helper('autoship/product');

        // Lookup whether product enabled / disabled
        $isProductEnabled = $productHelper->isAvailableForSubscription(
            $product,
            $this->getApiHelper()->getConfigStore(),
            true
        );
        if (!$isProductEnabled) {
            return false;
        }
        else {
            // Do API query by SKU for product info
            return $this->fetchProductBySku($product->getSku());
        }
    }

    /**
     * Create or update product info on the platform
     *
     * @param Mage_Catalog_Model_Product $product Magento product object
     * @param null|integer|Mage_Core_Model_Store $store
     * @param bool $onlyUpdateSubscriptionEnabledProducts If true, only make API calls to platform for subscription enabled products
     * @return bool|\SubscribePro\Service\Product\ProductInterface Returns false or the product
     */
    public function createOrUpdatePlatformProduct(Mage_Catalog_Model_Product $product, $store = null, $onlyUpdateSubscriptionEnabledProducts = true)
    {
        /** @var SubscribePro_Autoship_Helper_Product $productHelper */
        $productHelper = Mage::helper('autoship/product');

        // Set store on api helper
        if($store != null) {
            $this->getApiHelper()->setConfigStore($store);
        }

        // Reload product for this specific store
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')->setData('store_id', $store->getId())->load($product->getId());

        // Don't allow grouped product to be enabled for subscription
        // Otherwise don't check product types or check for options here
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            return false;
        }

        // Lookup whether product enabled / disabled
        if ($onlyUpdateSubscriptionEnabledProducts && !$productHelper->isAvailableForSubscription($product, $store, false)) {
            return false;
        }

        // Do API query by SKU for product info
        $platformProduct = $this->fetchProductBySku($product->getSku());
        // Check if product found
        if (!$platformProduct instanceof \SubscribePro\Service\Product\ProductInterface) {
            // Didn't find product matching SKU
            // Lets add this product via API
            $platformProduct = $this->getProductService()->createProduct();
            $platformProduct->setSku($product->getSku());
        }

        // Map data to product
        $platformProduct->setName($product->getName());
        $platformProduct->setPrice((string) $product->getPrice());

        // Save product
        $platformProduct = $this->getProductService()->saveProduct($platformProduct);

        // Return product
        return $platformProduct;
    }

    /**
     * Handle catalog_product_save_after Event and update product profile in DB and on platform
     *
     * @param Mage_Catalog_Model_Product $product Magento product object
     */
    public function handleOnSaveProduct(Mage_Catalog_Model_Product $product)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Helper_Platform::handleOnSaveProduct', Zend_Log::INFO);
        SubscribePro_Autoship::log('Product SKU: ' . $product->getSku(), Zend_Log::INFO);

        // Get ref to core session object
        /** @var Mage_Core_Model_Session $coreSession */
        $coreSession = Mage::getSingleton('core/session');

        // Get website ids for websites product is assigned to
        $productWebsites = $product->getWebsiteIds();

        // Iterate all websites / stores
        $websites = Mage::app()->getWebsites(false);
        /** @var Mage_Core_Model_Website $website */
        foreach ($websites as $website) {
            SubscribePro_Autoship::log('Website ID: ' . $website->getId() . ' code: ' . $website->getCode(), Zend_Log::INFO);
            $store = $website->getDefaultStore();
            if (!$store instanceof Mage_Core_Model_Store) {
                SubscribePro_Autoship::log('No default store for website!', Zend_Log::ERR);
                continue;
            }
            SubscribePro_Autoship::log('Website default store code: ' . $store->getCode(), Zend_Log::INFO);
            SubscribePro_Autoship::log('Subscription features enabled: ' . Mage::getStoreConfig('autoship_general/general/enabled', $store),
                Zend_Log::INFO);
            // Check configuration for this store
            if (Mage::getStoreConfig('autoship_general/general/enabled', $store) == '1') {
                // Check if product is assigned to website
                $productAssignedToWebsite = in_array($website->getId(), $productWebsites);
                SubscribePro_Autoship::log('Product is assigned to website: ' . $productAssignedToWebsite, Zend_Log::INFO);
                if ($productAssignedToWebsite) {
                    try {
                        $this->createOrUpdatePlatformProduct($product, $store);
                    }
                    catch (Exception $e) {
                        SubscribePro_Autoship::log('Failed to update product on platform with error: ' . $e->getMessage(), Zend_Log::ERR);
                        $coreSession->addError($this->__('Failed to update product on platform!'));
                    }
                }
            }
        }
    }

    /**
     * Fetch single product from Subscribe Pro platform by SKU.
     * Use cache if enabled.
     * Even if cache is disabled, store product in member so its only fetched once per request.
     *
     * @param $sku
     * @return bool|\SubscribePro\Service\Product\ProductInterface Returns false or the product
     */
    public function fetchProductBySku($sku)
    {
        if (isset($this->products[$sku])) {
            return $this->products[$sku];
        }
        else {
            // Lookup product from cache
            $product = $this->getCacheHelper()->loadCache(
                $this->getProductSkuCacheKey($sku),
                SubscribePro_Autoship_Helper_Cache::CACHE_TYPE_PRODUCTS);

            // Check if product found in cache
            if ($product !== false) {
                $product = unserialize($product);
            }
            else {
                // Product not found in cache
                // Request product from API
                $products = $this->getApiHelper()->getSdk()->getProductService()->loadProducts(array('sku' => $sku));
                if (count($products) != 1) {
                    return false;
                }
                $product = $products[0];
                // Save product in cache
                $this->getCacheHelper()->saveCache(
                    serialize($product),
                    $this->getProductSkuCacheKey($sku),
                    SubscribePro_Autoship_Helper_Cache::CACHE_TYPE_PRODUCTS);
            }

            // Save in member
            $this->products[$sku] = $product;

            // Return product
            return $product;
        }
    }

    /**
     * @param string $sku
     * @return string Cache key
     */
    protected function getProductSkuCacheKey($sku)
    {
        return 'autoship_api_product_sku_' . $sku;
    }

}
