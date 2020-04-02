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
 * Helper class to assist with displaying and formatting subscription
 */
class SubscribePro_Autoship_Helper_Subscription extends Mage_Core_Helper_Abstract
{

    /**
     * Return the price for purchasing the product as a one time purchase, optionally format the returned price
     *
     * @param Mage_Catalog_Model_Product $product Mage product object
     * @param int $qty Product quantity for order, goes into catalog price calculation
     * @param bool $formatted True to return the price formatted, false to return the raw price number
     * @param null $inclTax
     * @return string Price of product, either formatted or as a raw number
     * @internal param bool $factorTax
     */
    public function getOneTimePurchasePrice(Mage_Catalog_Model_Product $product, $qty = 1, $formatted = false, $inclTax = null)
    {
        // Set customer group and store on product
        $product->setStoreId(Mage::app()->getStore()->getId());
        $product->setCustomerGroupId(Mage::getSingleton('customer/session')->getCustomer()->getGroupId());
        // Lookup price - Get catalog rule / special price / tier pricing / etc calculation
        $finalPrice = $product->getFinalPrice($qty);
        // If the product isn't discounted then default back to the original price
        if ($finalPrice===false) {
            $finalPrice = $product->getPrice();
        }
        if (is_null($inclTax)) {
            $inclTax = Mage::helper("tax")->displayPriceIncludingTax();
        }
        /**
         * Get the final price based on whether or not we've included tax
         */
        $finalPrice = $inclTax ? $this->getProductPriceInclTax($product, $finalPrice) : $this->getProductPriceExclTax($product, $finalPrice);
        // Format price if requested
        if ($formatted) {
            $finalPrice = Mage::helper('core')->currency($finalPrice, true, false);
        }

        // Return price
        return $finalPrice;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int $qty
     * @return bool
     */
    protected function isProductDiscountedInCatalog(Mage_Catalog_Model_Product $product, $qty = 1)
    {
        // Lookup final price
        $finalPrice = $this->getOneTimePurchasePrice($product, $qty);
        //Adjust the normal price for tax settings
        $productPrice = $this->adjustProductPriceForTax($product, $product->getPrice());

        // Check if product discounted
        $isProductDiscounted = ($finalPrice != $productPrice);

        return $isProductDiscounted;
    }

    /**
     * Return the price for purchasing the product with a subscription, optionally format the returned price
     *
     * @param SubscribePro\Service\Product\ProductInterface $platformProduct Subscription profile for product
     * @param Mage_Catalog_Model_Product $product Mage product object
     * @param int $qty Product quantity for order, goes into catalog price calculation
     * @param bool $formatted True to return the price formatted, false to return the raw price number
     * @return string Price of product, either formatted or as a raw number
     */
    public function getSubscriptionPrice(SubscribePro\Service\Product\ProductInterface $platformProduct, Mage_Catalog_Model_Product $product,
        $qty = 1, $formatted = false)
    {
        // Trial product info
        $isTrialProduct = $platformProduct->getIsTrialProduct();
        $trialPrice = $platformProduct->getTrialPrice();
        // Lookup discount % / amount
        $discount = $platformProduct->getDiscount();
        $isDiscountPercentage = $platformProduct->getIsDiscountPercentage();
        // Lookup final price
        $finalPrice = $this->getOneTimePurchasePrice($product, $qty);
        // Get config settings
        // Don't need $store param to getStoreConfig.  This method only called from frontend where store already set.
        $applyDiscountToCatalogPrice = Mage::getStoreConfig('autoship_subscription/discount/apply_discount_to_catalog_price');

        // Check for trial product
        if ($isTrialProduct) {
            // Set trial price as subscription price
            $subscriptionPrice = $trialPrice;
        }
        else {
            // Calculate discount using all biz logic
            if ($discount > 0.0) {
                if (!$applyDiscountToCatalogPrice && $this->isProductDiscountedInCatalog($product, $qty)) {
                    // Don't apply any subscription discount, because config is turned off and product already has discount
                    $subscriptionPrice = $finalPrice;
                }
                else {
                    if ($isDiscountPercentage) {
                        /**
                         * In the case of a percentage discount, we need to apply a discount to only the portion of the product price
                         * that is "discountable"
                         */
                        $subscriptionPrice = $finalPrice - ($this->getDiscountableProductPrice($product, $qty) * $discount);
                    }
                    else {
                        $subscriptionPrice = $finalPrice - $discount;
                    }
                }
            }
            else {
                $subscriptionPrice = $finalPrice;
            }
        }

        // Format price if requested
        if ($formatted) {
            $subscriptionPrice = Mage::helper('core')->currency($subscriptionPrice, true, false);
        }

        // Return price
        return $subscriptionPrice;
    }

    /**
     * Return the price for purchasing the product with a subscription, formatted and with text indicating the discount amount
     *
     * @param SubscribePro\Service\Product\ProductInterface $platformProduct Subscription profile for product
     * @param Mage_Catalog_Model_Product $product Mage product object
     * @return string Price of product, formatted and with text indicating the discount
     */
    public function getSubscriptionPriceText(SubscribePro\Service\Product\ProductInterface $platformProduct, Mage_Catalog_Model_Product $product, $qty = 1)
    {
        // Lookup config setting
        // Trial product info
        $isTrialProduct = $platformProduct->getIsTrialProduct();
        // Don't need $store param to getStoreConfig.  This method only called from frontend where store already set.
        $applyDiscountToCatalogPrice = Mage::getStoreConfig('autoship_subscription/discount/apply_discount_to_catalog_price');
        // Lookup discount % / amount
        $discount = $platformProduct->getDiscount();
        $isDiscountPercentage = $platformProduct->getIsDiscountPercentage();
        // Lookup price, including discount using method from SubscribePro_Autoship_Block_Product_View
        $priceFormatted = $this->getSubscriptionPrice($platformProduct, $product, $qty, true);
        //Start an array to keep track of variables which can be used in translation code
        $translateVars = array($priceFormatted);
        // Build output text
        $priceText = '%s';
        // Add discount text
        if ($isTrialProduct) {
            $priceText .= ' trial price.';
        }
        else {
            if ($discount > 0.0 && ($applyDiscountToCatalogPrice || !$this->isProductDiscountedInCatalog($product, $qty))) {
                if ($isDiscountPercentage) {
                    $translateVars[] = 100.0 * $discount;
                    //%% = literal '%' character
                    $priceText .= ' with %s%% subscription discount.';
                    if (Mage::helper("tax")->needPriceConversion() && !Mage::helper("tax")->discountTax() &&
                        Mage::helper("tax")->displayPriceIncludingTax()
                    ) {
                        //If we are displaying a discount in which the math doesn't add up, display an additional note so the user is aware
                        $translateVars[] = substr($priceText, 0, -1);
                        $priceText = '%s (incl. tax).';
                    }
                }
                else {
                    $translateVars[] = Mage::helper('core')->currency($discount, true, false);
                    $priceText .= ' with %s subscription discount.';
                }
            }
        }

        // return text
        array_unshift($translateVars, $priceText);
        // translate expects each variable as a separate argument (not 1 argument of an array of variables)
        return call_user_func_array(array($this, '__'), $translateVars);
    }

    /**
     * Method inspired by Mage_Checkout_Model_Api_Resource_Product::_getQuoteItemByProduct
     * Get QuoteItem by Product and request info
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Object $requestInfo
     * @return Mage_Sales_Model_Quote_Item
     * @throw Mage_Core_Exception
     */
    protected function getQuoteItemByProduct(Mage_Sales_Model_Quote $quote, Mage_Catalog_Model_Product $product,
        Varien_Object $requestInfo)
    {
        $cartCandidates = $product->getTypeInstance(true)
            ->prepareForCartAdvanced(
                $requestInfo,
                $product,
                Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_FULL
            );

        /**
         * Error message
         */
        if (is_string($cartCandidates)) {
            Mage::throwException($cartCandidates);
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }

        /** @var $item Mage_Sales_Model_Quote_Item */
        $item = null;
        foreach ($cartCandidates as $candidate) {
            if ($candidate->getParentProductId()) {
                continue;
            }

            $item = $quote->getItemByProduct($candidate);
        }

        if (is_null($item)) {
            $item = Mage::getModel('sales/quote_item');
        }

        return $item;
    }

    /**
     * Get the product price including tax
     * @param Mage_Catalog_Model_Product $product
     * @param $finalPrice
     * @return float
     */
    public function getProductPriceInclTax(Mage_Catalog_Model_Product $product, $finalPrice)
    {
        $priceInclTax = Mage::helper("tax")->priceIncludesTax();
        if (!$priceInclTax) {
            $finalPrice = Mage::helper("tax")->getPrice($product, $finalPrice, true);
        }
        return $finalPrice;
    }

    /**
     * Get the product price excluding tax
     * @param Mage_Catalog_Model_Product $product
     * @param $finalPrice
     * @return float
     */
    public function getProductPriceExclTax(Mage_Catalog_Model_Product $product, $finalPrice)
    {
        $priceInclTax = Mage::helper("tax")->priceIncludesTax();
        if ($priceInclTax) {
            $finalPrice = Mage::helper("tax")->getPrice($product, $finalPrice, false);
        }
        return $finalPrice;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int $qty
     * @return float
     */
    public function getDiscountableProductPrice(Mage_Catalog_Model_Product $product, $qty = 1)
    {
        $applyDiscountToTax = Mage::helper("tax")->discountTax();
        $finalPrice = $this->getOneTimePurchasePrice($product, $qty, false, $applyDiscountToTax); //Get the one time Purchase price, with or without tax
        return $finalPrice;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param $productPrice
     * @return mixed
     */
    public function adjustProductPriceForTax(Mage_Catalog_Model_Product $product, $productPrice)
    {
        /**
         * Adjust the product price based on tax settings
         */
        $displayWithTax = Mage::helper("tax")->displayPriceIncludingTax();
        $priceInclTax = Mage::helper("tax")->priceIncludesTax();
        if ($displayWithTax && !$priceInclTax) {
            $productPrice = Mage::helper("tax")->getPrice($product, $productPrice, true);
        } elseif ($priceInclTax && !$displayWithTax) {
            $productPrice = Mage::helper("tax")->getPrice($product, $productPrice, false);
        }
        return $productPrice;
    }
}
