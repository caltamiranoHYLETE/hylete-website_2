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
 * Cart Redeem Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Checkout_OnepageController extends Mage_Core_Controller_Front_Action 
{	
	protected function _loadValidItem($item_id = null) {
		if ($item_id === null) {
			$item_id = ( int ) $this->getRequest ()->getParam ( 'item_id' );
		}
		if (! $item_id) {
			$this->_forward ( 'noRoute' );
			return null;
		}
		
		$item = Mage::getModel ( 'sales/quote_item' )->load ( $item_id );
		
		return $item;
	}
	
	protected function _loadValidQuote($quote_id = null) {
		if ($quote_id === null) {
			$quote_id = ( int ) $this->getRequest ()->getParam ( 'quote_id' );
		}
		if (! $quote_id) {
			$this->_forward ( 'noRoute' );
			return null;
		}
		
		$quote = Mage::getModel ( 'sales/quote' )->load ( $quote_id );
		
		return $quote;
	}
	
	protected function getQuote() {
		return $this->_getSess ()->getQuote ();
	}
	
	protected function _getSess() {
		return Mage::getSingleton ( 'checkout/session' );
	}
	
	protected function _getCart() {
		return Mage::getSingleton ( 'checkout/cart' );
	}
	
	public function setPointsSpendingAction() {
		$rule_id = $this->getRequest ()->getParam ( 'sr_id' );
		$amount = $this->getRequest ()->getParam ( 'amount' );
		$currency = 1; //$this->getRequest()->getParam('curr');
		

		try {
			//$this->enableRedemption($rule_id);
			

			$rule = Mage::getModel ( 'rewards/salesrule_rule' )->load ( $rule_id );
			if (! $rule->getId ()) {
				throw new Exception ( $this->__ ( "Sorry, unable to spend your points this way for this shopping cart." ) );
			}
			
			if (! $this->getQuote ()->hasItems ()) {
				throw new Exception ( $this->__ ( "It seems like your cart may have expired, so points weren't applied.  Add some products to your cart or re-login." ) );
			}
			
			if ($amount < 0) {
				throw new Exception ( $this->__ ( "Please specify an amount greater than zero." ) );
			}
			
			$max_usable_points = $this->getQuote ()->getMaxUsablePoints ( $rule );
			
			if ($amount > $max_usable_points) {
				$amount = $max_usable_points;
				$max_points_spending = Mage::getModel ( 'rewards/points' )->set ( 1, $max_usable_points );
				$this->_getSess ()->addNotice ( $this->__ ( "You can't spend more than %s.", $max_points_spending ) );
			}
			$points_spending = Mage::getModel ( 'rewards/points' )->set ( 1, $amount );
			
			$rule->_setPointsSpending ( $amount );
			$this->_getSess ()->addSuccess ( $this->__ ( "You are now spending %s on this cart.", $points_spending ) );
		} catch ( Exception $e ) {
			$this->_getSess ()->addError ( $e->getMessage () );
		}
		
		$this->_redirect ( 'checkout/cart/' );
		return;
	}
	
	public function updatePointsSpendingAction() {
		$new_points_spending = $this->getRequest ()->getParam ( "points_spending" );
        $cart = $this->_getCart ();
        $quote = $cart->getQuote();

		if ($this->isValidSpendingAmount ( $new_points_spending )) {
			$quote->setPointsSpending ( $new_points_spending );
		}
		
		$quote->collectTotals ();
		
		$this->loadLayout ( 'checkout_onepage_review' );
		$response = $this->getLayout ()->getBlock ( 'root' )->toHtml ();
		$this->getResponse ()->setBody ( $response );
		return;
	}
	
	public function getGrandTotalAction() 
        {
            $this->_getCart()->init();
            $this->_getCart()->save();
            
            $response = Mage::app()->getStore()->formatPrice($this->getQuote()->load($this->getQuote()->getId())->getGrandTotal());
            $this->getResponse()->setBody($response);
	}
	
	public function getSliderContentAction() {
		$response = $this->getLayout ()->createBlock ( 'rewards/checkout_cart_slider' )->setTemplate ( 'rewards/checkout/cart/slider.phtml' )->toHtml ();
		$this->getResponse ()->setBody ( $response );
		return;
	}
	
	protected function isValidSpendingAmount($sp) {
		return true;
	}
}
