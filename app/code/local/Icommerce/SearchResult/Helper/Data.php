<?php

class Icommerce_SearchResult_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function __construct()
    {
        $this->_path = 'searchresult/settings/';
    }

    public function getConfigData($field)
    {
        $res = Mage::getStoreConfig($this->_path . $field);
        return $res;
    }

    public function setConfigData($field,$value)
    {
        if ($this->getConfigData($field)!=$value) {
            Mage::getConfig()->saveConfig($this->_path . $field, $value );
            Mage::app()->getStore()->setConfig($this->_path . $field, $value );
            Mage::getConfig()->saveCache();
        }
    }

}

