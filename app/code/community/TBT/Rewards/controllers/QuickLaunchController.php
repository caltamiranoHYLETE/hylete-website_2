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
 * Quick Launch Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */

class TBT_Rewards_QuickLaunchController extends Mage_Adminhtml_Controller_Action 
{
    protected function _isAllowed()
    {
        return true;
    }
    
    public function indexAction() 
    {
        if (Mage::helper('rewards')->storeHasRewardRules()) {
            $this->_redirect('adminhtml/rewardsDashboard/index');
        }
        
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function launchAction()
    {
        $data = $this->getRequest()->getParams();
        $helper = Mage::helper('rewards');

        $websites = array();
        foreach (Mage::app()->getWebsites() as $website) {
            $websites[] = $website->getId();
        }
        
        $customerGroups = Mage::getModel('customer/group')->getCollection()->getAllIds();
        
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $currencySymbol = Mage::app()->getLocale()->currency($currencyCode)->getSymbol();
        
        // Spending Rule
        if ($data['spending-points']) {
            $spendingRuleData = array(
                'name' => $helper->__("Quick-Launch Rule: Spend %s points for each %s1 discount", $data['spending-points'], $currencySymbol),
                'is_active' => 1,
                'website_ids' => $websites,
                'customer_group_ids' => $customerGroups,
                'points_action' => 'discount_by_points_spent',
                'points_currency_id' => 1,
                'points_amount' => $data['spending-points'],
                'points_discount_action' => 'cart_fixed',
                'points_discount_amount' => 1,
                'stop_rules_processing' => 0
            );
            
            $model = Mage::getModel('rewards/salesrule_rule');
            $model->loadPost($spendingRuleData);
            Mage::getSingleton('adminhtml/session')->setPageData($model->getData());
            
            $model->save();
            Mage::getSingleton('adminhtml/session')->setPageData(false);
        }
        
        // Earning Rule
        if ($data['earning-points']) {
            $earningRuleData = array(
                'name' => $helper->__("Quick-Launch Rule: Earn %s point for every %s1 spent", $data['earning-points'], $currencySymbol),
                'is_active' => 1,
                'website_ids' => $websites,
                'customer_group_ids' => $customerGroups,
                'points_action' => 'give_by_amount_spent',
                'points_currency_id' => 1,
                'points_amount' => $data['earning-points'],
                'points_qty_step' => 0,
                'points_max_qty' => 0,
                'stop_rules_processing' => 0
            );
            
            $model = Mage::getModel('rewards/salesrule_rule');
            $model->loadPost($earningRuleData);
            Mage::getSingleton('adminhtml/session')->setPageData($model->getData());
            
            $model->save();
            Mage::getSingleton('adminhtml/session')->setPageData(false);
        }
        
        // Sign Up Bonus
        if ($data['sign-up']) {
            $signUpData = array(
                'name' => $helper->__("Quick-Launch Rule: Earn %s points for Signing Up", $data['sign-up']),
                'is_active' => 1,
                'website_ids' => implode(',', $websites),
                'customer_group_ids' => implode(',', $customerGroups),
                'points_conditions' => 'customer_sign_up',
                'points_action' => 'grant_points',
                'simple_action' => 'by_percent',
                'points_currency_id' => 1,
                'points_amount' => $data['sign-up'],
                'is_onhold_enabled' => 0,
                'onhold_duration' => 0
            );
            
            $model = Mage::getModel('rewards/special');
            $model->loadPost($signUpData);
            Mage::getSingleton('adminhtml/session')->setPageData($model->getData());
            
            $model->save();
            Mage::getSingleton('adminhtml/session')->setPageData(false);
        }
        
        // Points Caption
        if ($data['points-caption']) {
            $currency = Mage::getModel('rewards/currency')->load(1);
            if ($currency->getId()) {
                $currency->setCaption($data['points-caption']);
                $currency->save();
            }
        }
        
        Mage::getConfig()->saveConfig('rewards/general/last_quick_launch', Mage::helper('rewards/datetime')->now(false, true));
        $this->_redirect('adminhtml/quickLaunch/success');
    }
    
    public function successAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}
