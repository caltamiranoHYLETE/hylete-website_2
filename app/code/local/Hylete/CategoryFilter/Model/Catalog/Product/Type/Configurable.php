<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All Rights Reserved.
 */

/**
 * Configurable product type implementation
 *
 * This type builds in product attributes and existing simple products
 */
class Hylete_CategoryFilter_Model_Catalog_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Configurable
{
    /** @var Hylete_CategoryFilter_Helper_Data|Mage_Core_Helper_Abstract */
    private $helper;

    /** @var bool */
    private $isCacheable;

    /**
     * Retrieve subproducts identifiers
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getUsedProductIds($product = null)
    {
        if ($product === null || !$this->isCacheable()) {
            return parent::getUsedProductIds($product);
        }
        $cacheKey = $this->getCategoryFilterHelper()->getCacheKey(
            'USEDPRODUCTIDS',
            [
                'productid' => $product->getId(),
            ]
        );
        if ($cacheKey && $dataCached = $this->getCategoryFilterHelper()->getCache()->load($cacheKey)) {
            $usedProductIds = $this->getCategoryFilterHelper()->unserialize($dataCached);
            $this->getProduct($product)->setData($this->_usedProductIds, $usedProductIds);
        } else {
            if (!$this->getProduct($product)->hasData($this->_usedProductIds)) {
                $usedProductIds = array();
                foreach ($this->getUsedProducts(null, $product) as $_product) {
                    $usedProductIds[] = $_product->getId();
                }
                $this->getProduct($product)->setData($this->_usedProductIds, $usedProductIds);
            }
            try {
                $this->getCategoryFilterHelper()->getCache()->save(
                    $this->getCategoryFilterHelper()->serialize($usedProductIds),
                    $cacheKey,
                    [
                        Mage_Catalog_Model_Product::CACHE_TAG,
                        Mage_Catalog_Model_Category::CACHE_TAG,
                        Mage_Catalog_Model_Product_Type_Price::CACHE_TAG,
                    ]
                );
            } catch (Zend_Cache_Exception $e) {
                Mage::logException($e);
            }
        }

        return $this->getProduct($product)->getData($this->_usedProductIds);
    }

    /**
     * Retrieve array of "subproducts"
     *
     * @param  array
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getUsedProducts($requiredAttributeIds = null, $product = null)
    {
        Varien_Profiler::start('CONFIGURABLE:' . __METHOD__);
        if ($product === null || !$this->isCacheable()) {
            $usedProducts = parent::getUsedProducts($requiredAttributeIds, $product);
        } else {
            try {
                $requiredAttributeIdsKey = !is_array($requiredAttributeIds) ? [] : $requiredAttributeIds;
                $cacheKey = $this->getCategoryFilterHelper()->getCacheKey(
                    'USEDPRODUCTS',
                    [
                        'productid' => $product->getId(),
                        'requiredAttributeIdsKey' => implode($requiredAttributeIdsKey),
                    ]
                );
                if ($cacheKey && $dataCached = $this->getCategoryFilterHelper()->getCache()->load($cacheKey)) {
                    $usedProducts = $this->getCategoryFilterHelper()->unserialize($dataCached);
                } else {
                    $usedProducts = parent::getUsedProducts($requiredAttributeIds, $product);
                    $this->getCategoryFilterHelper()->getCache()->save(
                        $this->getCategoryFilterHelper()->serialize($usedProducts),
                        $cacheKey,
                        [
                            Mage_Catalog_Model_Product::CACHE_TAG,
                            Mage_Catalog_Model_Category::CACHE_TAG,
                            Mage_Catalog_Model_Product_Type_Price::CACHE_TAG
                        ]
                    );
                }
            } catch (Zend_Cache_Exception $e) {
                Mage::logException($e);
                $usedProducts = parent::getUsedProducts($requiredAttributeIds, $product);
            }
        }

        $this->getProduct($product)->setData($this->_usedProducts, $usedProducts);
        Varien_Profiler::stop('CONFIGURABLE:' . __METHOD__);

        return $this->getProduct($product)->getData($this->_usedProducts);
    }

    /**
     * Retrieve Category Filter Helper
     *
     * @return Hylete_CategoryFilter_Helper_Data|Mage_Core_Helper_Abstract
     */
    private function getCategoryFilterHelper()
    {
        if ($this->helper === null) {
            $this->helper = Mage::helper('categoryfilter');
        }

        return $this->helper;
    }

    /**
     * Check if should cache
     *
     * @return bool
     */
    private function isCacheable()
    {
        if ($this->isCacheable === null) {
            $this->isCacheable = true;
            try {
                if (Mage::app()->getStore()->isAdmin()) {
                    $this->isCacheable = false;
                }
            } catch (Mage_Core_Model_Store_Exception $e) {
                /** Since can't define the store, shouldn't be cached */
                $this->isCacheable = false;
            }
        }

        return $this->isCacheable;
    }
}
