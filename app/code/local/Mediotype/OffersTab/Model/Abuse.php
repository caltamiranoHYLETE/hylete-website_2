<?php

/**
 * Request abuse utility.
 *
 * @category Class
 * @package Mediotype_OffersTab
 * @author Rick Buczynski <rick@mediotype.com>
 * @copyright 2018 Mediotype. All Rights Reserved.
 */

class Mediotype_OffersTab_Model_Abuse
{
    const CONFIG_XML_PATH_ENABLED = 'mediotype_offerstab/general/abuse_manager';
    const CONFIG_XML_PATH_WHITELIST = 'mediotype_offerstab/general/abuse_whitelist';

    const REQUEST_STORAGE_KEY = 'OFFERSTAB_ABUSE_DATA';
    const REQUEST_THRESHOLD = 5;
    const REQUEST_COOLDOWN = 300;

    protected $data = array();

    private $enabled = true;

    /** @var Zend_Cache_Core */
    private $storage;

    private $whitelist = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->enabled = Mage::getStoreConfigFlag(self::CONFIG_XML_PATH_ENABLED);
        $this->storage = Mage::app()->getCache();
        $this->load();
    }

    /**
     * Approve an IP address for the current request.
     *
     * @param null $address Optional IP address to check
     * @return boolean
     * @throws Exception
     */
    public function approve($address = null)
    {
        if (!$this->enabled) {
            return true;
        }

        $model = $this->loadByAddress($address ?: $this->getAddress());

        if ($this->validate($model->getIpAddress())) {
            $model->incrementAttempt();
            return true;
        } elseif (($this->getTimestamp() - $model->getLastAttempt()) >= self::REQUEST_COOLDOWN) {
            $model->resetAttempts();
            return true;
        }

        $model->incrementAttempt();

        return false;
    }

    /**
     * Get request abuse data by the given IP address.
     *
     * @param $address
     * @return Mediotype_OffersTab_Model_Abuse_Data
     */
    public function getDataByAddress($address)
    {
        return $this->loadByAddress($address);
    }

    /**
     * Get the current GMT timestamp.
     *
     * @return int
     */
    public function getTimestamp()
    {
        return Mage::getSingleton('core/date')->gmtTimestamp();
    }

    /**
     * Synchronize abuse data with storage.
     *
     * @param Mediotype_OffersTab_Model_Abuse_Data $model
     * @return Mediotype_OffersTab_Model_Abuse
     * @throws Zend_Cache_Exception
     */
    public function persist(Mediotype_OffersTab_Model_Abuse_Data $model)
    {
        return $this->save($model->getId(), $model->toArray());
    }

    /**
     * Determine whether the given address has exceeded the request limit.
     *
     * @param null $address Optional IP address to check
     * @return boolean
     */
    public function validate($address = null)
    {
        /** @var Mediotype_OffersTab_Model_Abuse_Data $model */
        $model = $this->loadByAddress($address ?: $this->getAddress());

        return $this->isWhitelisted($address) || $model->getAttempts() < self::REQUEST_THRESHOLD;
    }

    /**
     * Get the client IP address.
     *
     * @return string
     */
    private function getAddress()
    {
        return Mage::helper('core/http')->getRemoteAddr();
    }

    /**
     * Generate a hash from the given storage key.
     *
     * @param $key
     * @return string
     */
    private function hashStorageKey($key)
    {
        return md5($key);
    }

    /**
     * Determine whether the given address is whitelisted.
     *
     * @param null $address
     * @return bool
     */
    private function isWhitelisted($address = null)
    {
        if ($address === null) {
            $address = $this->getAddress();
        }

        return in_array($address, $this->whitelist);
    }

    /**
     * Load data from storage.
     *
     * @return Mediotype_OffersTab_Model_Abuse
     */
    private function load()
    {
        $data = unserialize($this->storage->load(self::REQUEST_STORAGE_KEY));

        if (is_array($data)) {
            $this->data = $data;
        } else {
            $this->data = array();
        }

        $this->whitelist = (array) array_map(
            'trim',
            explode(
                ',',
                Mage::getStoreConfig(self::CONFIG_XML_PATH_WHITELIST)
            )
        );

        return $this;
    }

    /**
     * Load requester data by IP address.
     *
     * @param $address
     * @return Mediotype_OffersTab_Model_Abuse_Data
     */
    private function loadByAddress($address)
    {
        $storageKey = $this->hashStorageKey($address);
        $data = isset($this->data[$storageKey]) ? $this->data[$storageKey] : array();

        /** @var Mediotype_OffersTab_Model_Abuse_Data $model */
        $model = Mage::getModel(
            'mediotype_offerstab/abuse_data',
            array_merge(
                $data,
                array(
                    'manager' => $this,
                    'id' => $storageKey,
                    'ip_address' => $address,
                )
            )
        );

        return $model;
    }

    /**
     * Save the given data to storage.
     *
     * @param $key
     * @param $value
     * @throws Zend_Cache_Exception
     * @return Mediotype_OffersTab_Model_Abuse
     */
    private function save($key, $value)
    {
        $this->data[$key] = $value;

        $this->storage->save(
            serialize($this->data),
            self::REQUEST_STORAGE_KEY,
            ['CONFIG'],
            null,
            null
        );

        return $this;
    }
}
