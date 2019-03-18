<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All Rights Reserved.
 */

/**
 * Catalog Configurable Product Attribute Collection
 */
class Hylete_CategoryFilter_Model_Resource_Catalog_Product_Type_Configurable_Attribute_Collection extends Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
{
    /** @var Hylete_CategoryFilter_Helper_Data|Mage_Core_Helper_Abstract */
    private $helper;

    /** @var bool */
    private $isCacheable;

    /**
     * Add product attributes to collection items
     *
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
     * @throws Zend_Cache_Exception
     */
    protected function _addProductAttributes()
    {
        foreach ($this->_items as $item) {
            $cacheKey = $this->getCategoryFilterHelper()->getCacheKey(
                'ADDPRODUCTATTRIBUTES',
                [
                    'productid' => $item->getProductId(),
                    'attributeid' => $item->getAttributeId(),
                ]
            );

            $dataCached = false;
            if ($this->isCacheable()) {
                $dataCached = $this->getCategoryFilterHelper()->getCache()->load($cacheKey);
            }

            if ($dataCached) {
                $unserialized = $this->getCategoryFilterHelper()->unserialize($dataCached);
                $productAttribute = Mage::getModel('catalog/resource_eav_attribute')->setData($unserialized);
            } else {
                $productAttribute = $this->getProduct()->getTypeInstance(true)
                    ->getAttributeById($item->getAttributeId(), $this->getProduct());
                if ($this->isCacheable()) {
                    $this->getCategoryFilterHelper()->getCache()->save(
                        $this->getCategoryFilterHelper()->serialize($productAttribute->getData()),
                        $cacheKey,
                        [
                            Mage_Catalog_Model_Product::CACHE_TAG,
                            Mage_Catalog_Model_Category::CACHE_TAG,
                            Mage_Catalog_Model_Product_Type_Price::CACHE_TAG,
                        ]
                    );
                }
            }
            $item->setProductAttribute($productAttribute);
        }

        return $this;
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
