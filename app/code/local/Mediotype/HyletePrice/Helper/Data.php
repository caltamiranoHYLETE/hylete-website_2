<?php

/**
 * Class Mediotype_HyletePrice_Helper_Data
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_HyletePrice_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PRODUCT_MSRP_PRICE_SELECTOR = 4;
    const NOT_LOGGED_IN = 0;
    const EVERYDAY_ATHLETE = 1;

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
     * @param $currentCategory
     * @return string
     */
    public function getPriceLabelByCustomerGroup($currentCategory = null)
    {
        $groupId = $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $group = Mage::getModel('customer/group')->load($groupId);

        $label = $group->getCustomerGroupHyletePriceLabel();

        if ($label == null) {
            $group = Mage::getModel('customer/group')->load(0);
            $label = $group->getCustomerGroupHyletePriceLabel();
        }

        if ($currentCategory && $this->isClearanceCategory($currentCategory)) {
            $label = "clearance";  // MYLES: No reason not to make this configurable as well
        }

        $postamble = " price";

        return $label . $postamble;
    }

    /**
     * @param $currentCategory
     * @return string
     */
    public function getPriceLabelByCustomerGroupAndProduct($currentCategory, $currentProduct)
    {
        $groupId = $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $group = Mage::getModel('customer/group')->load($groupId);

        $label = $group->getCustomerGroupHyletePriceLabel();

        if ($label == null) {
            $group = Mage::getModel('customer/group')->load(0);
            $label = $group->getCustomerGroupHyletePriceLabel();
        }

        if ($this->isClearanceCategory($currentCategory)) {
            $label = "clearance"; // MYLES: No reason not to make this configurable as well
        }

        $label .= " price"; // MYLES: This is where the read to a configurable value in adminhtml needs to go

        // MYLES: Determine what to do on collisions with group label
        if($currentProduct->getIsOnFlashSale()) {
            $label = "<span style=\"color:#34BAF3\">FLASH SALE</span>";
        }

        return $label;
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
     * @return string
     */
    public function quoteItemSalesRulesForMsrpCalculation(Mage_Sales_Model_Quote_Item $item)
    {
        $hasMsrpTargetRule = false;
        $couponCode = $item->getQuote()->getShippingAddress()->getCouponCode();
        $originalPrice = $item->getProduct()->getPrice();
        $price = $item->getPrice();
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

        return ($hasMsrpTargetRule ? $originalPrice : $price);
    }

    public function quoteSalesRulesForMsrpCalculation(Mage_Sales_Model_Quote $quote)
    {
        $hasMsrpTargetRule = 0;
        $couponCode = $quote->getCouponCode();
        $items = $quote->getAllVisibleItems();

        $originalPrice = $this->_calculateOriginalQuoteTotal($items);
        $price = $quote->getSubtotal();
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
                $hasMsrpTargetRule = 1;
            }
        }

        return ($hasMsrpTargetRule ? $originalPrice : $price);
    }

    protected function _calculateOriginalQuoteTotal($items)
    {
        $originalPrice = 0;
        foreach ($items as $item) {
            $originalPrice += $item->getProduct()->getPrice() * $item->getQty();
        }

        return $originalPrice;
    }
}
