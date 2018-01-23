<?php

/**
 * Class Mediotype_OffersTab_Block_Adminhtml_Promo_OffersTab
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Block_Adminhtml_Promo_OffersTab extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Mediotype_OffersTab_Block_Adminhtml_Promo_OffersTab constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_blockGroup = 'mediotype_offerstab';
		$this->_controller = 'adminhtml_sales_order';
		$this->_headerText = Mage::helper('mediotype_offerstab')->__('OffersTab');
	}
}
