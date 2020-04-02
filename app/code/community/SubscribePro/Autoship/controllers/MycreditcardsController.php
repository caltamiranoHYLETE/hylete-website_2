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

class SubscribePro_Autoship_MycreditcardsController extends Mage_Core_Controller_Front_Action
{

    /**
     * Authenticate customer
     */
    public function preDispatch()
    {
        parent::preDispatch();
        // Require logged in customer
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
        // Check if payment vault payment method enabled
        if (Mage::getStoreConfig('payment/' . SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE . '/active') != '1') {
            // Send customer to 404 page
            $this->_forward('defaultNoRoute');
            return;
        }
    }

    /**
     * Customer Dashboard, payment profile grid
     */
    public function indexAction()
    {
        // Load layout
        $this->loadLayout();

        // Set page title
        /** @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $this->getLayout()->getBlock('head');
        $headBlock->setTitle($this->__('My Credit Cards'));

        $this->renderLayout();
    }

    /**
     * New payment profile
     */
    public function newAction()
    {
        // Get core session
        /** @var Mage_Core_Model_Session $coreSession */
        $coreSession = Mage::getSingleton('core/session');
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');
        // Get customer session
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');

        $applyToSubscriptionId = $this->getRequest()->getParam('apply_to_subscription_id');

        // Load layout
        $this->loadLayout();

        // Set page title
        /** @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $this->getLayout()->getBlock('head');
        $headBlock->setTitle($this->__('Enter New Saved Credit Card'));

        try {
            $paymentProfile = $vaultHelper->getPaymentProfileService()->createCreditCardProfile();
            $paymentProfile = $vaultHelper->initProfileWithCustomerDefault($paymentProfile, $customerSession->getCustomerId());
            // Pass fields to view for rendering
            $this->getLayout()->getBlock('payment_profile_edit')->setData('payment_profile', $paymentProfile);
            $this->getLayout()->getBlock('payment_profile_edit')->setData('apply_to_subscription_id', $applyToSubscriptionId);
        }
        catch (Exception $e) {
            $coreSession->addError($this->__('Failed to load new credit credit card page!'));
            // Send customer back to grid
            $this->_redirect('creditcards/*/');

            return;
        }

        // Render layout
        $this->renderLayout();
    }

    /**
     * Edit payment profile
     */
    public function editAction()
    {
        // Get core session
        /** @var Mage_Core_Model_Session $coreSession */
        $coreSession = Mage::getSingleton('core/session');
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');

        // Load layout
        $this->loadLayout();

        // Set page title
        /** @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $this->getLayout()->getBlock('head');
        $headBlock->setTitle($this->__('Edit Saved Credit Card'));

        try {
            // Get profile ID & load the profile
            $profileId = $this->getRequest()->getParam('id');
            $paymentProfile = $vaultHelper->getPaymentProfile($profileId);
            // Pass fields to view for rendering
            $this->getLayout()->getBlock('payment_profile_edit')->setData('payment_profile', $paymentProfile);
        }
        catch (Exception $e) {
            SubscribePro_Autoship::log('Error: ' . $e->getMessage(), Zend_Log::ERR);
            $coreSession->addError($this->__('Failed to retrieve credit card for edit!'));
            // Send customer back to grid
            $this->_redirect('subscriptions/mycreditcards/');

            return;
        }

        $this->renderLayout();
    }

    public function savenewAction()
    {
        // Get core session
        /** @var Mage_Core_Model_Session $coreSession */
        $coreSession = Mage::getSingleton('core/session');
        // Get customer session
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');
        /** @var SubscribePro_Autoship_Helper_Platform_Customer $customerHelper */
        $customerHelper = Mage::helper('autoship/platform_customer');
        /** @var SubscribePro_Autoship_Helper_Platform_Subscription $platformSubscriptionHelper */
        $platformSubscriptionHelper = Mage::helper('autoship/platform_subscription');

        try {
            // Get token
            $token = $this->getRequest()->getParam('token');
            // Get apply to subscription param
            $applyToSubscriptionId = $this->getRequest()->getParam('apply_to_subscription_id');
            // Create or update customer on platform
            $platformCustomer = $customerHelper->createOrUpdatePlatformCustomer($customerSession->getCustomer());
            // Is this is a CC being added to a subscription?
            if (strlen($applyToSubscriptionId)) {
                // Call platform to get subscription(s)
                $subscription = $platformSubscriptionHelper->getSubscription($applyToSubscriptionId);
                // Check subscription matches customer
                if ($platformCustomer->getId() != $subscription->getCustomerId()) {
                    $coreSession->addError($this->__("Credit card couldn't be applied to your subscription!"));
                }
                else {
                    // Store token as payment profile
                    $paymentProfile = $vaultHelper->createPaymentProfileFromToken($platformCustomer->getId(), $token, $customerSession->getCustomer());

                    // Update subscription
                    try {
                        $subscription->setPaymentProfileId($paymentProfile->getId());
                        $platformSubscriptionHelper->updateSubscription($subscription);

                        $coreSession->addSuccess($this->__('Credit card was saved and applied to subscription.'));
                    }
                    catch (\Exception $e) {
                        SubscribePro_Autoship::log('Error: ' . $e->getMessage(), Zend_Log::ERR);
                        $coreSession->addError($this->__("Credit card was saved, but couldn't be applied to your subscription!"));
                    }
                }

                // Send customer back to My Subscriptions
                $this->_redirect('subscriptions/mysubscriptions/');

                return;
            }
            else {
                // Store token as payment profile
                $vaultHelper->createPaymentProfileFromToken($platformCustomer->getId(), $token, $customerSession->getCustomer());

                $coreSession->addSuccess($this->__('Credit card was saved.'));
            }
        }
        catch (Exception $e) {
            SubscribePro_Autoship::log('Error: ' . $e->getMessage(), Zend_Log::ERR);
            $coreSession->addError($this->__('Failed to store credit card!'));
        }

        // Send customer back to grid
        $this->_redirect('subscriptions/mycreditcards/');
    }

    /**
     * Save a payment profile which is being edited
     */
    public function saveAction()
    {
        // Get core session
        /** @var Mage_Core_Model_Session $coreSession */
        $coreSession = Mage::getSingleton('core/session');
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');

        // Get post data
        $postData = $this->getRequest()->getPost();

        try {
            // Get profile ID & load the profile
            $profileId = $postData['payment_profile_id'];
            $paymentProfile = $vaultHelper->getPaymentProfile($profileId);

            // Update payment profile with post data
            // (Basically just expiration date)
            if (isset($postData['creditcard_month'])) {
                $paymentProfile->setCreditcardMonth($postData['creditcard_month']);
            }
            if (isset($postData['creditcard_year'])) {
                $paymentProfile->setCreditcardYear($postData['creditcard_year']);
            }

            // Save updated profile via API
            $vaultHelper->updatePaymentProfile($paymentProfile);

            $coreSession->addSuccess($this->__('Credit card was successfully updated!'));
        }
        catch (Exception $e) {
            SubscribePro_Autoship::log('Error: ' . $e->getMessage(), Zend_Log::ERR);
            $coreSession->addError($this->__('Failed to save credit card!'));
        }

        // Send customer back to grid
        $this->_redirect('subscriptions/mycreditcards/');
    }

    /**
     * Delete payment profile
     */
    public function deleteAction()
    {
        // Get core session
        /** @var Mage_Core_Model_Session $coreSession */
        $coreSession = Mage::getSingleton('core/session');
        // Get customer session
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');
        /** @var SubscribePro_Autoship_Helper_Platform_Subscription $platformSubscriptionHelper */
        $platformSubscriptionHelper = Mage::helper('autoship/platform_subscription');

        // Get id of profile to delete
        $profileId = $this->getRequest()->getParam('id');
        try {
            // Check for subscriptions
            $subscriptions = $platformSubscriptionHelper->getActiveSubscriptionsUsingPaymentProfile($customerSession->getCustomer(), $profileId);
            if (count($subscriptions)) {
                $coreSession->addError($this->__("Your credit card can't be deleted since it is used on 1 or more active subscription(s)."));
            }
            else {
                // Redact profile in vault
                $vaultHelper->deletePaymentProfile($profileId);

                $coreSession->addSuccess($this->__('Your credit card was deleted.'));
            }

        }
        catch (Exception $e) {
            $coreSession->addError($this->__('Failed to delete saved credit card!'));
        }

        // Send customer back to grid
        $this->_redirect('subscriptions/mycreditcards/');
    }

}
