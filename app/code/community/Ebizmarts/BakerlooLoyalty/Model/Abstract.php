<?php

/**
 * Loyalty abstraction providing support for any integration.
 *
 * @package Ebizmarts_BakerlooLoyalty
 */
abstract class Ebizmarts_BakerlooLoyalty_Model_Abstract extends Varien_Object {

    /**
     * @var Stores reward instance.
     */
    protected $_reward;

    /**
     * @var bool Stores if the integration can be used.
     */
    protected $_canUse = false;

    /**
     * Check if the integration can be used.
     *
     * @return bool
     */
    public function canUse() {
        return $this->_canUse;
    }

    /**
     * Check if the integration is enabled in config.
     */
    public function isEnabled() {
        $config = $this->getLoyaltyConfig();

        return ($config != '');
    }

    public function getWebsiteBaseCurrencyCode() {
        return Mage::app()->getWebsite($this->getWebsiteId())->getBaseCurrencyCode();
    }

    public function getCustomerId() {
        $customerId = $this->getData('customer_id');

        if(!$customerId && $this->getCustomer())
            $customerId = $this->getCustomer()->getId();

        return $customerId;
    }

    abstract public function getPointsBalance();

    abstract public function getMinumumToRedeem();

    abstract public function getCurrencyAmount();

    abstract public function init();

    /**
     * Transfer points to a given customers.
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param int $points
     * @return mixed
     */
    abstract public function rewardCustomer($customer, $points);

    /**
     * Return redeem options for product.
     *
     * @param $customer
     * @param $product
     * @return mixed
     */
    public function productRedeemOptions($customer, $product) {
        Mage::throwException('Not implemented.');
    }

    /**
     * Return redeem options for cart.
     *
     * @param $quote
     * @throws Mage_Core_Exception
     */
    public function cartRedeemOptions($quote) {
        Mage::throwException('Not implemented.');
    }

    /**
     * @param Mage_Sales_Model_Quote $cart
     * @return array
     */
    abstract public function getYouWillEarnPoints(Mage_Sales_Model_Quote $cart);

    /**
     * Return config data from settings.
     *
     * @return string
     */
    public function getLoyaltyConfig() {
        return (string)Mage::helper('bakerloo_restful')->config('integrations/loyalty');
    }

}