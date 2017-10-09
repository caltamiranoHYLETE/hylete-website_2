<?php
class Globale_Browsing_Block_Rewrite_Checkout_Links extends Mage_Checkout_Block_Links {

	/**
	 * Change default Checkout Link to GE International CheckoutLink when GE browsing is operated
	 * @return Globale_Browsing_Block_Rewrite_Checkout_Links|Mage_Checkout_Block_Links
	 */
	public function addCheckoutLink(){
		$IsOperatedByGlobale = Mage::registry('globale_user_supported');

		if(empty($IsOperatedByGlobale)){
			return parent::addCheckoutLink();
		}
		return $this->addInternationalCheckoutLink();
	}


	/**
	 * Add GE International CheckoutLink
	 * @return $this
	 */
	protected function addInternationalCheckoutLink(){
		$FrontName = (string)Mage::getConfig()->getNode('frontend/routers/browsing/args/frontName');

		$ParentBlock = $this->getParentBlock();
		if ($ParentBlock && Mage::helper('core')->isModuleOutputEnabled('Mage_Checkout')) {
			$Text = $this->__('Checkout');
			$ParentBlock->addLink(
				$Text, $FrontName.'/checkout', $Text,
				true, array('_secure' => true), 60, null,
				'class="top-link-checkout"'
			);
		}
		return $this;
	}
}