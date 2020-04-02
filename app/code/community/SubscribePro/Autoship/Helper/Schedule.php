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

class SubscribePro_Autoship_Helper_Schedule extends Mage_Core_Helper_Abstract
{
    /*
     * Get current system time (expected to be in UTC)
     *
     * @return DateTime Returns current sys time as TimeDate object
     */
    public function getCurrentDateTime()
    {
        // Lookup configured timezone
        $timezone = Mage::getStoreConfig('general/locale/timezone');
        // Get current time
        $curDateTime = new DateTime('now');

        // Return
        return $curDateTime;
    }

    /*
     * Get current system time(expected to be in UTC)
     *
     * @return string Returns current sys time as string
     */
    public function getCurrentTime()
    {
        // Get current DateTime obj
        $curDateTime = $this->getCurrentDateTime();

        // Return current time formatted nicely as string
        return $curDateTime->format('H:i:s');
    }

    /*
     * Get current system time (considering Magento time zone config setting)
     *
     * @return DateTime Returns current sys time as TimeDate object
     */
    public function getCurrentDateTimeUsingTimezone()
    {
        // Lookup configured timezone
        $timezone = Mage::getStoreConfig('general/locale/timezone');
        // Get current time
        $curDateTime = new DateTime('now', new DateTimeZone($timezone));

        // Return
        return $curDateTime;
    }

    /*
     * Get current system time (considering Magento time zone config setting)
     *
     * @return string Returns current sys time as string
     */
    public function getCurrentTimeUsingTimezone()
    {
        // Get current DateTime obj
        $curDateTime = $this->getCurrentDateTimeUsingTimezone();

        // Return current time formatted nicely as string
        return $curDateTime->format('H:i:s');
    }

}

