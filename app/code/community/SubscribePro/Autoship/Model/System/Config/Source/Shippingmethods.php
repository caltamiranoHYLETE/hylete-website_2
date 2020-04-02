<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

class SubscribePro_Autoship_Model_System_Config_Source_Shippingmethods extends Mage_Adminhtml_Model_System_Config_Source_Shipping_Allmethods
{

    /**
     * Return all allowed shipping methods, only the active ones
     *
     * @param bool $isActiveOnlyFlag Flag is ignored, only included to make method signature compatible with parent
     * @return array
     */
    // @codingStandardsIgnoreStart
    public function toOptionArray($isActiveOnlyFlag = false)
    {
        // Regardless of flag, only include active shipping carriers / methods
        return parent::toOptionArray(true);
    }
    // @codingStandardsIgnoreEnd
}
