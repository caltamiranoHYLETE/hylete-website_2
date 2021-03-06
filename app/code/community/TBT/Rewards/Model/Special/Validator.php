<?php

/**
 * Sweet Tooth
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Sweet Tooth SWEET TOOTH POINTS AND REWARDS
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL:
 * https://www.sweettoothrewards.com/terms-of-service
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
 * Special Validator
 *
 * @category   TBT
 * @package    TBT_Rewards
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Model_Special_Validator extends Varien_Object
{


    /**
     * Returns all rules that apply to a sendign a product to a friend
     *
     * @return array(TBT_rewards_Model_Special)
     */
    public function getApplicableRulesOnSendfriend()
    {
        return $this->getApplicableRules ( TBT_Rewards_Model_Special_Action::ACTION_SENDFRIEND );
    }

    /**
     * Returns all rules that apply to a given Poll
     *
     * @return array(TBT_rewards_Model_Special)
     * @deprecated from version 1.7.6.3+
     */
    public function getApplicableRulesOnPoll()
    {
        return $this->getApplicableRules ( TBT_Rewards_Model_Special_Action::ACTION_POLL );
    }



    /**
     * Returns all rules that apply to a given Rating
     *
     * @return array(TBT_rewards_Model_Special)
     */
    public function getApplicableRulesOnRating()
    {
        return $this->getApplicableRules ( TBT_Rewards_Model_Special_Action::ACTION_RATING );
    }

    /**
     * Returns all rules that apply to a customer signing up
     *
     * @return array(TBT_rewards_Model_Special)
     */
    public function getApplicableRulesOnSignup()
    {
        return $this->getApplicableRules ( TBT_Rewards_Model_Special_Action::ACTION_SIGN_UP );
    }

    //@nelkaake 20/01/2010 5:02:40 AM : made this method public.
    public function getApplicableRules($action = null, $or_action = null)
    {
        $resultCollection = array ();
        $ruleCollection = Mage::getModel ( 'rewards/special' )->getCollection ();
        foreach ( $ruleCollection as $rule ) {
            if ($this->isRuleValid ( $rule, $action )) {
                $resultCollection [] = $rule;
            } else if ($or_action != null) {
                if ($this->isRuleValid ( $rule, $or_action )) {
                    $resultCollection [] = $rule;
                }
            }
        }

        return $resultCollection;
    }

    /**
     * Runs through each rule validator
     *
     * @param TBT_Rewards_Model_Special $rule
     * @param unknown_type $actionType
     * @return boolean true if rule valid
     */
    protected function isRuleValidCheck($rule, $actionType)
    {
        //check to see if its active
        if (! $rule->getIsActive ()) {
            return false;
        }

        //check if current website_id is valid, but only if current store is not Admin
        if (! $rule->isApplicableToWebsite ( Mage::app ()->getStore ()->getWebsiteId () ) &&
            ! Mage::app()->getStore()->isAdmin()) {
            return false;
        }

        // Add date filters
        $skipDates = Mage::getSingleton('rewards/session')->getSkipDates();
        if (!$skipDates) {
            //check its after the start date
            $localDate = Mage::getModel ( 'core/date' )->gmtDate ();
            if (strtotime ( $rule->getFromDate () ) >= strtotime ( $localDate )) {
                return false;
            }

            //check ending date if not empty
            if ($rule->getToDate () != "") {
                //if it isn't make sure its before the ending date
                if (strtotime ( $rule->getToDate () ) + 86399 <= strtotime ( $localDate )) {
                    return false;
                }
            }
        }

        //check customer is within the allowed group for the rule
        $customer = $this->_getRS ()->getSessionCustomer ();
        $customer_group_ids = explode ( ",", $rule->getCustomerGroupIds () );
        if (! $this->isInGroup ( $customer, $customer_group_ids )) {
            return false;
        }

        //Unhashes the coditions and check rule triggers on customer performing the correct action
        $rule_conditions = $this->_getRH ()->unhashIt ( $rule->getConditionsSerialized () );
        if (is_array ( $rule_conditions )) {
            if (! in_array ( $actionType, $rule_conditions )) {
                return false;
            }
        } else {
            if ($rule_conditions != $actionType) {
                return false;
            }
        }

        return true;
    }

    /**
     * True if the rule is valid
     *
     * @param TBT_Rewards_Model_Special $rule
     * @param unknown_type $actionType
     * @return boolean
     */
    protected function isRuleValid($rule, $actionType)
    {
        Varien_Profiler::start ( "TBT_Rewards:: Customer Behavior Rule Validate" );
        $flag = $this->isRuleValidCheck ( $rule, $actionType );
        Varien_Profiler::stop ( "TBT_Rewards:: Customer Behavior Rule Validate" );

        return $flag;
    }

    /**
     * Returns true if customerId is within the customer groups listed
     * @param TBT_Rewards_Model_Customer $customer  : current customer id
     * @param array $groupIds                       : customer group ids array
     * @param bool checkAdmin
     *
     * @return boolean
     */
    protected function isInGroup($customer, array $groupIds, $checkAdmin = true)
    {
        $group_id = null;
        
        if ($checkAdmin && Mage::app()->getStore()->isAdmin()) {
            $group_id = Mage::getSingleton('rewards/session')->getRequestGroupId();

            if (!$group_id) {
                $group_id = $customer->getGroupId();
            }
        } else {
            $group_id = $customer->getGroupId();
        }
        
        return array_search($group_id, $groupIds) !== false;
    }

    /**
     * Fetches the rewards session singleton
     *
     * @return TBT_Rewards_Model_Session
     */
    protected function _getRS()
    {
        return Mage::getSingleton ( 'rewards/session' );
    }

    /**
     * Fetches the rewards helper singleton
     *
     * @return TBT_Rewards_Helper_Data
     */
    protected function _getRH()
    {
        return Mage::helper ( 'rewards' );
    }


    /**
     * Returns all rules that apply to a given review
     * @deprecated
     * @return array(TBT_rewards_Model_Special)
     */
    public function getApplicableRulesOnReview()
    {
        return $this->getApplicableRules ( TBT_Rewards_Model_Special_Action::ACTION_WRITE_REVIEW, TBT_Rewards_Model_Special_Action::ACTION_RATING );
    }

    /**
     * Returns all rules that apply wehn a customer signs up to a newsletter
     * @deprecated
     * @return array(TBT_rewards_Model_Special)
     */
    public function getApplicableRulesOnNewsletter()
    {
        return $this->getApplicableRules ( TBT_Rewards_Model_Special_Action::ACTION_NEWSLETTER );
    }

    /**
     * Returns all rules that apply to a given Tag
     * @deprecated
     *
     * @return array(TBT_rewards_Model_Special)
     */
    public function getApplicableRulesOnTag()
    {
        return $this->getApplicableRules ( TBT_Rewards_Model_Special_Action::ACTION_TAG );
    }
}
