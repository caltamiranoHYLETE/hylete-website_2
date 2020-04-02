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

class SubscribePro_Autoship_Block_Adminhtml_Sales_Order_Create_Iframescripts extends Mage_Checkout_Block_Onepage_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('autoship/sales/create_order/iframescripts.phtml');
    }

    protected function getStore()
    {
        // If we are in admin store, try to find correct store from current quote
        /** @var Mage_Adminhtml_Model_Session_Quote $adminhtmlQuoteSession */
        $adminhtmlQuoteSession = Mage::getSingleton('adminhtml/session_quote');
        $quote = $adminhtmlQuoteSession->getQuote();
        $store = $quote->getStore();

        return $store;
    }

    public function showIframe()
    {
        $spPayMethodActive = Mage::getStoreConfig('payment/subscribe_pro/active', $this->getStore()) == '1';

        return $spPayMethodActive;
    }

    public function getEnvironmentKey()
    {
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');
        // Set store on api helper
        $apiHelper->setConfigStore($this->getStore());
        // Lookup payment method code based on SP config
        $accountConfig = $apiHelper->getAccountConfig();
        // Get env key
        $environmentKey = $accountConfig['transparent_redirect_environment_key'];

        return $environmentKey;
    }

}
