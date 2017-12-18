<?php

/**
 * Class Mediotype_HyletePrice_Helper_Data
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_HyletePrice_Helper_Data extends Mage_Core_Helper_Abstract
{
	private $_menClearanceCategoryId = "65"; // MYLES: TODO: Refactor into adminhtml form somewhere
	private $_wommenClearanceCategoryId = "76"; // MYLES: TODO: Refactor into adminhtml form somewhere

	/**
	 * Mediotype_HyletePrice_Helper_Data constructor.
	 */
	public function __construct()
	{
		$this->_clearanceCategories = array(
			$this->_menClearanceCategoryId,
			$this->_wommenClearanceCategoryId
		);
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

		if ($label == null || array_contains($this->_clearanceCategories, $currentCategory)) {
			$group = Mage::getModel('customer/group')->load(0);
			$label = $group->getCustomerGroupHyletePriceLabel();
		}

		$postamble = " Price";

		return $label . $postamble;
	}
}
