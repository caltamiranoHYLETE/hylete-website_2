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
	 *
	 * MYLES: TODO: Filtering / SELECTing only matching Offers for display
	 */
	public function getFilteredOffers()
	{
		$model = Mage::getModel('mediotype_offerstab/offer');
		$collection = $model->getCollection();
		$collection->load();

		$offers = array();

		foreach ($collection->getItems() as $item) {
			$offers[] = $item;
		}

		return $offers;
	}
}
