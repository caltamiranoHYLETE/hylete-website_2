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

class SubscribePro_Autoship_Block_Adminhtml_Sales_Order_Create_Product_Subscription
    extends Mage_Adminhtml_Block_Template
{

    /**
     * @return Mage_Sales_Model_Quote_Item
     */
    public function getQuoteItem()
    {
        return $this->getParentBlock()->getData('item');
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');
        return $quoteHelper->getRelevantProductFromQuoteItem($this->getQuoteItem());
    }

    /**
     * Return the product profile for the current product
     *
     * @return bool|\SubscribePro\Service\Product\ProductInterface
     */
    public function getSubscribeProProduct()
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Product $platformProductHelper */
        $platformProductHelper = Mage::helper('autoship/platform_product');
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');

        $apiHelper->setConfigStore($this->getQuoteItem()->getStore());

        return $platformProductHelper->getPlatformProduct($this->getProduct());
    }

    /**
     * Indicates whether this product is eligible for subscription or not
     *
     * @return bool
     */
    public function isItemSubscriptionEligible()
    {
        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $this->getQuoteItem()->getStore()) != '1') {
            return false;
        }

        return ($this->getSubscribeProProduct() instanceof \SubscribePro\Service\Product\ProductInterface);
    }

    /**
     * Is this product a trial subscription product?
     *
     * @return boolean
     */
    public function isTrialProduct()
    {
        $platformProduct = $this->getSubscribeProProduct();

        return ($platformProduct->getIsTrialProduct());
    }

    /**
     * Subscription option mode
     *
     * @return string
     */
    public function getSubscriptionOptionMode()
    {
        $platformProduct = $this->getSubscribeProProduct();

        return ($platformProduct->getSubscriptionOptionMode());
    }

    /**
     * Default subscription option
     *
     * @return string
     */
    public function getDefaultSubscriptionOption()
    {
        $platformProduct = $this->getSubscribeProProduct();

        return ($platformProduct->getDefaultSubscriptionOption());
    }

    /**
     * @return bool
     */
    public function isItemFlaggedToCreateNewSubscription()
    {
        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $this->getQuoteItem()->getStore()) != '1') {
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
        return $this->getSubscribeProProduct()->getIntervals();
    }

    /**
     * Gets minimal sales quantity
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int|null
     */
    public function getMinimalQty($product)
    {
        $stockItem = $product->getStockItem();
        if ($stockItem) {
            return ($stockItem->getMinSaleQty()
            && $stockItem->getMinSaleQty() > 0 ? $stockItem->getMinSaleQty() * 1 : null);
        }
        return null;
    }

    /**
     * Get default qty - either as preconfigured, or as 1.
     * Also restricts it by minimal qty.
     *
     * @param null|Mage_Catalog_Model_Product $product
     * @return int|float
     */
    public function getProductDefaultQty($product = null)
    {
        if (!$product) {
            $product = $this->getProduct();
        }

        $qty = $this->getMinimalQty($product);
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }

        return $qty;
    }

    /**
     * Return the discount text for display on product page
     *
     * @return string Discount text for product page
     */
    public function getDiscountText()
    {
        /** @var SubscribePro_Autoship_Helper_Subscription $subscriptionHelper */
        $subscriptionHelper = Mage::helper('autoship/subscription');
        return $subscriptionHelper->getSubscriptionPriceText($this->getSubscribeProProduct(), $this->getProduct(), $this->getProductDefaultQty($this->getProduct()));
    }

}