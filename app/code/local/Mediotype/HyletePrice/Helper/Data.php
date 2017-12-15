<?php

/**
 * Class Mediotype_HyletePrice_Helper_Data
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_HyletePrice_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * @return string
	 */
	public function getPriceLabelByCustomerGroup()
	{
		$groupId = $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		$group = Mage::getModel('customer/group')->load($groupId);

		$label = $group->getCustomerGroupHyletePriceLabel();

		if ($label == null) {
			$group = Mage::getModel('customer/group')->load(0);
			$label = $group->getCustomerGroupHyletePriceLabel();
		}

		$postamble = " Price";

		return $label . $postamble;
	}
}
