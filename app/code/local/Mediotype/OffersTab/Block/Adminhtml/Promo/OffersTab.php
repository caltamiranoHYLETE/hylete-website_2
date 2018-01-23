<?php

/**
 * Class Mediotype_OffersTab_Block_Adminhtml_Promo_OffersTab
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Block_Adminhtml_Promo_OffersTab extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	/**
	 * Mediotype_OffersTab_Block_Adminhtml_Promo_OffersTab constructor.
	 */
	public function __construct()
	{
		$this->_blockGroup = 'mediotype_offerstab';
		$this->_controller = 'adminhtml_promo_offerstab';
		$this->_headerText = Mage::helper('adminhtml')->__('Offers Tab Offers');

		parent::__construct();
	}
}
