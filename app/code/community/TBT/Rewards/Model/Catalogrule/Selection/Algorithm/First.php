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
 * First rule selection algorithm.  Selects the first rule that
 * matches the product.
 *
 * @category   TBT
 * @package    TBT_Rewards
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Model_Catalogrule_Selection_Algorithm_First extends TBT_Rewards_Model_Catalogrule_Selection_Algorithm_Zeroabstract {
	
	/**
	 * Initializes the algorithm with the data
	 * it needs to compute the result
	 *
	 * @param TBT_Rewards_Model_Customer $customer
	 * @param TBT_Rewards_Model_Catalog_Product $product
	 * @return TBT_Rewards_Model_Catalogrule_Selection_Algorithm_First
	 */
	public function init($customer, $product) {
		return parent::init ( $customer, $product );
	}
	
	public function getRule() {
		foreach ( $this->getRules () as $rule ) {
			$matched_rule = TBT_Rewards_Model_Catalogrule_Rule::wrap ( $rule );
			return $matched_rule;
		}
		return null;
	}
	
	public function hasRule() {
		$has_a_rule = $this->getRules ()->count () !== 0;
		return $has_a_rule;
	}

}