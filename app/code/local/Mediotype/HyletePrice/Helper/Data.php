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
	 * @return mixed
	 */
	public function getPriceDifferenceCmsBlockByCustomerGroup()
	{
		$groupId = $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

		/** @var Mage_Customer_Model_Group $group */
		$group = Mage::getModel('customer/group')->load($groupId);

		// MYLES: Smells
		$groupCode = $group->getCustomerGroupCode();

		switch ($groupCode) {
			case "NOT LOGGED IN":
			default:
				return Mage::app()->getLayout()->createBlock('cms/block')->setBlockId('hylete_price_difference_verbiage_default')->toHtml();
				break;

			case "HYLETE Investors":
				return Mage::app()->getLayout()->createBlock('cms/block')->setBlockId('hylete_price_difference_verbiage_investor')->toHtml();
		}


	}
}
