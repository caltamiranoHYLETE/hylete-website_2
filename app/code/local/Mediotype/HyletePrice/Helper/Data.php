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
	public function getPriceLabelByCustomerGroup($currentCategory)
	{
		$groupId = $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		$group = Mage::getModel('customer/group')->load($groupId);

		$label = $group->getCustomerGroupHyletePriceLabel();

		if ($label == null) {
			$group = Mage::getModel('customer/group')->load(0);
			$label = $group->getCustomerGroupHyletePriceLabel();
		}

		if ($this->isClearanceCategory($currentCategory)) {
			$label = "Clearance";
		}

		$postamble = " Price";

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
			$label = "Clearance";
		}

		$label .= " Price";

		// MYLES: If customer is 'investor' and investor price is $30 and special price is $31, then
		// $30 is shown, with the 'FLASH SALE' text. Need to account for this.
		if($currentProduct->getIsOnFlashSale()) {
			$label = "<span style=\"color:#34BAF3\">FLASH SALE</span>";
		}

		return $label;
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
