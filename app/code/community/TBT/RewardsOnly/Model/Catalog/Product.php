<?php
/**
 * Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Sweet Tooth SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      https://www.sweettoothrewards.com/terms-of-service
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
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
 * Rewards Catalog Product
 *
 * @category   TBT
 * @package    TBT_Rewards
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_RewardsOnly_Model_Catalog_Product extends TBT_Rewards_Model_Catalog_Product
{



	/**
	 * Loads in a salesrule and returns a points salesrule
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return TBT_RewardsOnly_Model_Catalog_Product
	 */
	public function wrap2(Mage_Catalog_Model_Product $product) {
       	$rewards_product = Mage::getModel('rewardsonly/catalog_product')
       			->setData($product->getData())
       			->setId($product->getId());
		return $rewards_product;
	}
	
    /**
     * Ensures that the returned result is a rule model, not a rule id
     *
     * @param TBT_Rewards_Model_Catalogrule_Rule | integer $rule	: rule id or rule model
     * @return TBT_Rewards_Model_Catalogrule_Rule
     */
    protected function ensureCatalogrule($rule) {
    	if($rule instanceof TBT_Rewards_Model_Catalogrule_Rule) {
//    		$rule_id = $rule->getId();
    	} elseif($rule instanceof Mage_CatalogRule_Model_Rule) {
    		$rule = TBT_RewardsOnly_Model_Catalogrule_Rule::wrap($rule);
    	} elseif((int)$rule > 0) {
    		$rule_id = $rule;
        	$rule = Mage::getModel('rewards/catalogrule_rule')->load($rule_id);
    	} else {
    		return null;
    	}
    	return $rule;
       	
    }
    
	/**
	 * Fetches a string value of this product
	 *
	 * @return string
	 */
	public function getSimplePointsCost($customer, $positive=true) {
		
		$product = &$this;
		
		$rule_selector = Mage::helper('rewardsonly/config')->getRedemptionSelectionAlgorithm();
		$rule_selector->init($customer, $this);
		if(!$rule_selector->hasRule()) {
			return null;
		}
		
		$rule = $rule_selector->getRule();
		$pts = $rule->getPointsForProduct($product);
		if($pts < 0 && $positive) {
			$pts = $pts * -1;
		}
		
		$points = Mage::getModel('rewards/points')->set(array(
			$rule->getPointsCurrencyId() => $pts
		));
		
		
		return $points ;
	}
        
	/**
         * Return Points Params prepared for printing
         * 
         * @param TBT_Rewards_Model_Customer $customer
         * @param boolean $positive
         * @return array
         */
	public function getPointsCostParams($customer, $positive=true)
        {
            $params = array(
                'hasRule' => false,
                'ruleId'  => null,
                'points'  => 0,
                'pointsString' => null
            );

            $product = &$this;

            $ruleSelector = Mage::helper('rewardsonly/config')
                ->getRedemptionSelectionAlgorithm();
            $ruleSelector->init($customer, $this);
            
            if (!$ruleSelector->hasRule()) {
                return $params;
            }

            $rule = $ruleSelector->getRule();
            $pts = $rule->getPointsForProduct($product);
            
            if ($pts < 0 && $positive) {
                $pts = $pts * -1;
            }

            $pointsAdjuster = Mage::getModel('rewards/points')
                ->add(
                    array(
                        $rule->getPointsCurrencyId() => $pts
                    )
                )->setFormatPoints(false);
            
            $params['hasRule'] = true;
            $params['ruleId'] = $rule->getId();
            $params['points'] = $pointsAdjuster->getPoints();
            $params['pointsString'] = $pointsAdjuster->getRendering();
            
            return $params;
	}
	
    
	/**
	 * Fetches any points redeemable options.
	 *
	 * @param TBT_Rewards_Model_Customer $customer
	 * @param TBT_Rewards_Model_Catalog_Product $target_product [ = null ]
	 * @return array()
	 */
	public function getRedeemableOptions($customer = null, $target_product = null) {
		$applicable_rules = array();
   	
		if($target_product == null) {
    		$product = &$this;
		} else {
    		$product = &$target_product;
		}
		
		$gId = ($customer == null) ? null : $customer->getGroupId();
        if (empty($gId)) {
    		$gId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
        }
        // Fetch applicable rules
        $applicable_rules = $product->getCatalogRedemptionRules($customer);
		foreach($applicable_rules as $i => &$arr) {
			$arr = (array)$arr;
			$rule_id = $arr[TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID];
			$rule = Mage::getModel('rewards/catalogrule_rule')->load($rule_id);
			if(!$rule->getId()) {
				unset($applicable_rules[$i]);
				continue;
			}
					
			// create a fake item using our product
			$points = $product->getCatalogPointsForRule($rule);
			if (!$points) {
				unset($applicable_rules[$i]);
				continue;
			}
			
			$currency_id = $arr[TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID];
			$amt = $points['amount'] * -1;
			$effect = $arr[TBT_Rewards_Model_Catalogrule_Rule::POINTS_EFFECT];
			
			$arr['caption'] = $rule->getName();
			$arr['points'] = Mage::helper('rewards/currency')->formatCurrency($amt, $currency_id);
			$arr['points_caption'] = Mage::helper('rewards')->getPointsString(array(
				$currency_id => $amt
			));
			$arr['amount'] = $amt;
			$arr['currency_id'] = $currency_id;
			
			$arr['new_price'] = Mage::helper('rewards')->priceAdjuster($product->getFinalPrice(), $effect);
			
			$arr['price_disposition'] = $rule->getDispositionOnProduct($this);
			
			
             $arr['new_price'] = Mage::helper('core')->formatCurrency($arr['new_price'], false);
			 $arr['max_uses'] = $rule->getPointsUsesPerProduct();
			
			if($customer) {
				$arr['can_use_rule'] = true;
			} else {
				$arr['can_use_rule'] = Mage::helper('rewards/config')->canUseRedemptionsIfNotLoggedIn();
			}
			
		}
			   			   				
    	
    	return $applicable_rules;
	}
    
    
    /**
     * Fetches the rewards session model
     * alias to _getRewardsSess()
     *
     * @return TBT_Rewards_Model_Session
     */
    protected function _getRS() {
    	return $this->_getRewardsSess();
    }
    
    /**
     * gets the final product price in the base currency after the rule has
     * acted upon the product.
     *
     * @param TBT_Rewards_Model_Catalogrule_Rule $rule
     * @param integer $num_uses
     */
    public function getFinalPriceAfterRedemption($rule, $num_uses=1) {
    	$rule = $this->ensureCatalogrule($rule);
    	$final_price = $this->getFinalPrice();
    	$fx = $rule->getEffect();

    	// Amplify the effect if uses is > 1
	    if($num_uses > 1) {
			$fx = Mage::helper('rewards')->amplifyEffect($fx, $final_price, $num_uses);
		}
		
		// Adjust the price
    	$adjusted_price = Mage::helper('rewards')->priceAdjuster($final_price, $fx);
    	return $adjusted_price;
    }
    
    /**
     * Returns true if the price after the redemption provided is zero dollars 
     *
     * @param unknown_type $rule
     * @param unknown_type $num_uses
     * @return unknown
     */
    public function isPricelessAfterRedemption($rule, $num_uses=1) {
    	$price_after_r = $this->getFinalPriceAfterRedemption($rule, $num_uses);
    	if($price_after_r <= 0) {
    		return true;
    	} else {
    		return false;
    	}
    }

    /**
     * Points Only Rule Validator
     * @return boolean
     */
    public function hasPointsOnlyRule()
    {
		$rule_selector = Mage::helper('rewardsonly/config')->getRedemptionSelectionAlgorithm();
		$rule_selector->init($this->_getRS()->getSessionCustomer(), $this);
		return $rule_selector->hasRule();
    }
}
