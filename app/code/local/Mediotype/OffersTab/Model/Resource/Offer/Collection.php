<?php

/**
 * Class Collection
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Model_Resource_Offer_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	/**
	 *
	 */
	protected function _construct()
	{
		$this->_init('mediotype_offerstab/offer');
	}
}
