<?php

class Icommerce_Style_Model_Product_Type extends Mage_Catalog_Model_Product_Type_Abstract
{
    protected $_isComposite = true;
    protected $_canConfigure                = true;

    /**
     * Return relation info about used products
     *
     * @return Varien_Object Object with information data
     */
    public function getRelationInfo()
    {
        $info = new Varien_Object();
        $info->setTable('bundle/selection')
            ->setParentFieldName('parent_product_id')
            ->setChildFieldName('product_id');
        return $info;
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param bool $required
     * @return array
     */
    public function getChildrenIds($parentId, $required = true)
    {
        return Mage::getResourceSingleton('bundle/selection')
            ->getChildrenIds($parentId, $required);
    }

    /**
     * Retrieve parent ids array by requered child
     *
     * @param int|array $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        return Mage::getResourceSingleton('bundle/selection')
            ->getParentIdsByChild($childId);

    }


    /**
     * Prepare Quote Item Quantity
     *
     * @param mixed $qty
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function prepareQuoteItemQty($qty, $product = null)
    {
        return intval($qty);
    }

    /**
     * Checking if we can sale this bundle
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSalable($product = null)
    {
        $salable = parent::isSalable($product);
        if (!is_null($salable)) {
            return true;
            return $salable;
        }

        return true;
    }


    /**
     * Retrieve bundle selections collection based on ids
     *
     * @param array $selectionIds
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Bundle_Model_Mysql4_Selection_Collection
     */
    public function getSelectionsByIds($selectionIds, $product = null)
    {
        sort($selectionIds);

        $usedSelections     = $this->getProduct($product)->getData($this->_keyUsedSelections);
        $usedSelectionsIds  = $this->getProduct($product)->getData($this->_keyUsedSelectionsIds);

        if (!$usedSelections || serialize($usedSelectionsIds) != serialize($selectionIds)) {
            $storeId = $this->getProduct($product)->getStoreId();
            $usedSelections = Mage::getResourceModel('bundle/selection_collection')
                ->addAttributeToSelect('*')
                ->setFlag('require_stock_items', true)
                ->addStoreFilter($this->getStoreFilter($product))
                ->setStoreId($storeId)
                ->setPositionOrder()
                ->addFilterByRequiredOptions()
                ->setSelectionIdsFilter($selectionIds);

                if (!Mage::helper('catalog')->isPriceGlobal() && $storeId) {
                    $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
                    $usedSelections->joinPrices($websiteId);
                }
            $this->getProduct($product)->setData($this->_keyUsedSelections, $usedSelections);
            $this->getProduct($product)->setData($this->_keyUsedSelectionsIds, $selectionIds);
        }
        return $usedSelections;
    }
    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then prepare of bundle selections options.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);
    }


}
