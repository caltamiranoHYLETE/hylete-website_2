<?php

/**
 * Class OfferController
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Adminhtml_OfferController extends Mage_Adminhtml_Controller_Action
{
	/**
	 *
	 */
	public function indexAction()
	{
		$this->loadLayout();
		$this->_addContent(
			$this->getLayout()->createBlock('mediotype_offerstab/adminhtml_promo_offerstab')
		);
		$this->renderLayout();
	}
}
