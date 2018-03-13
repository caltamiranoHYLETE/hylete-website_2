<?php

/**
 * Class Mediotype_HyletePrice_Helper_Data
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_HyletePrice_Helper_Data extends Mage_Core_Helper_Abstract
{
    const DEFAULT_SPECIAL_PRICE_LABEL   = 'Sale';
    const PRODUCT_MSRP_PRICE_SELECTOR   = 4;
    const NOT_LOGGED_IN                 = 0;
    const EVERYDAY_ATHLETE              = 1;

    /**
     * @param $categoryId
     * @return mixed|string
     */
    public function isClearanceCategory($categoryId)
    {
        $attributeValue = Mage::getModel('catalog/category')->load($categoryId)->getData('is_hylete_price_clearance');
        if ($attributeValue == null) {
            $attributeValue = "0";
        }
        return $attributeValue;
    }

    /**
     * Generate a price type label for the current customer.
     * @param  Mage_Catalog_Model_Product $product  Optional product model for clearance label check.
     * @param integer $categoryId Optional category ID for clearance label check.
     * @return string
     */
    public function getPriceLabelByCustomerGroup(Mage_Catalog_Model_Product $product = null, $categoryId = null)
    {
        $groupId    = $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $group      = Mage::getModel('customer/group')->load($groupId);
        $typeId     = 'group';
        $label      = $group->getCustomerGroupHyletePriceLabel();

        if (is_null($label)) {
            $group = Mage::getModel('customer/group')->load(0);
            $label = $group->getCustomerGroupHyletePriceLabel();
        }

        // Must consider special price since its generator may also create a clearance label
        if ($categoryId && $product && $this->isClearanceCategory($categoryId) && !$this->hasSpecialPrice($product)) {
            $label  = 'Clearance';
            $typeId = 'clearance';
        }

        return sprintf(
            '<span class="price-label-%s-%d">%s</span>',
            $typeId,
            $group->getId(),
            $this->__($label)
        );
    }

    /**
     * Generate a special price label for the given product.
     * @param  Mage_Catalog_Model_Product $product    The product model.
     * @param  integer                    $categoryId Optional category ID for clearance label check.
     * @return string
     */
    public function getSpecialPriceLabelHtml(Mage_Catalog_Model_Product $product, $categoryId = null)
    {
        $label = $product->getResource()
            ->getAttribute('special_price_label')
            ->getSource()
            ->getOptionText($product->getSpecialPriceLabel());

        // Allow label to override clearance category if set
        if (!$label && $categoryId && $this->isClearanceCategory($categoryId)) {
            $label = 'Clearance';
        }

        //
        // Sample output:
        //   <span class="special-price-label price-label-option-id-1301 price-label-flash-sale">Flash Sale Price</span>
        //
        return sprintf(
            '<span class="special-price-label price-label-option-id-%d price-label-%s">%s</span>',
            (int) $product->getSpecialPriceLabel(),
            Mage::getSingleton('catalog/product_url')->formatUrlKey($label),
            $this->__($label ?: self::DEFAULT_SPECIAL_PRICE_LABEL)
        );
    }

    /**
     * @param Mage_Catalog_Model_Product $currentProduct
     * @param bool $isCategoryDetailsPage
     * @return string
     */
    public function hasProductMSRP($currentProduct, $isProductDetailsPage = false)
    {
        if (!$currentProduct || !$currentProduct->getMsrp()) {
            return '';
        }

        return 'hylete-price-label-' . ($isProductDetailsPage ? 'lg' : 'sm');
    }

    /**
     * Determine whether the given product has an active special price.
     * @param  Mage_Catalog_Model_Product $product The product model.
     * @return boolean
     */
    public function hasSpecialPrice(Mage_Catalog_Model_Product $product)
    {
        return (float) $product->getPriceModel()
            ->calculateSpecialPrice(
                (float) $product->getPrice(),
                (float) $product->getSpecialPrice(),
                $product->getSpecialFromDate(),
                $product->getSpecialToDate(),
                $product->getStore()
            ) !== (float) $product->getPrice();
    }

    /**
     * @return mixed
     */
    public function getPriceDifferenceCmsBlockByCustomerGroup()
    {
        $groupId = $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

        /** @var Mage_Customer_Model_Group $group */
        $group = Mage::getModel('customer/group')->load($groupId);

        $groupCmsBlock = $group->getHyletePriceCmsBlockIdentifier();

        if ($groupCmsBlock == null) {
            $groupCmsBlock = "hylete_price_difference_verbiage_default";
        }

        return Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($groupCmsBlock)->toHtml();
    }

    /**
     * Determine whether the original product price needs to be displayed based on the applied rules
     * If the sales rules are targeting MSRP discount we want to return the original product price
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param bool $isSubtotal
     * @return string
     */
    public function quoteItemSalesRulesForMsrpCalculation(Mage_Sales_Model_Quote_Item $item, $isSubtotal = false)
    {
        $itemPrice = (string) $item->getPrice();
        $quote = $item->getQuote();
        $hasMsrpTargetRule = $this->quoteHasMsrpTargetRule($quote);
        $catalogProduct = $item->getProduct();

        if ($hasMsrpTargetRule && !$isSubtotal) {
            $itemPrice = (string) $catalogProduct->getPrice();
        } elseif ($isSubtotal && !$hasMsrpTargetRule) {
            $itemPrice = $item->getPrice() * $item->getQty();
        } else if ($isSubtotal && $hasMsrpTargetRule) {
            $itemPrice = (string) $catalogProduct->getMsrp() * $item->getQty(); // Qty
        }

        return $itemPrice;
    }

    /**
     * Check if the current quote has an MSRP target rule applied to it
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function quoteHasMsrpTargetRule(Mage_Sales_Model_Quote $quote)
    {
        $hasMsrpTargetRule = false;
        $couponCode = $quote->getCouponCode();
        $websiteId = Mage::app()->getWebsite()->getId();
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $rules = [];

        $key = $websiteId . '_' . $customerGroupId . '_' . $couponCode;
        $rules[$key] = Mage::getResourceModel('salesrule/rule_collection')
            ->setValidationFilter($websiteId, $customerGroupId, $couponCode)
            ->load();

        foreach ($rules[$key] as $rule) {
            $ruleTargetPrice = $rule->getPriceSelector();

            if ($ruleTargetPrice == self::PRODUCT_MSRP_PRICE_SELECTOR) {
                $hasMsrpTargetRule = true;
            }
        }

        return $hasMsrpTargetRule;
    }

    /**
     * Check if the quote specifically has an MSRP target rule applied to it and calculate the original subtotal
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return float|int
     */
    public function quoteSalesRulesForMsrpCalculation(Mage_Sales_Model_Quote $quote)
    {
        $quoteSubTotal = $quote->getSubtotal();
        $hasMsrpTargetRule = $this->quoteHasMsrpTargetRule($quote);

        if ($hasMsrpTargetRule) {
            $items = $quote->getAllVisibleItems();
            $quoteSubTotal = $this->_calculateOriginalQuoteTotal($items);
        }

        return $quoteSubTotal;
    }

    /**
     * Calculation for the original subtotal as MSRP overrides the product price to correctly calculate totals
     *
     * @param $items
     * @return int
     */
    protected function _calculateOriginalQuoteTotal($items)
    {
        $originalPrice = 0;
        foreach ($items as $item) {
            $originalPrice += $item->getProduct()->getPrice() * $item->getQty();
        }

        return $originalPrice;
    }
}
