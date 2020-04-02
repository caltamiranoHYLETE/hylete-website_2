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

class SubscribePro_Autoship_Helper_Cache extends Mage_Core_Helper_Abstract
{

    // Cache Type Tags
    const CACHE_TYPE_CONFIG         = 'SP_CONFIG';
    const CACHE_TYPE_PRODUCTS       = 'SP_PRODUCTS';

    const CACHE_TOKEN_TAG           = 'autoship_api_access_token';

    /** @var Mage_Core_Model_Store|null */
    private $_store = null;


    public function __construct()
    {
    }

    /**
     * Set the primary key id of the store to use for all configuration scope
     *
     * @param int|Mage_Core_Model_Store $store Primary key id of the store to use
     */
    public function setConfigStore($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $this->_store = $store;
        }
        else {
            $this->_store = Mage::app()->getStore($store);
        }
    }

    /**
     * Return the store to use for pulling configuration settings
     *
     * @return Mage_Core_Model_Store
     */
    public function getConfigStore()
    {
        if ($this->_store == null) {
            $this->_store = Mage::app()->getStore();
        }
        return $this->_store;
    }

    /**
     * @return int|mixed
     */
    public function getCacheLifetime()
    {
        $lifetime = Mage::getStoreConfig('autoship_general/advanced/cache_lifetime', $this->getConfigStore());
        if ($lifetime <= 0) {
            $lifetime = 300;
        }

        return $lifetime;
    }

    /**
     * @param $type
     * @return bool
     */
    public function useCache($type)
    {
        if ($type == self::CACHE_TYPE_CONFIG) {
            $useCache = Mage::app()->useCache('subscribe_pro_config');
        }
        else if ($type == self::CACHE_TYPE_PRODUCTS) {
            $useCache = Mage::app()->useCache('subscribe_pro_products');
        }
        else {
            $useCache = false;
        }

        return $useCache;
    }

    /**
     * @param $data
     * @param $key
     * @param $type
     * @param int $lifetime
     */
    public function saveCache($data, $key, $type, $lifetime = 0)
    {
        // Lookup lifetime if not passed as param
        if ($lifetime <= 0) {
            $lifetime = $this->getCacheLifetime();
        }
        // Add store id to key
        $key .= '_store_' . $this->getConfigStore()->getId();
        // Check use cache setting
        if ($this->useCache($type)) {
            // Save json encoded data to cache
            Mage::app()->saveCache(json_encode($data), $key, array($type), $lifetime);
        }
    }

    /**
     * @param $key
     * @param $type
     * @return bool|mixed
     */
    public function loadCache($key, $type)
    {
        // Check use cache setting
        if ($this->useCache($type)) {
            // Add store id to key
            $key .= '_store_' . $this->getConfigStore()->getId();
            // Lookup data in cache
            $data = Mage::app()->loadCache($key);
            if ($data === false) {
                return false;
            }
            else {
                // JSON decode it and return
                return json_decode($data, true);
            }
        }
        else {
            return false;
        }
    }

    /**
     * @param $key
     * @param $type
     */
    public function removeCache($key, $type)
    {
        // Check use cache setting
        if ($this->useCache($type)) {
            // Add store id to key
            $key .= '_store_' . $this->getConfigStore()->getId();
            Mage::app()->removeCache($key);
        }
    }

}
