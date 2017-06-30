<?php

class Icommerce_SelectedProducts_Model_Cache
{
    const CACHE_FLAG_NAME = 'selectedproducts';

    public static function getCacheKey($key)
    {
        $hash = strtoupper(md5($key));
        $cacheKey = 'SELECTED_PRODUCTS_' . $hash;
        return $cacheKey;
    }

    // get cached collection or null if it is not cached or expired
    public static function getCollection($key)
    {
        $data = array();
        $cache = Mage::app()->getCache();
        $cacheKey = Icommerce_SelectedProducts_Model_Cache::getCacheKey($key);

        if (Mage::app()->useCache('selectedproducts') && $collectionJson = $cache->load($cacheKey)) {
            $data = Zend_Json::decode($collectionJson);
        }

        if (!isset($data['class'])) {
            return null;
        }

        $coll = new ArrayObject();
        $objClass = $data['class'];
        $obj = new $objClass();

        foreach (( array)$data['data'] as $prod) {
            $product = $obj->getNewEmptyItem();
            $product->setData($prod);
            $coll->append($product);
        }

        return $coll;
    }

    public static function cacheCollection($key, $coll)
    {
        if (Mage::app()->useCache('selectedproducts')) {
            $cache = Mage::app()->getCache();
            $cacheKey = Icommerce_SelectedProducts_Model_Cache::getCacheKey($key);
            $cacheLifetime = Icommerce_Default::getStoreConfig('selectedproducts/settings/seconds_to_expire');

            // executes the query. Keep it.
            $count = $coll->count();

            $data = array();
            $data['class'] = get_class($coll);
            $data['count'] = $count;
            $data['data'] = $coll->toArray();

            $encodedCollection = Zend_Json::encode($data);
            $cache->save($encodedCollection, $cacheKey, array('SELECTED_PRODUCTS','COLLECTION_DATA'), $cacheLifetime);
        }
    }
}
