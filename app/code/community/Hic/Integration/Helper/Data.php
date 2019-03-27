<?php
/**
 * HiConversion
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * [http://opensource.org/licenses/MIT]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category Hic
 * @package Hic_Integration
 * @Copyright © 2015 HiConversion, Inc. All rights reserved.
 * @license [http://opensource.org/licenses/MIT] MIT License
 */

/**
 * Integration data helper
 *
 * @category Hic
 * @package Integration
 * @author HiConversion <support@hiconversion.com>
 */
class Hic_Integration_Helper_Data extends Mage_Core_Helper_Abstract
{
    const SETTINGS_ENABLED   = 'integration/settings/enabled';
    const SETTINGS_SITE_ID   = 'integration/settings/site_id';
    const SETTINGS_BN_CODE   = 'integration/settings/bn_code';

    /**
     * Returns Site ID from Configuration
     *
     * @return string
     */
    public function getSiteId()
    {
        return Mage::getStoreConfig(self::SETTINGS_SITE_ID);
    }
    
    /**
     * Returns BN Code from Configuration
     *
     * @return string
     */
    public function getBNCode()
    {
        return Mage::getStoreConfig(self::SETTINGS_BN_CODE);
    }

    /**
     * Returns Magento Version
     *
     * @return string
     */
    public function getMageVersion()
    {
        return Mage::getVersion();
    }
    
    /**
     * Returns Magento Edition or empty string if less than version 1.7
     *
     * @return string
     */
    public function getMageEdition()
    {
        if (method_exists('Mage', 'getEdition')) {
            return Mage::getEdition();
        } else {
          return "";
        }
    }

    /**
     * Determines if module is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::SETTINGS_ENABLED);
    }

    /**
     * Returns Data that can be cached relative to a session
     * currently cart and user data
     * @return object
     */
    public function hicSessionData()
    {
        $model = Mage::getModel('integration/data')
            ->populateCartData()
            ->populateUserData();
            
        return $model;
    }
    
    /**
     * Returns Data that can be cached relative to a page
     * currently page and product data
     * @return object
     */
    public function hicPageData()
    {
        $model = Mage::getModel('integration/data')
            ->populatePageData();
        
        if ($model->isProduct()) {
            $model->populateProductData();
        }
            
        return $model;
    }
    
    /**
     * Returns Data that should never be cached
     * currently order data
     * @return object
     */
    public function hicNeverData()
    {
        $model = Mage::getModel('integration/data');
     
        if ($model->isConfirmation()) {
            $model->populateOrderData();
        }
        
        return $model;
    }


}