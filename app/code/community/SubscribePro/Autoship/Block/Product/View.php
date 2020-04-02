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
 * Product View block - Override the Magento catalog block
 *
 */
class SubscribePro_Autoship_Block_Product_View extends Mage_Catalog_Block_Product_View
{

    private $_platformProduct = null;

    /**
     * Return true if product has options
     *
     * @return bool
     */
    public function hasOptions()
    {
        if ($this->isProductAutoshipEligible()) {
            return true;
        }
        else {
            return parent::hasOptions();
        }
    }

    /**
     * Return the product profile for the current product
     *
     * @return \SubscribePro\Service\Product\ProductInterface The Magento product profile entity object for the current product
     */
    public function getPlatformProduct()
    {
        if ($this->_platformProduct == null) {
            $this->_platformProduct = Mage::helper('autoship/platform_product')->getPlatformProduct($this->getProduct());
        }

        return $this->_platformProduct;
    }

    /**
     * Indicates whether this product is eligible for autoship or not
     *
     * @return bool
     */
    public function isProductAutoshipEligible()
    {
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
     * Return the price for purchasing the current product as a one time purchase, optionally format the returned price
     *
     * @param bool $formatted True to return the price formatted, false to return the raw price number
     * @return string Price of product, either formatted or as a raw number
     */
    public function getOneTimePurchasePrice($formatted = false)
    {
        return Mage::helper('autoship/subscription')
            ->getOneTimePurchasePrice($this->getProduct(), $this->getProductDefaultQty($this->getProduct()), $formatted);
    }

    /**
     * Return the price for purchasing the product with a subscription, optionally format the returned price
     *
     * @param bool $formatted True to return the price formatted, false to return the raw price number
     * @return string Price of product, either formatted or as a raw number
     */
    public function getSubscriptionPrice($formatted = false)
    {
        return Mage::helper('autoship/subscription')->getSubscriptionPrice($this->getPlatformProduct(), $this->getProduct(), $this->getProductDefaultQty($this->getProduct()), $formatted);
    }

    /**
     * Gets minimal sales quantity
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int|null
     */
    public function getMinimalQty($product)
    {
        $mageDefault = parent::getMinimalQty($product);

        if ($this->isProductAutoshipEligible()) {
            $platformProduct = $this->getPlatformProduct();
            if ($platformProduct->getSubscriptionOptionMode() == 'subscription_only') {
                if ($platformProduct->getMinQty()) {
                    return (!is_null($mageDefault) && $mageDefault > $platformProduct->getMinQty())
                        ? $mageDefault
                        : $platformProduct->getMinQty();
                }
            }
        }

        return $mageDefault;
    }
}
