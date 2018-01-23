<?php

/**
 * Class Offer
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Model_Offer extends Mage_Core_Model_Abstract
{
	/**
	 *
	 */
	protected function _construct()
	{
		parent::_construct();

		$this->_init('mediotype_offerstab/offer');
	}
}
