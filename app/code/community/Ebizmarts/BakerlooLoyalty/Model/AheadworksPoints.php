<?php

class Ebizmarts_BakerlooLoyalty_Model_AheadworksPoints extends Ebizmarts_BakerlooLoyalty_Model_Abstract {

    public function init() {
        $this->_reward = Mage::getModel('points/summary')
                        ->loadByCustomerID($this->getCustomerId());
    }

	public function isEnabled() {
		$posConfig       = ($this->getLoyaltyConfig() == 'AW_Points');
        $active          = Mage::helper('points/config')->isPointsEnabled();

        return $posConfig && $active;
	}

    public function getPointsBalance() {
        return (int)$this->_reward->getPoints();
    }

    public function getMinumumToRedeem() {
        return Mage::helper('points/config')->getMinimumPointsToRedeem($this->getStoreId());
    }

    public function getCurrencyAmount() {
        return "";
    }

    public function getYouWillEarnPoints(Mage_Sales_Model_Quote $cart) {
        return array();
    }

    public function rewardCustomer($customer, $points) {
        return false;
    }
}