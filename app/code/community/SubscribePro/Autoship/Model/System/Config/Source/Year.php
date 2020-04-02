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

/**
 */
class SubscribePro_Autoship_Model_System_Config_Source_Year
{
    /**
     * Retrieve Option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $years = $this->_getConfig()->getYears();
        $yearOptionArray = array();
        foreach ($years as $year) {
            $yearOptionArray[] = array(
                'value' => $year,
                'label' => Mage::helper('autoship')->__($year)
            );

        }

        return $yearOptionArray;
    }

    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

}
