<?php

/**
 * WDCA - Sweet Tooth
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL:
 * https://www.sweettoothrewards.com/terms-of-service
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

class TBT_Rewards_Model_Review_Transfer extends TBT_Rewards_Model_Transfer 
{
    public function __construct() 
    {
        parent::__construct ();
    }

    public function setReviewId($id) 
    {
        $this->clearReferences ();
        $this->setReferenceType ( TBT_Rewards_Model_Transfer_Reference::REFERENCE_REVIEW );
        $this->setReferenceId ( $id );
        $this->_data ['review_id'] = $id;

        return $this;
    }

    public function isReview() 
    {
        return ($this->getReferenceType () == TBT_Rewards_Model_Transfer_Reference::REFERENCE_REVIEW) || isset ( $this->_data ['review_id'] );
    }

    public function getTransfersAssociatedWithReview($review_id) 
    {
        return $this->getCollection ()->addFilter ( 'reference_type', TBT_Rewards_Model_Transfer_Reference::REFERENCE_REVIEW )->addFilter ( 'reference_id', $review_id );
    }

    /**
     * Fetches the transfer helper
     *
     * @return TBT_Rewards_Helper_Transfer
     */
    protected function _getTransferHelper() 
    {
        return Mage::helper ( 'rewards/transfer' );
    }

    /**
     * Fetches the rewards special validator singleton
     *
     * @return TBT_Rewards_Model_Special_Validator
     */
    protected function _getSpecialValidator() 
    {
        return Mage::getSingleton ( 'rewards/special_validator' );
    }

    /**
     * Fetches the rewards special validator singleton
     *
     * @return TBT_Rewards_Model_Special_Validator
     */
    protected function _getReviewValidator() 
    {
        return Mage::getSingleton ( 'rewards/review_validator' );
    }

    /**
     * Do the points transfer for the review
     *
     * @param  Mage_Review_Model_Review $review
     * @param  int $rule       : Special Rule
     * @return boolean            : whether or not the point-transfer succeeded
     */
    public function transferReviewPoints($review, $rule) 
    {
        $num_points = $rule->getPointsAmount();
        $currency_id = $rule->getPointsCurrencyId();
        $review_id = $review->getId();
        $rule_id = $rule->getId();
        $transfer = $this->initTransfer($num_points, $currency_id, $rule_id);
        
        $customer_id = $review->getCustomerId();
        
        if ( ! $transfer ) {
            return false;
        }
        
        // get the default starting status - usually Pending
        if ( ! $transfer->setStatus(null, Mage::helper('rewards/config')->getInitialTransferStatusAfterReview()) ) {
            // we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
            return false;
        }
        
        // Check for daily and weekly review limits
        $skipDates = Mage::getSingleton('rewards/session')->getSkipDates();
        if (!$skipDates && !$this->isWithinLimits($customer_id)) {
            return false;
        }
        
        $transfer->setReviewId($review_id)
            ->setComments(Mage::getStoreConfig('rewards/transferComments/reviewOrRatingEarned'))
            ->setCustomerId($customer_id)
            ->save();
        
        return true;
    }

    /**
     * Check if the daily & weekly review limits for point earnings were reached 
     * 
     * @param  int $customerId
     * @return bool 
     */
    public function isWithinLimits($customerId) 
    {
        $isWithinLimits = true;
        
        if (!$this->isWithinDailyLimit($customerId)) {
            $isWithinLimits = false;
            Mage::getSingleton('core/session')->addError(
                Mage::helper('rewards')->__("You've reached the review rewards limit for today. You will not receive any points for this review.")
            );
        } elseif (!$this->isWithinWeeklyLimit($customerId)) {
            $isWithinLimits = false;
            Mage::getSingleton('core/session')->addError(
                Mage::helper('rewards')->__("You've reached the review rewards limit for this week. You will not receive any points for this review.")
            );
        }
        
        return $isWithinLimits;
    }
    
    /**
     * Check if the daily review limits for point earnings were reached 
     * 
     * @param  int $customerId
     * @return bool 
     */
    public function isWithinDailyLimit($customerId)
    {
        // Get system config settings
        $dailyReviewLimit = Mage::getStoreConfig('rewards/reviews_and_tags/daily_review_limit');
        
         // Calculate seconds in a day and in a week
        $secondsInADay = 60 * 60 * 24;
        $yesterday = time() - $secondsInADay;
        
        //Get review ids (pending and approved) posted by the customer since yesterday
        $reviewIds = Mage::getModel('review/review')
            ->getResourceCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId()) 
            ->addCustomerFilter($customerId)
            ->addFieldToFilter('status_id', array(Mage_Review_Model_Review::STATUS_APPROVED, Mage_Review_Model_Review::STATUS_PENDING))
            ->addFieldToFilter('created_at', array('gteq' => $yesterday))
            ->addRateVotes()
            ->getAllIds();
        
        // Count Reviews that have a transfer associated to them
        $numberOfReviewsInThePastDay = Mage::getModel('rewards/transfer')
            ->getCollection()
            ->addAllReferences()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('reference_type', TBT_Rewards_Model_Review_Reference::REFERENCE_TYPE_ID)
            ->addFieldToFilter('reference_id', array('in' => $reviewIds))
            ->getSize();
 
        // Count current review as well (which doesn't have a transfer yet)
        $numberOfReviewsInThePastDay++;
        
        return !($dailyReviewLimit > 0 && $numberOfReviewsInThePastDay > $dailyReviewLimit);
    }
    
    /**
     * Check if the weekly review limits for point earnings were reached 
     * 
     * @param  int $customerId
     * @return bool 
     */
    public function isWithinWeeklyLimit($customerId)
    {
         $weeklyReviewLimit = Mage::getStoreConfig('rewards/reviews_and_tags/weekly_review_limit');
        
        // Calculate seconds in a day and in a week
        $secondsInADay = 60 * 60 * 24;
        $oneWeekAgo = time() - 7 * $secondsInADay;
        
        //Get review ids (pending and approved) posted by the customer between now and a week ago
        $reviewIds = Mage::getModel('review/review')
            ->getResourceCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId()) 
            ->addCustomerFilter($customerId)
            ->addFieldToFilter('status_id', array(Mage_Review_Model_Review::STATUS_APPROVED, Mage_Review_Model_Review::STATUS_PENDING))
            ->addFieldToFilter('created_at', array('gteq' => $oneWeekAgo))
            ->addRateVotes()
            ->getAllIds();
        
        // Count Reviews that have a transfer associated to them
        $numberOfReviewsInThePastWeek = Mage::getModel('rewards/transfer')
            ->getCollection()
            ->addAllReferences()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('reference_type', TBT_Rewards_Model_Review_Reference::REFERENCE_TYPE_ID)
            ->addFieldToFilter('reference_id', array('in' => $reviewIds))
            ->getSize();
        
        // Count current review as well (which doesn't have a transfer yet)
        $numberOfReviewsInThePastWeek++;
        
        return !($weeklyReviewLimit > 0 && $numberOfReviewsInThePastWeek > $weeklyReviewLimit);
    }
}
