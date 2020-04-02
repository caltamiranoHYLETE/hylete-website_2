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

/**
 * Controller to handle the My Subscriptions page in the customer account dashboard section of the frontend
 */
class SubscribePro_Autoship_MysubscriptionsController extends Mage_Core_Controller_Front_Action
{

    /**
     * Authenticate customer
     */
    public function preDispatch()
    {
        parent::preDispatch();

        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');

        // Require logged in customer
        if (!$customerSession->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
        // Check if extension enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled') != '1') {
            // Send customer to 404 page
            $this->_forward('defaultNoRoute');
            return;
        }
    }

    /**
     * Customer Dashboard - My Product Subscriptions page
     */
    public function indexAction()
    {
        // Get customer
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        $customer = $customerSession->getCustomer();

        // Load layout from XML, with extra handle specific to page mode
        // Either: 'autoship_mysubscriptions_index_native' or 'autoship_mysubscriptions_index_hosted'
        $this->loadMySubscriptionsLayout();

        // Native or Hosted mode?
        if (Mage::getStoreConfig('autoship_subscription/hosted_features/use_hosted_my_subscriptions') == 0) {
            //
            // Native mode
            //
            /** @var SubscribePro_Autoship_Helper_Platform_Subscription $platformSubscriptionHelper */
            $platformSubscriptionHelper = Mage::helper('autoship/platform_subscription');

            // Load and set data in native mode
            // Get subscriptions data
            $data = $platformSubscriptionHelper->getMySubscriptionsData($customer);

            // Apply data to layout
            /** @var SubscribePro_Autoship_Block_Mysubscriptions $mySubscriptionsBlock */
            $mySubscriptionsBlock = $this->getLayout()->getBlock('mysubscriptions');
            $mySubscriptionsBlock->setMySubscriptionsData($data);
        }
        else {
            //
            // Hosted mode
            //
        }

        // Render the layout
        $this->renderLayout();
    }

    /**
     * Action to update interval, qty or next order date, payment, shipping, etc for a single subscription
     */
    public function changeAction()
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Subscription $platformSubscriptionHelper */
        $platformSubscriptionHelper = Mage::helper('autoship/platform_subscription');
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $platformVaultHelper */
        $platformVaultHelper = Mage::helper('autoship/platform_vault');
        // Get customer
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        $customer = $customerSession->getCustomer();
        try {
            // Get POST data
            $postData = $this->validateChangePostData();

            // Call platform to get subscription(s)
            $subscription = $platformSubscriptionHelper->getSubscription($postData['subscription_id']);

            // Are we modifying CC exp date or subscription params?
            if (isset($postData['cc_exp_month']) && isset($postData['cc_exp_month'])) {
                // Update only payment profile
                $paymentProfile = $platformVaultHelper->getPaymentProfile($subscription->getPaymentProfileId());
                $paymentProfile->setCreditcardMonth($postData['cc_exp_month']);
                $paymentProfile->setCreditcardYear($postData['cc_exp_year']);
                $platformVaultHelper->updatePaymentProfile($paymentProfile);
            }
            else {
                // Update subscription
                $this->updateSubscriptionFromPost($postData, $subscription);
            }

            // Ajax Output = Re-render subscription block
            $this->reRenderSubscriptionBlock($customer, $subscription);
        }
        catch (Exception $e) {
            $this->handleAjaxException($e);
        }
    }

    public function skipAction()
    {
        $this->subscriptionActionImpl('skipSubscription');
    }

    public function cancelAction()
    {
        $this->subscriptionActionImpl('cancelSubscription');
    }

    public function pauseAction()
    {
        $this->subscriptionActionImpl('pauseSubscription');
    }

    public function restartAction()
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Subscription $platformSubscriptionHelper */
        $platformSubscriptionHelper = Mage::helper('autoship/platform_subscription');
        // Get customer
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        $customer = $customerSession->getCustomer();

        try {
            // Get subscription id from request
            $subscriptionId = $this->getRequest()->getParam('id');
            // Call platform to get subscription again
            $subscription = $platformSubscriptionHelper->getSubscription($subscriptionId);
            // Update next order date, based on subscription status
            // Paused = +2 days
            // Failed / retry = today
            if ($subscription->getStatus() == 'Paused') {
                $subscription->setNextOrderDate(date('Y-m-d', strtotime('+2 days')));
            }
            else {
                $subscription->setNextOrderDate(date('Y-m-d'));
            }
            $platformSubscriptionHelper->updateSubscription($subscription);
            // Call API to restart subscription
            $platformSubscriptionHelper->getSubscriptionService()->restartSubscription($subscriptionId);
            // Now call platform to get subscription again
            $subscription = $platformSubscriptionHelper->getSubscription($subscriptionId);
            // Ajax Output = Re-render subscription block
            $this->reRenderSubscriptionBlock($customer, $subscription);
        }
        catch (Exception $e) {
            $this->handleAjaxException($e);
        }
    }

    protected function subscriptionActionImpl($actionMethodName)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Subscription $platformSubscriptionHelper */
        $platformSubscriptionHelper = Mage::helper('autoship/platform_subscription');
        // Get customer
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        $customer = $customerSession->getCustomer();

        try {
            // Get subscription id from request
            $subscriptionId = $this->getRequest()->getParam('id');
            // Call API to perform action on subscription
            $platformSubscriptionHelper->getSubscriptionService()->$actionMethodName($subscriptionId);
            // Now call platform to get subscription again
            $subscription = $platformSubscriptionHelper->getSubscription($subscriptionId);
            // Ajax Output = Re-render subscription block
            $this->reRenderSubscriptionBlock($customer, $subscription);
        }
        catch (Exception $e) {
            $this->handleAjaxException($e);
        }
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param \SubscribePro\Service\Subscription\SubscriptionInterface $subscription
     */
    protected function reRenderSubscriptionBlock(Mage_Customer_Model_Customer $customer, \SubscribePro\Service\Subscription\SubscriptionInterface $subscription)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Subscription $platformSubscriptionHelper */
        $platformSubscriptionHelper = Mage::helper('autoship/platform_subscription');
        /** @var SubscribePro_Autoship_Block_Mysubscriptions_Subscription $subscriptionBlock */
        $subscriptionBlock = $this->getLayout()->createBlock('autoship/mysubscriptions_subscription');

        // Re-fetch subscription
        $subscription = $platformSubscriptionHelper->getSubscription($subscription->getId());
        // Return the rendered html for this new subscription state
        $html = $subscriptionBlock
            ->setSubscriptionData($platformSubscriptionHelper->getMySubscriptionsSubscriptionData($customer, $subscription))
            ->setTemplate('autoship/mysubscriptions/subscription.phtml')
            ->toHtml();
        $this->getResponse()->setBody($html);
    }

    /**
     * @param array $postData
     * @param \SubscribePro\Service\Subscription\SubscriptionInterface $subscription
     * @return \SubscribePro\Service\Subscription\SubscriptionInterface
     */
    protected function updateSubscriptionFromPost(array $postData, \SubscribePro\Service\Subscription\SubscriptionInterface $subscription)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Subscription $platformSubscriptionHelper */
        $platformSubscriptionHelper = Mage::helper('autoship/platform_subscription');
        /** @var SubscribePro_Autoship_Helper_Platform_Customer $platformCustomerHelper */
        $platformCustomerHelper = Mage::helper('autoship/platform_customer');

        // Update fields
        if (isset($postData['interval'])) {
            $subscription->setInterval($postData['interval']);
        }
        if (isset($postData['qty'])) {
            $subscription->setQty($postData['qty']);
        }
        if (isset($postData['product_sku'])) {
            $subscription->setQty($postData['product_sku']);
        }
        if (isset($postData['delivery_date'])) {
            $subscription->setNextOrderDate($postData['delivery_date']);
        }
        if (isset($postData['payment_profile_id'])) {
            $subscription->setPaymentProfileId($postData['payment_profile_id']);
        }
        if (isset($postData['shipping_address_id'])) {
            if ($postData['shipping_address_id'] == 'new' && isset($postData['new_shipping_address'])) {
                $addressData = $postData['new_shipping_address'];
                // Handle Magento region select data
                if (isset($addressData['region_id']) && strlen($addressData['region_id'])) {
                    /** @var Mage_Directory_Model_Region $region */
                    $region = Mage::getModel('directory/region')->load($addressData['region_id']);
                    $addressData['region'] = $region->getName();
                }
                // Init shipping address in SP format
                $shippingAddress = $platformCustomerHelper->initCustomerAddress($subscription->getCustomerId(), $addressData);
                $subscription->setShippingAddress($shippingAddress);
                $subscription->setShippingAddressId(null);
            }
            else {
                $subscription->setShippingAddressId($postData['shipping_address_id']);
            }
        }
        // Send changes to platform
        $platformSubscriptionHelper->updateSubscription($subscription);

        return $subscription;
    }

    /**
     * Retrieve post data for changeAction and validate it
     */
    protected function validateChangePostData()
    {
        $data = $this->getRequest()->getPost();
        if (!is_array($data)) {
            Mage::throwException('Failed to process POST data!');
        }
        // Validate POST data and return in array
        $validatedPostData = array();
        // Fields on all forms
        if (isset($data['subscription_id']) && strlen($data['subscription_id'])) {
            $validatedPostData['subscription_id'] = $data['subscription_id'];
        }

        // Fields on subscription info form
        if (isset($data['delivery_qty']) && strlen($data['delivery_qty'])) {
            if (!is_numeric($data['delivery_qty'])) {
                Mage::throwException('Please specify a numeric value for subscription quantity!');
            }
            $validatedPostData['qty'] = $data['delivery_qty'];
        }
        if (isset($data['delivery_interval']) && strlen($data['delivery_interval'])) {
            $validatedPostData['interval'] = $data['delivery_interval'];
        }
        if (isset($data['delivery_date']) && strlen($data['delivery_date'])) {
            $validatedPostData['delivery_date'] = date('Y-m-d', strtotime($data['delivery_date']));
        }

        // Payment info form
        if (isset($data['cc_exp_month']) && strlen($data['cc_exp_month'])) {
            $validatedPostData['cc_exp_month'] = $data['cc_exp_month'];
        }
        if (isset($data['cc_exp_year']) && strlen($data['cc_exp_year'])) {
            $validatedPostData['cc_exp_year'] = $data['cc_exp_year'];
        }

        // Payment form
        if (isset($data['payment_profile_id']) && strlen($data['payment_profile_id'])) {
            if (is_numeric($data['payment_profile_id'])) {
                $validatedPostData['payment_profile_id'] = $data['payment_profile_id'];
            }
        }

        // Shipping form
        if (isset($data['new_shipping_address']) && is_array($data['new_shipping_address'])) {
            $validatedPostData['new_shipping_address'] = $data['new_shipping_address'];
        }
        if (isset($data['shipping_address_id']) && strlen($data['shipping_address_id'])) {
            $validatedPostData['shipping_address_id'] = $data['shipping_address_id'];
        }

        // Return validate POST data
        return $validatedPostData;
    }

    /**
     * Method logs exception and outputs message for display to customer
     *
     * @param Exception $e
     */
    protected function handleAjaxException(Exception $e)
    {
        // Log exception
        SubscribePro_Autoship::log('Ajax Exception occurred: ' . $e->getMessage(), Zend_Log::ERR);
        SubscribePro_Autoship::logCallStack();
        SubscribePro_Autoship::logException($e);
        // Output error message formatted for display
        $this->getResponse()->setBody('<li class="error ajax">' . $this->__('An error occurred while updating your subscription!') . '</li>');
    }


    /**
     * Load layout by handles(s)
     * Add custom handled.  Either: 'autoship_mysubscriptions_index_native' or 'autoship_mysubscriptions_index_hosted'
     *
     * @param   string|null|bool $handles
     * @param   bool $generateBlocks
     * @param   bool $generateXml
     * @return  SubscribePro_Autoship_MysubscriptionsController
     */
    protected function loadMySubscriptionsLayout($handles = null, $generateBlocks = true, $generateXml = true)
    {
        // if handles were specified in arguments load them first
        if (false!==$handles && ''!==$handles) {
            $this->getLayout()->getUpdate()->addHandle($handles ? $handles : 'default');
        }

        // add default layout handles for this action
        $this->addActionLayoutHandles();

        // Now about specific handle based on config
        if (Mage::getStoreConfig('autoship_subscription/hosted_features/use_hosted_my_subscriptions') == 1) {
            $this->getLayout()->getUpdate()->addHandle('autoship_mysubscriptions_index_hosted');
        }
        else {
            $this->getLayout()->getUpdate()->addHandle('autoship_mysubscriptions_index_native');
        }

        $this->loadLayoutUpdates();

        if (!$generateXml) {
            return $this;
        }
        $this->generateLayoutXml();

        if (!$generateBlocks) {
            return $this;
        }
        $this->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        return $this;
    }

}
