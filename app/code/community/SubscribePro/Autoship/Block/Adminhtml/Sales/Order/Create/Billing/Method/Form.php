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

class SubscribePro_Autoship_Block_Adminhtml_Sales_Order_Create_Billing_Method_Form extends Mage_Adminhtml_Block_Sales_Order_Create_Billing_Method_Form
{
    /**
     * Check payment method model
     *
     * @param Mage_Payment_Model_Method_Abstract|null $method
     * @return bool
     */
    protected function _canUseMethod($method)
    {
        $quote = $this->getQuote();

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $quote->getStore()) != '1') {
            return parent::_canUseMethod($method);
        }

        // We should create subs during checkout, and we are checking a payment method other than standard is being used
        // If other payment method being used, only allow this when there are no subscriptions to create
        // Get quote helper
        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');
        // Check if quote has any subscriptions in it
        if(!$quoteHelper->hasProductsToCreateNewSubscription($quote)) {
            // Quote has no subscriptions,
            // Go through normal qualification process for payment methods
            return parent::_canUseMethod($method);
        }
        else {
            // Quote has subscriptions, only allow payment methods compatible with subscriptions
            /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
            $apiHelper = Mage::helper('autoship/api');
            $apiHelper->setConfigStore($quote->getStore());
            /** @var SubscribePro_Autoship_Helper_Payment $paymentHelper */
            $paymentHelper = Mage::helper('payment');
            // Check for configured payment method code
            if($paymentHelper->isSubscribeProCreditCardMethod($method->getCode()) || $paymentHelper->isSubscribeProBankAccountMethod($method->getCode())) {
                // This is the pay method which is allowed by Subscribe Pro config
                // Run normal check
                return parent::_canUseMethod($method);
            }
            else {
                // This is some other payment method, not allowed when checking out and creating subscriptions
                return false;
            }

        }
    }
}