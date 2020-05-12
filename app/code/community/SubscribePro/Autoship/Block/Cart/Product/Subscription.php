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

class SubscribePro_Autoship_Block_Cart_Product_Subscription extends Mage_Core_Block_Template
{

    /**
     * Get quote item for this block
     *
     * @return Mage_Sales_Model_Quote_Item|null
     */
    public function getQuoteItem()
    {
        // Get quote item from parent block
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = $this->getParentBlock()->getData('item');

        return $quoteItem;
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::helper('autoship/quote')->getRelevantProductFromQuoteItem($this->getQuoteItem());
    }

    /**
     * Return the product profile for the current product
     *
     * @return \SubscribePro\Service\Product\ProductInterface The Magento product profile entity object for the current product
     */
    public function getPlatformProduct()
    {
        return Mage::helper('autoship/platform_product')->getPlatformProduct($this->getProduct());
    }

    /**
     * Indicates whether this product is eligible for subscription or not
     *
     * @return bool
     */
    public function isItemSubscriptionEligible()
    {
        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled') != '1') {
            return false;
        }

        // Get product
        $product = $this->getProduct();
        // Lookup whether product enabled / disabled for subscription
        $isProductEnabled = Mage::helper('autoship/product')->isAvailableForSubscription($product);

        return $isProductEnabled;
    }

    /**
     * Is this product a trial subscription product?
     *
     * @return boolean
     */
    public function isTrialProduct()
    {
        $platformProduct = $this->getPlatformProduct();

        return ($platformProduct->getIsTrialProduct());
    }

    /**
     * Subscription option mode
     *
     * @return string
     */
    public function getSubscriptionOptionMode()
    {
        $platformProduct = $this->getPlatformProduct();

        return true;
        return ($platformProduct->getSubscriptionOptionMode());
    }

    /**
     * Default subscription option
     *
     * @return string
     */
    public function getDefaultSubscriptionOption()
    {
        $platformProduct = $this->getPlatformProduct();

        return ($platformProduct->getDefaultSubscriptionOption());
    }

    /**
     * @return bool
     */
    public function isItemFlaggedToCreateNewSubscription()
    {
        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled') != '1') {
            return false;
        }

        // Get quote item
        $quoteItem = $this->getQuoteItem();
        // Return subscription flag
        return $quoteItem->getData('create_new_subscription_at_checkout');
    }

    /**
     * Get new subscription interval set on current quote item
     *
     * @return string
     */
    public function getNewSubscriptionInterval()
    {
        // Get quote item
        $quoteItem = $this->getQuoteItem();
        // Return subscription flag
        return $quoteItem->getData('new_subscription_interval');
    }

    /**
     * Return eligible subscription intervals for this product
     *
     * @return array Array of eligible subscription interval strings (for example: One Month, Two Months, etc)
     */
    public function getIntervals()
    {
        return $this->getPlatformProduct()->getIntervals();
    }

}
