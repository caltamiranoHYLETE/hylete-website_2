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

		if ($groupId == 37) {
			$groupLabel = "Investor";

		} else {
			$groupLabel = "Hylete";
		}

		$postamble = " Price";

		return $groupLabel . $postamble;
	}
}
