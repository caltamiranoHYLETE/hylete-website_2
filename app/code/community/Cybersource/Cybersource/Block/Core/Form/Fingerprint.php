<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_Core_Form_Fingerprint extends Mage_Core_Block_Template
{
    public function _construct()
    {
        $this->setTemplate('cybersourcecore/fingerprint.phtml');
    }

    /**
     * is device fingerprint enabled
     * @return bool
     */
    public function isFingerprintEnabled()
    {
        return Mage::helper('cybersource_core')->getIsFingerprintEnabled();
    }

    /**
     * Retrieves session id
     * @return mixed
     */
    public function getSessionId()
    {
        return Mage::getSingleton('customer/session')->getEncryptedSessionId();
    }

    /**
     * Retrieves merchant id
     * @return mixed
     */
    public function getMerchantId()
    {
        return Mage::helper('cybersource_core')->getMerchantId();
    }

    /**
     * Retrieves org id
     * @return mixed
     */
    public function getOrgId()
    {
        return Mage::helper('cybersource_core')->getFingerprintOrgId();
    }

    /**
     * Retrieves fingerprint url
     * @return mixed
     */
    public function getFingerprintUrl()
    {
        return Mage::helper('cybersource_core')->getFingerprintUrl();
    }
}
