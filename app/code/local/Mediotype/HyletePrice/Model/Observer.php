<?php

/**
 * Class Observer
 */
class Mediotype_HyletePrice_Model_Observer
{
	/**
	 * Observer constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * @param Varien_Event_Observer $observer
	 */
	public function addIsFlashSaleAttribute(Varien_Event_Observer $observer)
	{
		if ($observer) {
			$collection = $observer->getEvent()->getCollection();

			$collection->addAttributeToSelect('is_on_flash_sale');
		}
	}
}
