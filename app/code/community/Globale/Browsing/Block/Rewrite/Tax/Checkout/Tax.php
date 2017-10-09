<?php
class Globale_Browsing_Block_Rewrite_Tax_Checkout_Tax extends Mage_Tax_Block_Checkout_Tax {

	/**
	 * Hiding tax block from cart page for Global-e operated countries
	 * @param array $args
	 */
	public function __construct(array $args)
	{
		if(Mage::registry('globale_user_supported')){
			$this->_template = 'globale/tax/checkout/tax.phtml';
		}
		parent::__construct($args);
	}

}