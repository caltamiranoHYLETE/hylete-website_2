<?php

/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All Rights Reserved.
 */

/**
 * Helper class for ajax login
 *
 * Class Mediotype_AjaxLogin_Helper_Data
 */
class Mediotype_AjaxLogin_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CONFIG_AJAX_LOGIN_ENABLED = 'mediotype_general/mediotype_ajax_login/enable';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfigFlag(self::XML_PATH_CONFIG_AJAX_LOGIN_ENABLED);
    }
}
