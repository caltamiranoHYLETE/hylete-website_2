<?php

/**
 * Class Mediotype_OffersTab_Model_Offer
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

	/**
	 * Get assigned customer group IDs.
	 * @return array
	 */
	public function getCustomerGroupIds()
	{
		if (!is_array($this->_getData('customer_group_ids'))) {
			return array_filter(explode(',', $this->_getData('customer_group_ids')), 'is_numeric');
		}

		return $this->_getData('customer_group_ids');
	}
}
