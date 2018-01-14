<?php

/**
 * Class Observer
 */
class Mediotype_HyletePrice_Model_Observer extends Amasty_Rules_Model_Observer
{
	/**
	 * Observer constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Responsible for adding the "is_on_flash_sale" attribute to catalog_product SELECTs
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function addIsFlashSaleAttribute(Varien_Event_Observer $observer)
	{
		if ($observer) {
			$collection = $observer->getEvent()->getCollection();

			$collection->addAttributeToSelect('is_on_flash_sale');
		}
	}

	/**
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function handleFormCreation(Varien_Event_Observer $observer)
	{
		parent::handleFormCreation($observer);

		if ($observer) {
			$event = $observer->getEvent();
		}

		return $this;
	}
}
