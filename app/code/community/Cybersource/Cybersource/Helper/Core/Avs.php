<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Helper_Core_Avs extends Mage_Core_Helper_Abstract
{
    const LOGFILE = 'cybs_avs.log';

    /**
     * @param array|string $message
     * @param bool $force
     * @return $this
     */
    public function log($message, $force = false)
    {
        if (!$this->isDebugMode() && !$force) {
            return $this;
        }

        if (is_array($message)) {
            $message = var_export($message, true);
        }

        Mage::log($message, null, self::LOGFILE, $force);

        return $this;
    }

    public function isDebugMode()
    {
        return (bool) Mage::getStoreConfig('payment/cybersourcecore/avs_debug');
    }
}
