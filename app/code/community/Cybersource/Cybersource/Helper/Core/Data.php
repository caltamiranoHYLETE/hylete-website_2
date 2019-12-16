<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Helper_Core_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param null $store
     * @return mixed
     */
    public function getMerchantId($store = null)
    {
        return $this->getConfig('merchant_id', $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function getIsTestMode($store = null)
    {
        return (bool) $this->getConfig('test', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getSoapKey($store = null)
    {
        return $this->getConfig('soapkey', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getWsdlUrl($store = null)
    {
        return $this->getIsTestMode($store)
            ? $this->getConfig('wsdl_test_url', $store)
            : $this->getConfig('wsdl_live_url', $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function getIsDmEnabled($store = null)
    {
        return (bool) $this->getConfig('dm_enabled', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getDmReviewMessage($store = null)
    {
        return $this->getConfig('review_message', $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function getIsDmMerchantDataEnabled($store = null)
    {
        return (bool) $this->getConfig('mdd_enabled', $store) && $this->getIsDmEnabled();
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getDmMerchantDataFields($store = null)
    {
        return $this->getConfig('mdd_fields', $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function getIsFingerprintEnabled($store = null)
    {
        return (bool) $this->getConfig('device_fingerprint_enabled', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getFingerprintOrgId($store = null)
    {
        return $this->getConfig('device_fingerprint_org_id', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getFingerprintUrl($store = null)
    {
        return $this->getConfig('device_fingerprint_url', $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function getIsReportEnabled($store = null)
    {
        return (bool) $this->getConfig('report_enabled', $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function getIsReportLogEnabled($store = null)
    {
        return (bool) $this->getConfig('report_log', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getReportUsername($store = null)
    {
        return $this->getConfig('report_username', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getReportPassword($store = null)
    {
        return $this->getConfig('report_password', $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function getAvsActive($store = null)
    {
        return (bool) $this->getConfig('avs_active', $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function getAvsForceNormalization($store = null)
    {
        return (bool) $this->getConfig('avs_force_normal', $store);
    }

    /**
     * @param bool $key
     * @param null $store
     * @return mixed
     */
    public function getConfig($key = false, $store = null)
    {
        if (! $store) {
            $store = Mage::app()->getStore()->getId();
        }

        if ($key) {
            return Mage::getStoreConfig('payment/cybersourcecore/' . $key, $store);
        }

        return Mage::getStoreConfig('payment/cybersourcecore', $store);
    }
}
