<?php

/**
 * Class Mediotype_OffersTab_Helper_Data
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Mediotype_HyletePrice_Helper_Data constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Responsible for returning a list of CMS static block ids to display
	 */
	public function getFilteredOffers()
	{
		// MYLES: TODO: Test CMS block should be created by an SQL data script
		$cmsBlock1 = Mage::getModel('cms/block')->load('offers_tab_test_1');
		$cmsBlock2 = Mage::getModel('cms/block')->load('offers_tab_test_2');
		$cmsBlock3 = Mage::getModel('cms/block')->load('offers_tab_test_3');
		$cmsBlock4 = Mage::getModel('cms/block')->load('offers_tab_test_4');
		$cmsBlock5 = Mage::getModel('cms/block')->load('offers_tab_test_5');

		return array(
			$cmsBlock1,
			$cmsBlock2,
			$cmsBlock3,
			$cmsBlock4,
			$cmsBlock5
		);
	}
}
