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
 * Reward observer
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SubscribePro_Autoship_Model_Enterprise_Reward_Observer extends Enterprise_Reward_Model_Observer
{

    /**
     * Override Enterprise_Reward implementation of this method.  Allow SP Vault payment method
     * in this situation when there is a subscription in cart.
     *
     * Enable Zero Subtotal Checkout payment method
     * if customer has enough points to cover grand total
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function preparePaymentMethod($observer)
    {
        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');
        /** @var SubscribePro_Autoship_Helper_Payment $paymentHelper */
        $paymentHelper = Mage::helper('payment');

        if (!Mage::helper('enterprise_reward')->isEnabledOnFront()) {
            return $this;
        }

        $quote = $observer->getEvent()->getQuote();
        if (!is_object($quote) || !$quote->getId()) {
            return $this;
        }

        /* @var $reward Enterprise_Reward_Model_Reward */
        $reward = $quote->getRewardInstance();
        if (!$reward || !$reward->getId()) {
            return $this;
        }

        $baseQuoteGrandTotal = $quote->getBaseGrandTotal() + $quote->getBaseRewardCurrencyAmount();
        if ($reward->isEnoughPointsToCoverAmount($baseQuoteGrandTotal)) {
            $paymentCode = $observer->getEvent()->getMethodInstance()->getCode();
            $result = $observer->getEvent()->getResult();
            if (($quoteHelper->hasProductsToCreateNewSubscription($quote)
                    || !$quoteHelper->hasSubscriptionReorderProduct($quote))
                && $paymentHelper->isSubscribeProCreditCardMethod($paymentCode)
            ) {
                // Don't do extra check
            }
            else {
                $result->isAvailable = $paymentCode === 'free' && empty($result->isDeniedInConfig);
            }
        }
        return $this;
    }

}

