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

class SubscribePro_Autoship_Block_Checkout_Iframescripts extends Mage_Checkout_Block_Onepage_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('autoship/checkout/iframescripts.phtml');
    }

    public function getEnvironmentKey()
    {
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');
        // Lookup payment method code based on SP config
        $accountConfig = $apiHelper->getAccountConfig();
        // Get env key
        $environmentKey = $accountConfig['transparent_redirect_environment_key'];

        return $environmentKey;
    }

}
