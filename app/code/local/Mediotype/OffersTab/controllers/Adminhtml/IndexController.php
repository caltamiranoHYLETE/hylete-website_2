<?php

/**
 * Class Mediotype_OffersTab_Adminhtml_IndexController
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
	/**
	 *
	 */
	public function indexAction()
	{
		$this->_title($this->__('Promo'))->_title($this->__('Offers Tab'));
		$this->loadLayout();
		$this->_setActiveMenu('promo/mediotype_offerstab');
		$this->renderLayout();
	}
}
