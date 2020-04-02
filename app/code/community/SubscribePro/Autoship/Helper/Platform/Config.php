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

class SubscribePro_Autoship_Helper_Platform_Config extends SubscribePro_Autoship_Helper_Platform_Abstract
{

    private $accountConfig = null;


    /**
     * @return array|bool|null
     */
    public function getAccountConfig()
    {
        if (is_array($this->accountConfig)) {
            return $this->accountConfig;
        }
        else {
            // Lookup from cache
            $accountConfig = $this->getCacheHelper()->loadCache(
                'autoship_api_config',
                SubscribePro_Autoship_Helper_Cache::CACHE_TYPE_CONFIG);

            // Check if found in cache
            if ($accountConfig !== false) {
                $accountConfig = unserialize($accountConfig);
            }
            else {
                // Not found in cache
                // Request from API
                $accountConfig = $this->getConfigTool()->load();
                // Save in cache
                $this->getCacheHelper()->saveCache(
                    serialize($accountConfig),
                    'autoship_api_config',
                    SubscribePro_Autoship_Helper_Cache::CACHE_TYPE_CONFIG);
            }

            // Save in member
            $this->accountConfig = $accountConfig;

            // Return product
            return $accountConfig;
        }
    }

    /**
     * @return string
     */
    public function getConfiguredPaymentMethodCode()
    {
        // Get account configuration from platform
        $accountConfig = $this->getAccountConfig();
        if (isset($accountConfig['magento_payment_method'])) {
            return $accountConfig['magento_payment_method'];
        }
        else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getConfiguredGateway()
    {
        // Get account configuration from platform
        $accountConfig = $this->getAccountConfig();
        if (isset($accountConfig['payment_gateway'])) {
            return $accountConfig['payment_gateway'];
        }
        else {
            return '';
        }
    }

}
