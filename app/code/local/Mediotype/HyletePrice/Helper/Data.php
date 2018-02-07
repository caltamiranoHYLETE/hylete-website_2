<?php

/**
 * Class Mediotype_HyletePrice_Helper_Data
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_HyletePrice_Helper_Data extends Mage_Core_Helper_Abstract
{
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
}
