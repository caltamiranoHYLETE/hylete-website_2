<?php

/**
 * Rewrite shipping block in order to hide it in the shopping cart totals view
 * Class Globale_Browsing_Block_Rewrite_Tax_Checkout_Shipping
 */
class Globale_Browsing_Block_Rewrite_Tax_Checkout_Shipping extends Mage_Tax_Block_Checkout_Shipping {

	/**
	 * Hiding shipping block from cart page for Global-e operated countries
	 * @param array $args
	 */
	public function __construct(array $args)
	{
		if(Mage::registry('globale_user_supported')){
			$this->_template = 'globale/tax/checkout/shipping.phtml';
		}
		parent::__construct($args);
	}

}