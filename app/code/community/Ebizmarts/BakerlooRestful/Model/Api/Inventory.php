<?php

class Ebizmarts_BakerlooRestful_Model_Api_Inventory extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model   = 'cataloginventory/stock_item';
    public $defaultSort = "main_table.updated_at";

    public function getPageSize() {
        return parent::getSafePageSize();
    }

    protected function _getIndexId() {
        return 'product_id';
    }

    /**
     * Process GET requests.
     *
     * @return array
     * @throws Exception
     */
    public function get() {

        $this->checkGetPermissions();

        $identifier = $this->_getIdentifier();

        if($identifier) { //get item by id
            $helper = Mage::helper('bakerloo_restful');

            if(is_numeric($identifier)) {
                $product = Mage::getModel('catalog/product')->load($identifier);
                if (false === $helper->productIsInStore($product, $this->getStoreId()))
                    Mage::throwException($helper->__('Product does not exist in store.'));

                return $this->_createDataObject((int)$identifier, $product);
            }
            else
                throw new Exception('Incorrect request.');
        }
        else {

            //get page
            $page = $this->_getQueryParameter('page');
            if(!$page) {
                $page = 1;
            }

            $filters     = $this->_getQueryParameter('filters');
            $resultArray = $this->_getAllItems($page, $filters);

            return $resultArray;

        }

    }

    public function _createDataObject($id = null, $data = null) {

        Mage::app()->setCurrentStore($this->getStoreId());

        $result = new Varien_Object;

        $stockItem = Mage::getModel($this->_model)->load($id, 'product_id');
        $product   = is_null($data) ? Mage::getModel('catalog/product')->load($stockItem->getProductId()) : $data;

        if($stockItem->getId()) {
            $stockData = clone $stockItem;

            $stockData->setMinimumSaleQty( $this->_getMinSaleQtyAllCustomerGroups($stockData) );

            if( ((int)Mage::helper('bakerloo_restful')->config('catalog/allow_backorders')) ) {
                $stockData->setBackorders(1);
                if( !Mage::registry(Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES) )
                    Mage::register(Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES, true);
            }

            Mage::dispatchEvent("pos_get_inventory", array("product" => $product, "stock_item" => $stockData));

            $updatedAt = $stockData->getUpdatedAt();
            if($stockData->getUpdatedAt() == '0000-00-00 00:00:00' or is_null($updatedAt)) {
                $updatedAt = '0001-01-01 00:00:00';
            }


            $result = array(
                'backorders'              => (int)$stockData->getBackorders(),
                'enable_qty_increments'   => (int)$stockData->getEnableQtyIncrements(),
                'is_qty_decimal'          => (int)$stockData->getIsQtyDecimal(),
                'is_in_stock'             => (int)$stockData->getIsInStock(),
                'manage_stock'            => (int)$stockData->getManageStock(),
                'manage_stock_use_config' => (int)$stockData->getUseConfigManageStock(),
                'product_id'              => (int)$stockData->getProductId(),
                'qty'                     => (is_null($stockData->getQty()) ? 0.0000 : $stockData->getQty()),
                'qty_increments'          => ($stockData->getQtyIncrements() === false ? 0.0000 : $stockData->getQtyIncrements()),
                'store_id'                => $stockData->getStoreId(),
                'updated_at'              => $updatedAt,
                'min_sale_qty'            => $stockData->getMinimumSaleQty(),
                'max_sale_qty'            => $stockData->getMaxSaleQty()
            );

        }

        return $result;
    }

    /**
     * Retrieve inventory data for a given array of product ids.
     *
     */
    public function multiple() {
        $ids = explode(",", $this->_getQueryParameter('products'));

        $result = array();

        if(is_array($ids) && !empty($ids)) {
            $nrOfIds = count($ids);
            for($i = 0; $i < $nrOfIds; $i++) {
                $data = $this->_createDataObject($ids[$i]);

                if(is_array($data) && !empty($data)) {
                    $result[] = $data;
                }

            }
        }

        return $result;
    }

    /**
     * Update inventory for a given product.
     *
     * @return array
     */
    public function put() {
        parent::put();

        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload();

        $productId = (isset($data->product_id) ? ((int)$data->product_id) : null);

        $product = Mage::getModel('catalog/product')->setStoreId($this->getStoreId())->load($productId);

        $oldData = clone $product->getStockItem();

        if($product->getId()) {
            $product->getStockItem()
            ->setQty($data->qty)
            ->setIsInStock($data->is_in_stock)
            ->setManageStock($data->manage_stock)
            ->save();

            Mage::dispatchEvent("pos_update_inventory", array("product" => $product, "old_stock_item" => $oldData, "new_stock_item" => $product->getStockItem()));

        }
        else {
            Mage::throwException('Product does not exist.');
        }

        return $this->_createDataObject($product->getId());
    }

    /**
     * Return stock data min qty.
     *
     * @param $stockData object CatalogInventory object
     * @return array|float
     */
    private function _getMinSaleQtyAllCustomerGroups($stockData) {

        $allGroups = array('customer_group_id' => Mage_Customer_Model_Group::CUST_GROUP_ALL, 'customer_group_code' => 'ALL GROUPS');

        if((int)$stockData->getUseConfigMinSaleQty() === 0) {
            $allGroups['min_sale_qty'] = (float)$stockData->getMinSaleQty();

            return array($allGroups);
        }

        $allCustomerGroups = Mage::getModel('customer/group')->getCollection();
        $allCustomerGroupsArray = $allCustomerGroups->toArray();
        $allCustomerGroupsArray = $allCustomerGroupsArray['items'];

        array_push($allCustomerGroupsArray, $allGroups);

        $allGroupsCount = count($allCustomerGroupsArray);
        for ($i=0; $i < $allGroupsCount; $i++) {
            $_configQty = Mage::helper('cataloginventory/minsaleqty')->getConfigValue($allCustomerGroupsArray[$i]['customer_group_id']);

            $allCustomerGroupsArray[$i]['customer_group_id'] = (int)$allCustomerGroupsArray[$i]['customer_group_id'];

            if(isset($allCustomerGroupsArray[$i]['tax_class_id']))
                unset($allCustomerGroupsArray[$i]['tax_class_id']);

            if(isset($allCustomerGroupsArray[$i]['bakerloo_payment_methods']))
                unset($allCustomerGroupsArray[$i]['bakerloo_payment_methods']);

            if(empty($_configQty))
                unset($allCustomerGroupsArray[$i]);
            else
                $allCustomerGroupsArray[$i]['min_sale_qty'] = (float)$_configQty;
        }

        return array_values($allCustomerGroupsArray);
    }
}