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

class SubscribePro_Autoship_Block_Applepay_Scripts extends SubscribePro_Autoship_Block_Hosted_Abstract
{

    /**
     * @return string
     */
    public function getCreateSessionUrl()
    {
        return rtrim($this->getApiBaseUrl(), '/') . '/services/v2/vault/applepay/create-session.json';
    }

    /**
     * @return array
     */
    public function getApplePayPaymentRequest()
    {
        /** @var SubscribePro_Autoship_Helper_Applepay $applePayHelper */
        $applePayHelper = Mage::helper('autoship/applepay');

        return $applePayHelper->getApplePayPaymentRequest();
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return  (bool) Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * @return bool
     */
    public function hasProductsToCreateNewSubscription()
    {
        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');

        return (bool) $quoteHelper->hasProductsToCreateNewSubscription();
    }

}
