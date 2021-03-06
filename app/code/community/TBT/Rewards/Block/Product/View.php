<?php

/**
 * Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Sweet Tooth SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).

 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, Sweet Tooth is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by Sweet Tooth, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time Sweet Tooth spent 
 * during the support process.
 * Sweet Tooth does not guarantee compatibility with any other framework extension. 
 * Sweet Tooth is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@sweettoothrewards.com or call 1.855.699.9322, so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2014 Sweet Tooth Inc. (http://www.sweettoothrewards.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product View Points
 * @deprecated as of Sweet Tooth 1.3.0.6
 * @see TBT_Rewards_Block_Product_View_Points instead
 *
 * @category   TBT
 * @package    TBT_Rewards
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Block_Product_View extends Mage_Catalog_Block_Product_View {
	
	/**
	 * Get JSON encripted configuration array which can be used for JS dynamic
	 * price calculation depending on product options
	 *
	 * @return string
	 */
	public function getJsonConfig() {
		if (! Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.0.0' ))
			return parent::getJsonConfig ();
		$config = array ();
		
		$_request = Mage::getSingleton ( 'tax/calculation' )->getRateRequest ( false, false, false );
		$_request->setProductClassId ( $this->getProduct ()->getTaxClassId () );
		$defaultTax = Mage::getSingleton ( 'tax/calculation' )->getRate ( $_request );
		
		$_request = Mage::getSingleton ( 'tax/calculation' )->getRateRequest ();
		$_request->setProductClassId ( $this->getProduct ()->getTaxClassId () );
		$currentTax = Mage::getSingleton ( 'tax/calculation' )->getRate ( $_request );
		
		$_regularPrice = $this->getProduct ()->getPrice ();
		$_finalPrice = $this->getProduct ()->getFinalPrice ();
		$_priceInclTax = Mage::helper ( 'tax' )->getPrice ( $this->getProduct (), $_finalPrice, true );
		$_priceExclTax = Mage::helper ( 'tax' )->getPrice ( $this->getProduct (), $_finalPrice );
		
		$config = array ('productId' => $this->getProduct ()->getId (), 'priceFormat' => Mage::app ()->getLocale ()->getJsPriceFormat (), 'includeTax' => Mage::helper ( 'tax' )->priceIncludesTax () ? 'true' : 'false', 'showIncludeTax' => Mage::helper ( 'tax' )->displayPriceIncludingTax (), 'showBothPrices' => Mage::helper ( 'tax' )->displayBothPrices (), 'productPrice' => Mage::helper ( 'core' )->currency ( $_finalPrice, false, false ), 'productOldPrice' => Mage::helper ( 'core' )->currency ( $_regularPrice, false, false ), 'skipCalculate' => ($_priceExclTax != $_priceInclTax ? 0 : 1), 'defaultTax' => $defaultTax, 'currentTax' => $currentTax, 'idSuffix' => '_clone', 'oldPlusDisposition' => 0, 'plusDisposition' => 0, 'oldMinusDisposition' => 0, 'minusDisposition' => 0 );
		
		$responseObject = new Varien_Object ();
		Mage::dispatchEvent ( 'catalog_product_view_config', array ('response_object' => $responseObject ) );
		if (is_array ( $responseObject->getAdditionalOptions () )) {
			foreach ( $responseObject->getAdditionalOptions () as $option => $value ) {
				$config [$option] = $value;
			}
		}
		
		return Mage::helper ( 'core' )->jsonEncode ( $config );
	}

}
