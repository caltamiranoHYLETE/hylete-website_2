<?php

/**
 * Class Mediotype_OffersTab_Helper_Data
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Mediotype_OffersTab_Helper_Data constructor.
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
		$filterCategory = $this->_getCurrentCategory();
		$filterProduct = $this->_getCurrentProduct();
		$filterCustomerGroup = $this->_getCurrentCustomerGroup();

		$model = Mage::getModel('mediotype_offerstab/offer');
		$collection = $model->getCollection();
		$collection->setOrder('priority', 'DESC');
		$collection->load();

		$offers = array();

		foreach ($collection->getItems() as $item) {
			$offers[] = $item;
		}

		return $offers;
	}

	/**
	 * @return mixed
	 */
	protected function _getCurrentCategory()
	{
		return Mage::registry('current_category');
	}

	/**
	 * @return mixed
	 */
	protected function _getCurrentProduct()
	{
		return Mage::registry('current_product');
	}

	/**
	 * @return mixed
	 */
	protected function _getCurrentCustomerGroup()
	{
		return Mage::getSingleton('customer/session')->getCustomerGroupId();
	}
}
