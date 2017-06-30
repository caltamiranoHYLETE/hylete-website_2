<?php

class Ebizmarts_BakerlooLoyalty_Model_EnterpriseRewards extends Ebizmarts_BakerlooLoyalty_Model_Abstract {

    public function init() {
    	$reward = Mage::getModel('enterprise_reward/reward')
                ->getCollection()
                ->addFieldToFilter('customer_id', $this->getCustomerId())
                ->addWebsiteFilter($this->getWebsiteId())
                ->getFirstItem();

    	$this->_reward = $reward;
    }

	public function isEnabled() {
		$posConfig       = ($this->getLoyaltyConfig() == 'Enterprise_Reward');
        $active          = Mage::helper('enterprise_reward')->isEnabled();

        return $posConfig && $active;

	}

	public function getPointsBalance() {
		return (int)$this->_reward->getPointsBalance();
	}

	public function getMinumumToRedeem() {
		return (int)Mage::helper('enterprise_reward')->getGeneralConfig('min_points_balance');
	}

	public function getCurrencyAmount() {
		return $this->_reward->getCurrencyAmount();
	}

    public function getYouWillEarnPoints(Mage_Sales_Model_Quote $cart) {
        return array();
    }

	public function rewardCustomer($customer, $points) {
		return false;
	}

}