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

class SubscribePro_Autoship_Model_System_Config_Source_Loglevel
{

    /**
     * From Zend_Log docs:
     *
     * The Zend_Log class defines the following priorities:
     *      EMERG   = 0;  // Emergency: system is unusable
     *      ALERT   = 1;  // Alert: action must be taken immediately
     *      CRIT    = 2;  // Critical: critical conditions
     *      ERR     = 3;  // Error: error conditions
     *      WARN    = 4;  // Warning: warning conditions
     *      NOTICE  = 5;  // Notice: normal but significant condition
     *      INFO    = 6;  // Informational: informational messages
     *      DEBUG   = 7;  // Debug: debug messages
     *
     */

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Zend_Log::EMERG,
                'label' => Mage::helper('autoship')->__('EMERG - Emergency'),
            ),
            array(
                'value' => Zend_Log::ALERT,
                'label' => Mage::helper('autoship')->__('ALERT - Alert'),
            ),
            array(
                'value' => Zend_Log::CRIT,
                'label' => Mage::helper('autoship')->__('CRIT - Critical'),
            ),
            array(
                'value' => Zend_Log::ERR,
                'label' => Mage::helper('autoship')->__('ERR - Error'),
            ),
            array(
                'value' => Zend_Log::WARN,
                'label' => Mage::helper('autoship')->__('WARN - Warning'),
            ),
            array(
                'value' => Zend_Log::NOTICE,
                'label' => Mage::helper('autoship')->__('NOTICE - Notice'),
            ),
            array(
                'value' => Zend_Log::INFO,
                'label' => Mage::helper('autoship')->__('INFO - Informational'),
            ),
            array(
                'value' => Zend_Log::DEBUG,
                'label' => Mage::helper('autoship')->__('DEBUG - Emergency'),
            ),
        );
    }

}
