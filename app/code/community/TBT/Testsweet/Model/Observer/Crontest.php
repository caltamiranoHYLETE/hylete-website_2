<?php

class TBT_Testsweet_Model_Observer_Crontest {

    public function run() {
        $timestamp = $this->getCurrentTimestamp();
        Mage::getConfig()
            ->saveConfig('testsweet/crontest/timestamp', $timestamp, 'default', 0)
            ->reinit();

        return $this;
    }
    
    public function getCurrentTimestamp() {
        /** @var Mage_Core_Model_Resource_Config_Data_Collection $configDataCollection */
        $configDataCollection = Mage::getResourceModel('core/config_data_collection');

        $configDataCollection->addFieldToFilter('scope', 'default');
        $configDataCollection->addFieldToFilter('scope_id', 0);
        $configDataCollection->addFieldToFilter('path', array('eq' => 'testsweet/crontest/timestamp'));

        /** @var Mage_Core_Model_Config_Data $configData */
        $configData = $configDataCollection->getFirstItem();

        return (string)$configData->getValue();
    }

    public function getCronTimestamp() {
        //$timestamp = (string)Mage::getConfig()->getNode('testsweet/crontest/timestamp', 'default', 0);
        $timestamp = Mage::getStoreConfig('testsweet/crontest/timestamp');
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
