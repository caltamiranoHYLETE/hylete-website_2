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
class SubscribePro_Autoship_Model_System_Config_Source_Month
{
    /**
     * Retrieve Check Type Option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => '01',
                'label' => Mage::helper('autoship')->__('01 - January')
            ),
            array(
                'value' => '01',
                'label' => Mage::helper('autoship')->__('01 - January')
            ),
            array(
                'value' => '02',
                'label' => Mage::helper('autoship')->__('02 - February')
            ),
            array(
                'value' => '03',
                'label' => Mage::helper('autoship')->__('03 - March')
            ),
            array(
                'value' => '04',
                'label' => Mage::helper('autoship')->__('04 - April')
            ),
            array(
                'value' => '05',
                'label' => Mage::helper('autoship')->__('05 - May')
            ),
            array(
                'value' => '06',
                'label' => Mage::helper('autoship')->__('06 - June')
            ),
            array(
                'value' => '07',
                'label' => Mage::helper('autoship')->__('07 - July')
            ),
            array(
                'value' => '08',
                'label' => Mage::helper('autoship')->__('08 - August')
            ),
            array(
                'value' => '09',
                'label' => Mage::helper('autoship')->__('09 - September')
            ),
            array(
                'value' => '10',
                'label' => Mage::helper('autoship')->__('10 - October')
            ),
            array(
                'value' => '11',
                'label' => Mage::helper('autoship')->__('11 - November')
            ),
            array(
                'value' => '12',
                'label' => Mage::helper('autoship')->__('12 - December')
            ),
        );
    }
}
