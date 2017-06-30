<?php

class Ebizmarts_BakerlooRestful_Model_Observer_Catalog
{

    const WEBSITES_REG_KEY = "bakerloorestful_product_original_websites";

    /**
     * Save data to custom table when product is deleted from Magento.
     *
     * Event: catalog_product_delete_after_done
     *
     * @param  Varien_Event_Observer $observer
     * @return Ebizmarts_BakerlooRestful_Model_Observer_Catalog
     */
    public function productDelete(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        if ($product->getId()) {

            $storeId = Mage::app()->getRequest()->getParam('store');

            $trash = Mage::getModel('bakerloo_restful/catalogtrash');
            $trash
                ->setProductId($product->getId())
                ->setStoreId($storeId)
                ->setAction('delete')
                ->setProductSku($product->getSku())
                ->setProductName($product->getName());

            $trash->save();
        }

        return $this;
    }

    /**
     * Process attributes mass update from Catalog > Products grid, unfortunately
     * the event is only available from Magento CE 1.6.0.0+.
     *
     * @param Varien_Event_Observer $observer
     */
    public function productAttributeUpdate(Varien_Event_Observer $observer) {

        /**
        Mage_Catalog_Model_Product_Action

        Mage::dispatchEvent('catalog_product_attribute_update_before', array(
        'attributes_data' => &$attrData,
        'product_ids'   => &$productIds,
        'store_id'      => &$storeId
        ));
         */

        $productIds = $observer->getEvent()->getProductIds();

        if (is_array($productIds)) {
            Mage::helper('bakerloo_restful')->updateProductDateByIds($productIds);
        }

    }

    /**
     * Process products website updates on attributes mass update from Catalog > Products grid, unfortunately
     * the event is only available from Magento CE 1.6.0.0+.
     *
     * @param Varien_Event_Observer $observer
     */
    public function productWebsiteUpdate(Varien_Event_Observer $observer){
        /**
        Mage_Catalog_Model_Product_Action

        'website_ids' => &$websiteIds,
        'product_ids'   => &$productIds,
        'action'      => &$type
         */

        $productIds = $observer->getProductIds();
        if(is_array($productIds))
            Mage::helper('bakerloo_restful')->updateProductDateByIds($productIds);


        $websiteIds = $observer->getWebsiteIds();
        $action = $observer->getAction();

        foreach($productIds as $_id){
            $product = Mage::getModel('catalog/product')->load($_id);

            if($product->getId()) {
                //if products removed from website, trash them
                if($action == 'remove')
                    $this->trashProduct($product, $websiteIds);

                elseif($action == 'add')
                    $this->undoTrash($product, $websiteIds);
            }
        }
    }

    /**
     * Process product save, compare assigned websites, if something changed, save to table.
     *
     * @param  Varien_Event_Observer $observer
     * @return Ebizmarts_BakerlooRestful_Model_Observer_Catalog
     */
    public function productSave(Varien_Event_Observer $observer)
    {

        $product = $observer->getEvent()->getProduct();

        $originalWebsiteIds = Mage::registry(self::WEBSITES_REG_KEY);
        $websiteIds = $product->getWebsiteIds();

        if (!is_array($originalWebsiteIds)) {
            $originalWebsiteIds = array();
        }

        //Search for remove products form websites
        $deleted = array_diff($originalWebsiteIds, $websiteIds);

        //If removed from any Website, save to local DB for replication
        if (is_array($deleted) && !empty($deleted))
            $this->trashProduct($product, $deleted);

        //Undo trash on saved website ids, in case they had been removed and re added.
        $this->undoTrash($product, $websiteIds);

        return $this;
    }

    /**
     * Save product to catalogtrash table for the specified websites.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $websites
     */
    public function trashProduct(Mage_Catalog_Model_Product $product, $websites = array()){

        foreach($websites as $websiteId){
            $websiteStores = Mage::app()->getWebsite($websiteId)->getStoreIds();

            if (is_array($websiteStores) && !empty($websiteStores)) {

                foreach ($websiteStores as $_stid) {
                    $trash = Mage::getModel('bakerloo_restful/catalogtrash');
                    $trash
                        ->setProductId($product->getId())
                        ->setStoreId($_stid)
                        ->setAction('remove_website')
                        ->setProductSku($product->getSku())
                        ->setProductName($product->getName());

                    $trash->save();
                }
            }
        }
    }

    /**
     * Remove product from catalogtrash table for the specified websites.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $websites
     */
    public function undoTrash(Mage_Catalog_Model_Product $product, $websites = array()){

        foreach($websites as $websiteId) {
            $websiteStores = Mage::app()->getWebsite($websiteId)->getStoreIds();

            if (is_array($websiteStores) && !empty($websiteStores)) {

                $rows = Mage::getModel('bakerloo_restful/catalogtrash')
                    ->getCollection()
                    ->addFieldToFilter('product_id', array('eq' => $product->getId()))
                    ->addFieldToFilter('action', array('eq' => 'remove_website'))
                    ->addFieldToFilter('store_id', array('in' => $websiteStores));

                foreach ($rows as $_row) {
                    $_row->delete();
                }
            }
        }
    }

    /**
     * Process products updated_at when added/removed from categories from category edit page.
     *
     * @param Varien_Event_Observer $observer
     * @return Ebizmarts_BakerlooRestful_Model_Observer_Catalog
     */
    public function productSaveFromCategory(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();

        $posted   = is_array($category->getPostedProducts()) ? $category->getPostedProducts() : array();
        $position = is_array($category->getProductsPosition()) ? $category->getProductsPosition() : array();

        $deleted = array_diff_key($position, $posted);
        $added   = array_diff_key($posted, $position);

        $toUpdate = array_merge(array_keys($deleted), array_keys($added));

        if (is_array($toUpdate) and !empty($toUpdate)) {
            Mage::helper('bakerloo_restful')->updateProductDateByIds($toUpdate);
        }

        return $this;
    }

    /**
     * Process product PRE save.
     *
     * @param  Varien_Event_Observer $observer
     * @return Ebizmarts_BakerlooRestful_Model_Observer_Catalog
     */
    public function productPreSave(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $request = $observer->getEvent()->getRequest();


        $_product = Mage::getModel('catalog/product')
            ->setStoreId($request->getParam('store', 0))
            ->load($product->getId());

        //Save DB websites on local object to compare afterwards
        if (Mage::registry(self::WEBSITES_REG_KEY)) {
            Mage::unregister(self::WEBSITES_REG_KEY);
        }
        Mage::register(self::WEBSITES_REG_KEY, $_product->getWebsiteIds());

        return $this;
    }

    /**
     * Monitor inventory when new order is placed.
     *
     * @param Varien_Event_Observer $observer
     * @return Ebizmarts_BakerlooRestful_Model_Observer_Catalog
     */
    public function inventoryNewOrder(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $items = $order->getAllItems();

        foreach ($items as $item) {

            if (($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) or ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)) {
                continue;
            }

            $stockItem = $item->getProduct()->getStockItem();
            if($stockItem instanceof Mage_CatalogInventory_Model_Stock_Item)
                $this->_saveInventoryChange($stockItem);

        }

        return $this;

    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * Update inventory when a POS order has returns.
     * (Don't update if stock is managed by Ebizmarts_Warehouse.)
     *
     */
    public function inventoryPosOrderReturns(Varien_Event_Observer $observer) {
        if(Mage::helper('core')->isModuleEnabled('Ebizmarts_Warehouse'))
            return;

        if(Mage::helper('bakerloo_restful')->dontCheckStock())
            return;

        $orderIncrementId = $observer->getOrderId();
        $order = Mage::getModel('sales/order')->load($orderIncrementId, 'increment_id');

        if($order->getId()) {
            $returnedItems = $observer->getReturnedItems();

            foreach($returnedItems as $return) {

                $product = Mage::getModel('catalog/product')->load($return['product_id']);

                if($product->getId()) {

                    $qty = abs($return['product_qty']);

                    $stockItem = $product->getStockItem();

                    $stockData = $stockItem->getData();
                    $minSaleQty = isset($stockData['min_sale_qty']) ? (float)$stockData['min_sale_qty'] : 0;
                    if ((!$product->getIsInStock()) and ($qty + $stockItem->getQty() >= $minSaleQty)
                        && Mage::helper('bakerloo_restful')->updateStockAvailability())
                        $stockItem->setIsInStock(1);

                    $stockItem->addQty($qty)
                        ->save();
                }
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return Ebizmarts_BakerlooRestful_Model_Observer_Catalog
     *
     * Update inventory stock status
     * (Don't update if stock is managed by Ebizmarts_Warehouse.)
     */
    public function inventoryBackToStock(Varien_Event_Observer $observer) {
        if(Mage::helper('core')->isModuleEnabled('Ebizmarts_Warehouse'))
            return;

        if(Mage::helper('bakerloo_restful')->dontCheckStock())
            return;

        $creditMemo = $observer->getEvent()->getCreditmemo();
        if(Mage::helper('bakerloo_restful')->updateStockAvailability()){

            foreach ($creditMemo->getAllItems() as $creditMemoItem) {
                $product = Mage::getModel('catalog/product')->load($creditMemoItem->getProductId());
                $stockItem = $product->getStockItem();
                if ($creditMemoItem->getBackToStock() && (!$product->getIsInStock())
                    && ($creditMemoItem->getQty() + $stockItem->getQty() >= $stockItem->getMinSaleQty())){
                    $stockItem->setQty($creditMemoItem->getQty() + $stockItem->getQty());
                    $stockItem->setIsInStock(1);
                    $stockItem->save();
                }
            }
        }

        return $this;
    }

    /**
     * Monitor inventory when order is refunded.
     *
     * @param Varien_Event_Observer $observer
     * @return Ebizmarts_BakerlooRestful_Model_Observer_Catalog
     */
    public function inventoryNewCreditMemo(Varien_Event_Observer $observer)
    {
        $creditMemo = $observer->getEvent()->getCreditmemo();

        $items = $creditMemo->getAllItems();
        foreach ($items as $item) {

            if ($item->getQty() == 0 or !$item->getBackToStock()) {
                continue;
            }

            $_product = Mage::getModel('catalog/product')->load($item->getProductId());

            $stockItem = $_product->getStockItem();
            if($stockItem instanceof Mage_CatalogInventory_Model_Stock_Item)
                $this->_saveInventoryChange($stockItem);
        }

        return $this;
    }

    /**
     * Monitor inventory when order item is canceled.
     *
     * @param Varien_Event_Observer $observer
     * @return Ebizmarts_BakerlooRestful_Model_Observer_Catalog
     */
    public function inventoryOrderItemCancel(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getItem();

        $_product = Mage::getModel('catalog/product')->load($item->getProductId());

        $stockItem = $_product->getStockItem();
        if($stockItem instanceof Mage_CatalogInventory_Model_Stock_Item)
            $this->_saveInventoryChange($stockItem);

        return $this;
    }

    public function inventoryUpdate(Varien_Event_Observer $observer) {
        $stockItem = $observer->getEvent()->getItem();

        $this->_saveInventoryChange($stockItem);
    }

    /**
     * Save inventory change to database so we are able to use deltas on inventory sync.
     *
     * @param Mage_CatalogInventory_Model_Stock_Item $stockItem
     */
    private function _saveInventoryChange(Mage_CatalogInventory_Model_Stock_Item $stockItem)
    {
        $stockItem->updateModifiedDate();
    }

    public function addAdvancedStockInventory(Varien_Event_Observer $observer) {
        $h = Mage::helper('bakerloo_restful');
        $enabled = $h->isModuleInstalled('MDN_AdvancedStock') and $h->isModuleEnabled('MDN_AdvancedStock');

        if ($enabled) {
            $product = $observer->getEvent()->getProduct();
            $websiteId = Mage::app()->getStore()->getWebsiteId();

            $stocks = Mage::helper('AdvancedStock/Product_Base')->getStocksForWebsiteAssignment($websiteId, MDN_AdvancedStock_Model_Assignment::_assignmentSales, $product->getId());

            if($stocks->getFirstItem()->getId())
                $observer->getStockItem()->load($stocks->getFirstItem()->getId());

        }

        return $this;
    }
    
}