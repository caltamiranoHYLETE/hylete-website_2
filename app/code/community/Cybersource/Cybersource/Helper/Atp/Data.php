<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Helper_Atp_Data extends Mage_Core_Helper_Data
{
    const GROUPNAME = 'cybersource_atp';

    const CONFIG_ENABLED = 'customer/atp/enabled';
    const CONFIG_USE_INTERNAL = 'customer/atp/use_internal_block';
    const CONFIG_TITLE = 'customer/atp/title';
    const CONFIG_MESSAGE = 'customer/atp/message';

    /**
     * Is the Account Transfer Protection service integration enabled?
     *
     * @return bool
     */

    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::CONFIG_ENABLED);
    }

    /**
     * Is the Account Transfer Protection using the internal mechanism?
     *
     * @return bool
     */

    public function isInternalMechanismEnabled()
    {
        return Mage::getStoreConfigFlag(self::CONFIG_USE_INTERNAL);
    }

    /**
     * Retrieves the rejection message title configuration value
     *
     * @return string
     */
    public function getRejectionTitle()
    {
        return Mage::getStoreConfig(self::CONFIG_TITLE);
    }

    /**
     * Retrieves the rejection message configuration value
     *
     * @return string
     */
    public function getRejectionMessage()
    {
        return Mage::getStoreConfig(self::CONFIG_MESSAGE);
    }

}
