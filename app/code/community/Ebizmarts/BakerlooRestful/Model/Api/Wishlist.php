<?php

class Ebizmarts_BakerlooRestful_Model_Api_Wishlist extends Ebizmarts_BakerlooRestful_Model_Api_Api {
    protected $_model = "wishlist/wishlist";

    protected function _getIndexId() {
        return 'wishlist_id';
    }

    public function _createDataObject($id = null, $data = null) {
        $result = array();

        if(is_null($data))
            $wishlist = Mage::getModel($this->_model)->load($id);
        else
            $wishlist = $data;

        if($wishlist->getId()){
            $result["wishlist_id"] = (int)$wishlist->getId();
            $result["customer_id"] = (int)$wishlist->getCustomerId();

            $items = $wishlist->getItemCollection();
            foreach($items as $_item){
                $result["wishlist_items"][] = array(
                    "wishlist_item_id" => (int)$_item->getId(),
                    "product_id" => (int)$_item->getProductId(),
                    "qty" => (int)$_item->getQty()
                );
            }
        }

        return $this->returnDataObject($result);
    }

    public function addToWishlist(){
        $h = Mage::helper('bakerloo_restful');

        if(!$this->getStoreId())
            Mage::throwException($h->__('Please provide a Store ID.'));
        Mage::app()->setCurrentStore($this->getStoreId());

        //get the customer
        $customerId = (int)$this->_getQueryParameter('customer_id');
        $customer = Mage::getModel('customer/customer')->load($customerId);

        if(!$customer->getId())
            $customer = Mage::getSingleton('customer/session')->getCustomer();

        if(!$customer->getId())
            Mage::throwException($h->__('Cannot add product to wishlist. Customer ID not specified.'));

        $data = $this->getJsonPayload();

        //get the product
        $productId = $data->product_id; //$this->_getQueryParameter('product_id');
        $product = Mage::getModel('catalog/product')->load($productId);
        if(!$product->getId())
            Mage::throwException($h->__('Cannot add product to wishlist. Product "%s" does not exist.', $productId));

        $buyInfo = Mage::helper('bakerloo_restful/sales')->getBuyInfo($data);

        //get the customer's wishlist
        $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer, true);
        $result = $wishlist->addNewItem($product, $buyInfo);

        if(is_string($result))
            Mage::throwException($h->__($result));

        $wishlist->save();
        return $this->_createDataObject(null, $wishlist);
    }
}