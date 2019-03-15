<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All Rights Reserved.
 */
class Hylete_CategoryFilter_Model_Catalog_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Configurable
{
    /** @var Hylete_CategoryFilter_Helper_Data|Mage_Core_Helper_Abstract */
    private $helper;

    /** @var bool */
    private $isAdmin;

    /**
     * Retrieve subproducts identifiers
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getUsedProductIds($product = null)
    {
        $cacheKey = $this->getCategoryFilterHelper()->getCacheKey(
            'USEDPRODUCTIDS',
            [
                'productid' => $product->getId(),
            ]
        );
        if ($cacheKey !== null && !$this->isAdmin()) {
            $dataCached = $this->getCategoryFilterHelper()->getCache()->load($cacheKey);
            if ($dataCached) {
             //   Mage::log($cacheKey . '- CACHED');
                $usedProductIds = $this->getCategoryFilterHelper()->unserialize($dataCached);
                $this->getProduct($product)->setData($this->_usedProductIds, $usedProductIds);
            }
        }

        if (!$this->getProduct($product)->hasData($this->_usedProductIds)) {
            $usedProductIds = [];
            foreach ($this->getUsedProducts(null, $product) as $_product) {
                $usedProductIds[] = $_product->getId();
            }
            try {
                $this->getCategoryFilterHelper()->getCache()->save(
                    $this->getCategoryFilterHelper()->serialize($usedProductIds),
                    $cacheKey,
                    [
                        Mage_Catalog_Model_Product::CACHE_TAG,
                        Mage_Catalog_Model_Category::CACHE_TAG,
                        Mage_Catalog_Model_Product_Type_Price::CACHE_TAG
                    ]
                );
            } catch (Zend_Cache_Exception $e) {
                Mage::logException($e);
            }

            $this->getProduct($product)->setData($this->_usedProductIds, $usedProductIds);
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
        if (!$this->getProduct($product)->hasData($this->_usedProducts)) {
            if ($requiredAttributeIds === null
                && $this->getProduct($product)->getData($this->_configurableAttributes) === null) {
                // If used products load before attributes, we will load attributes.
                $this->getConfigurableAttributes($product);
                // After attributes loading products loaded too.
                Varien_Profiler::stop('CONFIGURABLE:' . __METHOD__);

                return $this->getProduct($product)->getData($this->_usedProducts);
            }
            $requiredAttributeIdsKey = !is_array($requiredAttributeIds) ? [] : $requiredAttributeIds;

            $cacheKey = $this->getCategoryFilterHelper()->getCacheKey(
                'USEDPRODUCTS',
                [
                    'productid' => $product->getId(),
                    'requiredAttributeIdsKey' => implode($requiredAttributeIdsKey),
                ]
            );
            if ($cacheKey !== null && !$this->isAdmin()) {
                $dataCached = $this->getCategoryFilterHelper()->getCache()->load($cacheKey);
                if ($dataCached) {
               //     Mage::log($cacheKey . '- CACHED');
                    $usedProducts = $this->getCategoryFilterHelper()->unserialize($dataCached);
                    $this->getProduct($product)->setData($this->_usedProducts, $usedProducts);

                    return $this->getProduct($product)->getData($this->_usedProducts);
                }
            }

            $usedProducts = [];
            $collection = $this->getUsedProductCollection($product)
                ->addFilterByRequiredOptions();

            // Provides a mechanism for attaching additional attributes to the children of configurable products
            // Will primarily have affect on the configurable product view page
            $childAttributes = Mage::getConfig()->getNode(self::XML_PATH_PRODUCT_CONFIGURABLE_CHILD_ATTRIBUTES);

            if ($childAttributes) {
                $childAttributes = $childAttributes->asArray();
                $childAttributes = array_keys($childAttributes);

                $collection->addAttributeToSelect($childAttributes);
            }

            if (is_array($requiredAttributeIds)) {
                foreach ($requiredAttributeIds as $attributeId) {
                    $attribute = $this->getAttributeById($attributeId, $product);
                    if ($attribute !== null) {
                        $collection->addAttributeToFilter($attribute->getAttributeCode(), ['notnull' => 1]);
                    }
                }
            }

            foreach ($collection as $item) {
                $usedProducts[] = $item;
            }
            if (!$this->isAdmin()) {
                try {
                    $this->getCategoryFilterHelper()->getCache()->save(
                        $this->getCategoryFilterHelper()->serialize($usedProducts),
                        $cacheKey,
                        [
                            Mage_Catalog_Model_Product::CACHE_TAG,
                            Mage_Catalog_Model_Category::CACHE_TAG,
                            Mage_Catalog_Model_Product_Type_Price::CACHE_TAG
                        ]
                    );
                  //  Mage::log($cacheKey . '- NOT CACHED');
                } catch (Zend_Cache_Exception $e) {
                    Mage::logException($e);
                }
            }
            $this->getProduct($product)->setData($this->_usedProducts, $usedProducts);
        }
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
     * Check if current store is admin
     *
     * @return bool
     */
    private function isAdmin()
    {
        if ($this->isAdmin === null) {
            try {
                $this->isAdmin = Mage::app()->getStore()->isAdmin();
            } catch (Mage_Core_Model_Store_Exception $e) {
                /** returning true avoid cache */
                return true;
            }
        }

        return $this->isAdmin;
    }
}
