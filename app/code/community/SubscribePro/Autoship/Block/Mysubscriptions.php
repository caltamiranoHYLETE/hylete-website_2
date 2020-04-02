<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

class SubscribePro_Autoship_Block_Mysubscriptions extends Mage_Core_Block_Template
{

    private $customer = null;
    private $customerPaymentProfiles = array();
    private $customerAddresses = array();
    private $allSubscriptions = array();
    private $magentoProducts = array();
    private $subscribeProProducts = array();


    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    public function setMySubscriptionsData(array $data)
    {
        $this->customer = $data['customer'];
        $this->customerPaymentProfiles = $data['customer_payment_profiles'];
        $this->customerAddresses = $data['customer_addresses'];
        $this->allSubscriptions = $data['all_subscriptions'];
        $this->magentoProducts = $data['magento_products'];
        $this->subscribeProProducts = $data['subscribe_pro_products'];
    }

    public function getAllSubscriptions()
    {
        return $this->allSubscriptions;
    }

    public function getSubscriptionData(\SubscribePro\Service\Subscription\SubscriptionInterface $subscription)
    {
        return array(
            'customer' => $this->customer,
            'customer_payment_profiles' => $this->customerPaymentProfiles,
            'customer_addresses' => $this->customerAddresses,
            'subscription' => $subscription,
            'magento_product' => $this->magentoProducts[$subscription->getProductSku()],
            'subscribe_pro_product' => $this->subscribeProProducts[$subscription->getProductSku()],
        );
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    public function getCustomerPaymentProfiles()
    {
        return $this->customerPaymentProfiles;
    }

    public function getCustomerAddresses()
    {
        return $this->customerAddresses;
    }

}
