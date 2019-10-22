<?php
/**
 * This is a replacement model for the removed Globale Extension
 * This prevents errors loading orders that were placed with the Globale extension
 */
class HyleteShipping_Shipping_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
	protected $_code = 'globale';
	protected $_canUseCheckout = false;
	protected $_canCancelInvoice = true;

	/**
	 * Payment method supposed not to be available on local checkout
	 * @param null $quote
	 * @return bool
	 */
	public function isAvailable($quote = null)
	{
		if( !Mage::registry('globale_user_supported') && !Mage::registry('globale_api') ){
			return false;
		}
		else{
			return parent::isAvailable($quote);
		}
	}
}