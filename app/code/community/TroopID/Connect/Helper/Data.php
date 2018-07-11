<?php

class TroopID_Connect_Helper_Data extends Mage_Core_Helper_Abstract {

    const CACHE_TAG = "troopid_connect";
    const CACHE_KEY = "troopid_affiliations";

    public function getKey($key, $storeId = null) {
        return Mage::getStoreConfig("troopid_connect/settings/" . $key, $storeId);
    }

    public function isEnabled($scope) {
        return $this->getKey("enabled_" . $scope) === "1";
    }

    public function isOperational() {
        return $this->getKey("enabled") == "1" && $this->getKey("client_id") && $this->getKey("client_secret");
    }

    public function getAffiliationByScope($scope) {
        $result = null;
        $groups = $this->getAffiliations();

        foreach ($groups as $group) {
            if ($group["scope"] == $scope)
                $result = $group["name"];
        }

        return $result;
    }

    public function getScopeByAffiliation($affiliation) {
        $result = null;
        $groups = $this->getAffiliations();

        foreach ($groups as $group) {
            if ($group["name"] == $affiliation)
                $result = $group["scope"];
        }

        return $result;
    }

    public function getAffiliations() {
        $cache  = Mage::app()->getCache();
        $oauth  = Mage::helper("troopid_connect/oauth");
        $values = $cache->load(self::CACHE_KEY);
        $values = unserialize($values);

        if (is_array($values))
            return $values;

        $values = $oauth->getAffiliations();

        if (is_array($values) && count($values) > 0)
            $cache->save(serialize($values), self::CACHE_KEY, array(self::CACHE_TAG), 60*60*24*7);

        return $values;
    }

    public function formatAffiliation($scope, $group) {
        $affiliation = $this->getAffiliationByScope($scope);

        if (!empty($group))
            $affiliation .= " - " . $group;

        return $affiliation;
    }

}