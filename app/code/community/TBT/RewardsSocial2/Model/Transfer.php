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
 * @package    [TBT_RewardsSocial2]
 * @copyright  Copyright (c) 2014 Sweet Tooth Inc. (http://www.sweettoothrewards.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Rewardssocial Transfer Model
 *
 * @category   TBT
 * @package    TBT_RewardsSocial2
 * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_RewardsSocial2_Model_Transfer extends TBT_Rewards_Model_Transfer
{
    /**
     * Reason ID mapping
     * @var array
     */
    protected $reasonsMap = array(
        'facebook_like' => TBT_RewardsSocial2_Model_Reason_FacebookLike::REASON_TYPE_ID,
        'facebook_share' => TBT_RewardsSocial2_Model_Reason_FacebookShare::REASON_TYPE_ID,
        'twitter_follow' => TBT_RewardsSocial2_Model_Reason_TwitterFollow::REASON_TYPE_ID,
        'twitter_tweet' => TBT_RewardsSocial2_Model_Reason_TwitterTweet::REASON_TYPE_ID,
        'google_plusone' => TBT_RewardsSocial2_Model_Reason_GooglePlusOne::REASON_TYPE_ID,
        'pinterest_pin' => TBT_RewardsSocial2_Model_Reason_PinterestPin::REASON_TYPE_ID,
        'facebook_share_purchase' => TBT_RewardsSocial2_Model_Reason_PurchaseShareFacebook::REASON_TYPE_ID,
        'twitter_tweet_purchase' => TBT_RewardsSocial2_Model_Reason_PurchaseShareTwitter::REASON_TYPE_ID,
        'facebook_share_referral' => TBT_RewardsSocial2_Model_Reason_ReferralShare::REASON_TYPE_ID,
        'twitter_tweet_referral' => TBT_RewardsSocial2_Model_Reason_ReferralShare::REASON_TYPE_ID
    );
    
    /**
     * Configuration paths for initial transfer status
     * @var array
     */
    protected $initialStatusPaths = array(
        'facebook_like' => 'rewards/InitialTransferStatus/AfterFacebookLike',
        'facebook_share' => 'rewards/InitialTransferStatus/AfterFacebookProductShare',
        'twitter_follow' => 'rewards/InitialTransferStatus/AfterTwitterFollow',
        'twitter_tweet' => 'rewards/InitialTransferStatus/AfterTwitterTweet',
        'google_plusone' => 'rewards/InitialTransferStatus/AfterGooglePlusOne',
        'pinterest_pin' => 'rewards/InitialTransferStatus/AfterPinterestPin',
        'facebook_share_purchase' => 'rewards/InitialTransferStatus/afterPurchaseShareOnFacebook',
        'twitter_tweet_purchase' => 'rewards/InitialTransferStatus/afterPurchaseShareOnTwitter',
        'facebook_share_referral' => 'rewards/InitialTransferStatus/AfterReferralShare',
        'twitter_tweet_referral' => 'rewards/InitialTransferStatus/AfterReferralShare'
    );
    
    /**
     * Configuration paths for transfer comments
     * @var array
     */
    protected $transferCommentPaths = array(
        'facebook_like' => 'rewards/transferComments/facebookLike',
        'facebook_share' => 'rewards/transferComments/facebookProductShare',
        'twitter_follow' => 'rewards/transferComments/twitterFollow',
        'twitter_tweet' => 'rewards/transferComments/twitterTweet',
        'google_plusone' => 'rewards/transferComments/googlePlusOne',
        'pinterest_pin' => 'rewards/transferComments/pinterestPin',
        'facebook_share_purchase' => 'rewards/transferComments/purchaseShareOnFacebook',
        'twitter_tweet_purchase' => 'rewards/transferComments/purchaseShareOnTwitter',
        'facebook_share_referral' => 'rewards/transferComments/referralShare',
        'twitter_tweet_referral' => 'rewards/transferComments/referralShare'
    );
    
    /**
     * Create all necessary transfers for a social action
     * 
     * @param TBT_RewardsSocial2_Model_Action $socialAction
     */
    public function initiateTransfers($socialAction)
    {
        $action = $socialAction->getAction();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $rules = Mage::helper('rewardssocial2')->fetchApplicableRules($action);
        
        foreach ($rules as $rule) {
            $this->createSocialTransfer($customer, $rule, $socialAction);
        }
    }
    
    /**
     * Create all social transfers for a certain rule
     * 
     * @param Mage_Customer_Model_Customer $customer
     * @param TBT_Rewards_Model_Salesrule_Rule $rule
     * @param TBT_RewardsSocial2_Model_Action $socialAction
     * @return boolean | TBT_RewardsSocial2_Model_Transfer
     */
    public function createSocialTransfer($customer, $rule, $socialAction)
    {
        $pointsAmount = $rule->getPointsAmount();
        $currencyId = $rule->getPointsCurrencyId();
        $ruleId = $rule->getId();
        
        $store = $customer->getStore();
        $action = $socialAction->getAction();

        // Inititate transfer
        $transfer = $this->initTransfer($pointsAmount, $currencyId, $ruleId);

        if (!$transfer) {
            return false;
        }

        // Get on-hold initial status override
        if ($rule->getOnholdDuration() > 0) {
            $transfer->setEffectiveStart(date('Y-m-d H:i:s', strtotime("+{$rule->getOnholdDuration()} days")))
                ->setStatus(null, TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_TIME);
        } else {
            // Get the default starting status
            $initialStatus = $this->getInitialTransferStatus($action, $store);
            if (!$transfer->setStatus(null, $initialStatus)) {
                return false;
            }
        }

        $this->clearReferences();

        // Fetch transfer comments
        $initialTransferMessage = $this->getTransferComments($action, $store);
        $comments = Mage::helper('rewardssocial2')->__($initialTransferMessage);
        $actionId = $socialAction->getId();
        
        // Set Additional Data
        $this->setReferenceType(TBT_RewardsSocial2_Model_Transfer_Reference::REFERENCE_TYPE_ID)
            ->setReferenceId($actionId)
            ->setReasonId($this->reasonsMap[$action])
            ->setActionId($actionId)
            ->setComments($comments)
            ->setCustomerId($customer->getId())
            ->save();

        return $this;
    }
    
    /**
     * Fetch the initial status for a social action
     * 
     * @param string $action
     * @param int $store
     * @return string|boolean
     */
    public function getInitialTransferStatus($action, $store = null)
    {
        if (isset($this->initialStatusPaths[$action])) {
            return Mage::getStoreConfig($this->initialStatusPaths[$action], $store);
        }
        
        return false;
    }
    
    /**
     * Fetch transfer comments for a social action
     * 
     * @param string $action
     * @param int $store
     * @return string|boolean
     */
    public function getTransferComments($action, $store = null)
    {
        if (isset($this->transferCommentPaths[$action])) {
            return Mage::getStoreConfig($this->transferCommentPaths[$action], $store);
        }
        
        return false;
    }
}
