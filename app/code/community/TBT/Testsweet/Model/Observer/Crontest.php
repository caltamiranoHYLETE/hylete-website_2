<?php

class TBT_Testsweet_Model_Observer_Crontest {

    public function run() {
        $timestamp = $this->getCurrentTimestamp();
        Mage::getConfig()
            ->saveConfig('testsweet/crontest/timestamp', $timestamp, 'default', 0)
            ;
        // PATCH
        // DO NOT REINIT FULL CACHE! Instead set flag that config cache needs to be invalidated.
        //    ->reinit();
        //~PATCH

        return $this;
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getDatabaseRead()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    protected function _getDirectStoreConfig($path, $store = null, $backendModel = null)
    {
        /** @var Mage_Core_Model_Resource_Config $configResource */
        $configResource = Mage::getConfig()->getResourceModel();
        $scopeId = $store === 0 ? $store : (int)Mage::app()->getStore($store)->getId();
        if ($scopeId) {
            $connection = $this->_getDatabaseRead();
            $select = $connection->select();
            $select->from($configResource->getMainTable(), array('value'));
            $select->where('scope = :scope AND scope_id = :scope_id AND path = :path');


            $value = $connection->fetchOne($select, array(
                'scope' => $scopeId === 0 ? 'default' : 'stores',
                'scope_id' => $scopeId,
                'path' => $path,
            ));

            if ($backendModel && !empty($value)) {
                $backend = Mage::getModel($backendModel);
                $backend->setPath($path)->setValue($value)->afterLoad();
                $value = $backend->getValue();
            }

            return $value;
        }

        return null;
    }

    public function getCurrentTimestamp() {
        $timestamp = (string)time();
        return $timestamp;
    }

    public function getCronTimestamp() {
        //$timestamp = (string)Mage::getConfig()->getNode('testsweet/crontest/timestamp', 'default', 0);
        // PATCH
        //$timestamp = Mage::getStoreConfig('testsweet/crontest/timestamp');
        $timestamp = $this->_getDirectStoreConfig('testsweet/crontest/timestamp', 0);
        //~PATCH
        return $timestamp;
    }

    public function isWorking() {
        $timestamp = $this->getCronTimestamp();
        if (empty($timestamp))
            return false;

        $seconds = $this->getCurrentTimestamp() - $timestamp;

        // if the timestamp is within 30 minuets return true
        return $seconds < (60 * 30);
    }

}