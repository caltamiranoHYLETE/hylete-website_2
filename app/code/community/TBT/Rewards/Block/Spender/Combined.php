<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).

 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@sweettoothrewards.com or call 1.855.699.9322, so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2015 Sweet Tooth Inc. (http://www.sweettoothrewards.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Quick Launch Block
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Block_Spender_Combined extends Mage_Core_Block_Template {

    protected $redemptionData = null;

    protected function _construct() 
    {
        $this->setTemplate('rewards/spender/combined.phtml');
        
        // Calculate slider limits and step
        $quote = Mage::getSingleton('rewards/session')->getQuote();
        $this->setPointsStep($quote->getPointsStep());
        $this->setMinSpendablePoints($quote->getMinSpendablePoints());
        $this->setMaxSpendablePoints($quote->getMaxSpendablePoints());
    }

    /**
     * True if the customer has any applicable point redemptions or has any 
     * point redemptions applied
     * 
     * @return bool
     */
    public function hasRedemptionData() 
    {
        $redemptionData = $this->collectShoppingCartRedemptions();
        if (!isset($redemptionData['applied']) || !isset($redemptionData['applicable'])) {
            return false;
        }
        
        return (sizeof($redemptionData['applied']) > 0) || (sizeof($redemptionData['applicable']) > 0);
    }

    /**
     * Collects redemption data
     * @return array
     */
    public function collectShoppingCartRedemptions() 
    {
        if (is_null($this->redemptionData)) {
            $quote = Mage::getSingleton('rewards/session')->getQuote();
            $this->redemptionData = Mage::getSingleton('rewards/session')->collectShoppingCartRedemptions($quote);
        }
        
        return $this->redemptionData;
    }

    /**
     * Should we show the shopping cart points redemption box?
     * @return boolean
     */
    public function showCartRedeemBox() 
    {
        $storeId = Mage::getSingleton('rewards/session')->getQuote()->getStoreId();
        return Mage::helper('rewards/cart')->showCartRedeemBox($storeId);
    }

    /**
     * Do we need a logged in customer to apply redemptions?
     * @return bool
     */
    public function needsLogin() 
    {
        $canUseRedemptionsIfNotLoggedIn = Mage::getStoreConfigFlag('rewards/general/canUseRedemptionsIfNotLoggedIn');
        return !$canUseRedemptionsIfNotLoggedIn && !$this->isCustomerLoggedIn();
    }

    /**
     * Show the points slider if there are any shopping cart points rules that 
     * contain any applicable or applied points redemption rules that are of 
     * the type "discount by points spent" (dbps)
     * 
     * @return bool
     */
    public function showPointsSlider() 
    {
        if (!$this->hasRedemptionData()) {
            return false;
        }
        
        $redemptionData = $this->collectShoppingCartRedemptions();
        foreach (array_merge($redemptionData['applicable'], $redemptionData['applied']) as $entry) {
            if (isset($entry['is_dbps']) && $entry['is_dbps']) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * True if there any non discount_by_points_spent type applicable or applied rules
     * @return boolean
     */
    public function hasNonDbpsCartRules() 
    {
        if (!$this->hasRedemptionData()) {
            return false;
        }
        
        $redemptionData = $this->collectShoppingCartRedemptions();
        foreach (array_merge($redemptionData['applicable'], $redemptionData['applied']) as $entry) {
            if ($entry ['is_dbps']) {
                continue;
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * True if the customer has usable points.
     *
     * @return boolean
     */
    public function customerHasUsablePoints() 
    {
        return $this->isCustomerLoggedIn() && Mage::getSingleton('rewards/session')->getSessionCustomer()->hasUsablePoints();
    }

    /**
     * True if the customer is logged in.
     * @return bool
     */
    public function isCustomerLoggedIn() 
    {
        return Mage::getSingleton('rewards/session')->isCustomerLoggedIn();
    }

    public function getCurrentSpendingPoints() 
    {
        $quote = Mage::getSingleton('rewards/session')->getQuote();
        return min($quote->getMaxSpendablePoints(), $quote->getPointsSpending());
    }
}
