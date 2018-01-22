<?php

/**
 * Class Mediotype_OffersTab_Block_Offers
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Block_Offers extends Mage_Core_Block_Template
{
	protected $_offersTabHelper;

	protected $_offers; // Will hold array of offers to show user

	/**
	 * Mediotype_OffersTab_Block_Offers constructor.
	 * @param array $args
	 */
	public function __construct(array $args = array())
	{
		parent::__construct($args);

		$this->_offersTabHelper = Mage::helper("mediotype_offerstab");

		// Populate $this->_offers appropriately
		$this->_offers = $this->_offersTabHelper->getFilteredOffers();
	}
}
