<?php

/**
 * Class Offer
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Offer extends Mage_Core_Model_Resource_Db_Abstract
{
	/**
	 *
	 */
	protected function _construct()
	{
		$this->_init('mediotype_offerstab/offer', 'offer_id');
	}
}

