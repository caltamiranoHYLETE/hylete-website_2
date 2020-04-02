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

use \SubscribePro\Service\Subscription\SubscriptionInterface;

class SubscribePro_Autoship_Helper_Platform_Subscription extends SubscribePro_Autoship_Helper_Platform_Abstract
{

    /**
     * @return SubscriptionInterface
     */
    public function initSubscription()
    {
        $subscription = $this->getSubscriptionService()->createSubscription();

        return $subscription;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return SubscriptionInterface
     */
    public function createSubscription(\SubscribePro\Service\Subscription\SubscriptionInterface $subscription)
    {
        $subscription = $this->getSubscriptionService()->saveSubscription($subscription);

        return $subscription;
    }

    /**
     * @param $subscriptionId
     * @return SubscriptionInterface
     */
    public function getSubscription($subscriptionId)
    {
        return $this->getSubscriptionService()->loadSubscription($subscriptionId);
    }

    /**
     * @param SubscriptionInterface $subscription
     */
    public function updateSubscription(\SubscribePro\Service\Subscription\SubscriptionInterface $subscription)
    {
        $shippingAddress = $subscription->getShippingAddress();
        if ($shippingAddress instanceof \SubscribePro\Service\Address\AddressInterface && !strlen($shippingAddress->getId())) {
            if (!strlen($shippingAddress->getFirstName()) && !strlen($shippingAddress->getLastName())) {
                $subscription->setShippingAddress(null);
            }
        }
        $this->getSubscriptionService()->saveSubscription($subscription);
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param $paymentProfileId
     * @return array
     */
    public function getActiveSubscriptionsUsingPaymentProfile(Mage_Customer_Model_Customer $customer, $paymentProfileId)
    {
        // Fetch all subs for customer
        $subscriptions = $this->getSubscriptions($customer);
        // Filter to include only active ones using this pay profile
        $filteredSubscriptions = array_filter(
            $subscriptions,
            function(SubscriptionInterface $subscription) use ($paymentProfileId) {
                return $subscription->getStatus() == SubscriptionInterface::STATUS_ACTIVE
                    &&
                $subscription->getPaymentProfileId() == $paymentProfileId;
            });

        return $filteredSubscriptions;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return \SubscribePro\Service\Subscription\SubscriptionInterface[]
     */
    public function getSubscriptions(Mage_Customer_Model_Customer $customer)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Customer $platformCustomerHelper */
        $platformCustomerHelper = Mage::helper('autoship/platform_customer');
        $spCustomerId = $platformCustomerHelper->fetchSubscribeProCustomerId($customer);
        if (strlen($spCustomerId)) {
            $subscriptions = $this->getSubscriptionService()->loadSubscriptions($spCustomerId);
        }
        else {
            $subscriptions = array();
        }

        return $subscriptions;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param SubscriptionInterface $subscription
     * @return array
     */
    public function getMySubscriptionsSubscriptionData(Mage_Customer_Model_Customer $customer, \SubscribePro\Service\Subscription\SubscriptionInterface $subscription)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Customer $platformCustomerHelper */
        $platformCustomerHelper = Mage::helper('autoship/platform_customer');
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $platformVaultHelper */
        $platformVaultHelper = Mage::helper('autoship/platform_vault');

        // SP Customer ID
        $spCustomerId = $subscription->getCustomerId();

        // Addresses
        $customerAddresses = $platformCustomerHelper
            ->getMergedSubscribeProAndMagentoCustomerAddresses(
                $customer,
                $spCustomerId
            );

        // Pay profiles
        $paymentProfiles = $platformVaultHelper->getPaymentProfilesForCustomerById($spCustomerId);

        // Mage Product
        $product = $this->getMagentoProduct($subscription);

        // SP Product
        $spProduct = $this->getSubscribeProProduct($subscription);

        return array(
            'customer' => $customer,
            'customer_payment_profiles' => $paymentProfiles,
            'customer_addresses' => $customerAddresses,
            'subscription' => $subscription,
            'magento_product' => $product,
            'subscribe_pro_product' => $spProduct,
        );
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return array
     */
    public function getMySubscriptionsData(Mage_Customer_Model_Customer $customer)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Customer $platformCustomerHelper */
        $platformCustomerHelper = Mage::helper('autoship/platform_customer');
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $platformVaultHelper */
        $platformVaultHelper = Mage::helper('autoship/platform_vault');

        // Customer
        $spCustomerId = $platformCustomerHelper->fetchSubscribeProCustomerId($customer);
        if (!strlen($spCustomerId)) {
            return array(
                'customer' => $customer,
                'customer_payment_profiles' => array(),
                'customer_addresses' => array(),
                'all_subscriptions' => array(),
                'subscribe_pro_products' => array(),
                'magento_products' => array(),
            );
        }

        // Payment profiles
        $paymentProfiles = $platformVaultHelper->getPaymentProfilesForCustomerById($spCustomerId);

        // Address book
        $customerAddresses = $platformCustomerHelper
            ->getMergedSubscribeProAndMagentoCustomerAddresses(
                $customer,
                $spCustomerId
            );

        // Get all subs
        $allSubscriptions = $this->getSubscriptions($customer);

        // Get products
        $magentoProducts = array();
        $subscribeProProducts = array();
        $subscriptionsWithValidProducts = array();
        /** @var \SubscribePro\Service\Subscription\SubscriptionInterface $subscription */
        foreach ($allSubscriptions as $subscription) {
            // Get Magento product
            $product = $this->getMagentoProduct($subscription);
            if ($product && strlen($product->getId())) {
                // Save mage product
                $magentoProducts[$subscription->getProductSku()] = $product;
                // Get SP product
                $spProduct = $this->getSubscribeProProduct($subscription);
                if ($spProduct instanceof \SubscribePro\Service\Product\ProductInterface && strlen($spProduct->getId())) {
                    // Save SP product
                    $subscribeProProducts[$subscription->getProductSku()] = $spProduct;
                    // Mage & SP product valid
                    $subscriptionsWithValidProducts[] = $subscription;
                }
                else {
                    SubscribePro_Autoship::log('Failed to locate Subscribe Pro product for SKU: ' . $subscription->getProductSku(), Zend_Log::ERR);
                }
            }
            else {
                SubscribePro_Autoship::log('Failed to locate Magento product for SKU: ' . $subscription->getProductSku(), Zend_Log::ERR);
            }
        }
        $allSubscriptions = $subscriptionsWithValidProducts;

        // Filter and sort subscriptions
        $allSubscriptions = array_filter($allSubscriptions, 'SubscribePro_Autoship_Helper_Platform_Subscription::matchesShouldDisplayFilter');
        usort($allSubscriptions, 'SubscribePro_Autoship_Helper_Platform_Subscription::compareSubscriptions');

        return array(
            'customer' => $customer,
            'customer_payment_profiles' => $paymentProfiles,
            'customer_addresses' => $customerAddresses,
            'all_subscriptions' => $allSubscriptions,
            'subscribe_pro_products' => $subscribeProProducts,
            'magento_products' => $magentoProducts,
        );
    }

    /**
     * @param \SubscribePro\Service\Subscription\SubscriptionInterface $subscription
     * @return Mage_Catalog_Model_Product
     */
    protected function getMagentoProduct(\SubscribePro\Service\Subscription\SubscriptionInterface $subscription)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');
        $product->load($product->getIdBySku($subscription->getProductSku()));

        return $product;
    }

    /**
     * @param \SubscribePro\Service\Subscription\SubscriptionInterface $subscription
     * @return bool|\SubscribePro\Service\Product\ProductInterface
     */
    protected function getSubscribeProProduct(\SubscribePro\Service\Subscription\SubscriptionInterface $subscription)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Product $platformProductHelper */
        $platformProductHelper = Mage::helper('autoship/platform_product');

        return $platformProductHelper->getPlatformProductBySku($subscription->getProductSku());
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return bool
     */
    protected static function matchesShouldDisplayFilter(\SubscribePro\Service\Subscription\SubscriptionInterface $subscription)
    {
        // Ignore cancelled subscriptions
        if ($subscription->getStatus() == 'Cancelled') {
            return false;
        }
        // Ignore expired subscriptions
        if ($subscription->getStatus() == 'Expired') {
            return false;
        }

        return true;
    }

    /**
     * @param SubscriptionInterface $a
     * @param SubscriptionInterface $b
     * @return int
     */
    protected static function compareSubscriptions(\SubscribePro\Service\Subscription\SubscriptionInterface $a, \SubscribePro\Service\Subscription\SubscriptionInterface $b)
    {
        // Compare Status - Failed or Retry always comes first
        if (($a->getStatus() == 'Failed' || $a->getStatus() == 'Retry') && $b->getStatus() != 'Failed' && $b->getStatus() != 'Retry') {
            return -1;
        }
        else if (($b->getStatus() == 'Failed' || $b->getStatus() == 'Retry') && $a->getStatus() != 'Failed' && $a->getStatus() != 'Retry') {
            return 1;
        }

        // Compare Status - Paused always comes last
        if ($a->getStatus() == 'Paused' && $b->getStatus() != 'Paused') {
            return 1;
        }
        else if ($b->getStatus() == 'Paused' && $a->getStatus() != 'Paused') {
            return -1;
        }

        // Compare by next order date reversed
        $dateResult = (0 - strcmp($a->getNextOrderDate(), $b->getNextOrderDate()));
        if($dateResult != 0) {
            return $dateResult;
        }

        // Compare by shipping address
        $shippingAddressResult = strcmp($a->getShippingAddressId(), $b->getShippingAddressId());
        if($shippingAddressResult != 0) {
            return $shippingAddressResult;
        }

        // Otherwise, consider them to match
        return 0;
    }

}
