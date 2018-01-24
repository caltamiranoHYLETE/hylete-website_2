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

		foreach ($collection->getItems() as $offer) {
			$categoryMatch = false;
			$productMatch = false;
			$customerGroupMatch = false;

			$offerCategories = $offer->getCategoryIds();
			$offerProducts = $offer->getProductIds();
			$offerCustomerGroups = $offer->getCustomerGroupIds();

			// Check categories
			if ($offerCategories == NULL) {
				$categoryMatch = true;

			} else {
				$categories = explode(",", $offerCategories);

				if ($filterCategory != null && array_contains($categories, $filterCategory->getId())) {
					$categoryMatch = true;
				}
			}

			// Check products
			if ($offerProducts == NULL) {
				$productMatch = true;

			} else {
				$products = explode(",", $offerProducts);

				if ($filterProduct != null && array_contains($products, $filterProduct->getId())) {
					$productMatch = true;
				}
			}

			// Check customer group
			if ($offerCustomerGroups == NULL) {
				$customerGroupMatch = true;

			} else {
				$customerGroups = explode(",", $offerCustomerGroups);

				$filterCustomerGroup = (string) $filterCustomerGroup;

				if (array_contains($customerGroups, $filterCustomerGroup)) {
					$customerGroupMatch = true;
				}
			}

			// Add to list if applicable
			if ($categoryMatch && $productMatch && $customerGroupMatch) {
				$offers[] = $offer;
			}
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
