<?php

/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All Rights Reserved.
 */
class Hylete_CategoryFilter_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $customerGroup;

    private $cache;

    /**
     * Retrieve cache key
     *
     * @param $key
     * @param array $datakey
     * @return string
     */
    public function getCacheKey($key, array $datakey)
    {
        if (empty($datakey)) {
            return null;
        }
        $additionalKey = implode('_', array_map(
            function ($v, $k) {
                return sprintf('%s_%s', $k, $v);
            },
            $datakey,
            array_keys($datakey)
        ));
        $storeId = $this->getStoreId();
        if ($storeId === null) {
            return null;
        }

        return strtoupper($key)
            . '_STOREID_' . $this->getStoreId()
            . '_CUSTOMERGROUP_' . $this->getCustomerGroup()
            . '_' . strtoupper($additionalKey);
    }

    /**
     * Retrieve customer id
     *
     * @return int
     */
    private function getCustomerGroup()
    {
        if ($this->customerGroup === null) {
            $customer = Mage::getSingleton('customer/session');
            $this->customerGroup = $customer->isLoggedIn() ? $customer->getCustomer()->getGroupId() : 'GUEST';
        }

        return $this->customerGroup;
    }

    /**
     * Retrieve store id
     *
     * @return string|null
     */
    private function getStoreId()
    {
        try {
            return 'STORE_ID_' . Mage::app()->getStore()->getId();
        } catch (Mage_Core_Model_Store_Exception $e) {
            Mage::logException($e);
        }

        return null;
    }

    /**
     * Serialize data to cache
     *
     * @param array|string|int|object $data
     * @return string
     */
    public function serialize($data)
    {
        if (is_object($data)) {
            return urlencode(serialize($data));
        }

        return serialize($data);
    }

    /**
     * Serialize data to cache
     *
     * @param array|string|int|object $data
     * @return string
     */
    public function unserialize($data)
    {
        if (is_object($data)) {
            return unserialize(@urldecode($data));
        }

        return unserialize($data);
    }

    /**
     * Retrieve cache object
     *
     * @return Zend_Cache_Core
     */
    public function getCache()
    {
        if ($this->cache === null) {
            $this->cache = Mage::app()->getCache();
        }

        return $this->cache;
    }
}
