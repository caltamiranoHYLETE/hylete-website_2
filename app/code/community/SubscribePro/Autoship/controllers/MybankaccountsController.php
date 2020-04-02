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

class SubscribePro_Autoship_MybankaccountsController extends Mage_Core_Controller_Front_Action
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
        if (Mage::getStoreConfig('payment/' . SubscribePro_Autoship_Model_Payment_Method_Echeck::METHOD_CODE . '/active') != '1') {
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
        $headBlock->setTitle($this->__('My Bank Accounts'));

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
        $headBlock->setTitle($this->__('Enter New Saved Bank Account'));

        try {
            $paymentProfile = $vaultHelper->getPaymentProfileService()->createBankAccountProfile();
            $paymentProfile = $vaultHelper->initProfileWithCustomerDefault($paymentProfile, $customerSession->getCustomerId());
            // Pass fields to view for rendering
            $this->getLayout()->getBlock('echeck_profile_new')->setData('payment_profile', $paymentProfile);
            $this->getLayout()->getBlock('echeck_profile_new')->setData('apply_to_subscription_id', $applyToSubscriptionId);
        }
        catch (Exception $e) {
            $coreSession->addError($this->__('Failed to load new bank account page!'));
            // Send customer back to grid
            $this->_redirect('subscriptions/mybankaccounts/');

            return;
        }

        // Render layout
        $this->renderLayout();
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
        /** @var SubscribePro_Autoship_Helper_Platform_Customer $customerHelper */
        $customerHelper = Mage::helper('autoship/platform_customer');
        /** @var SubscribePro_Autoship_Helper_Platform_Subscription $platformSubscriptionHelper */
        $platformSubscriptionHelper = Mage::helper('autoship/platform_subscription');
        // Get customer session
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');

        // Get post data
        $postData = $this->getRequest()->getPost();

        try {
            // Create or update customer on platform
            $platformCustomer = $customerHelper->createOrUpdatePlatformCustomer($customerSession->getCustomer());

            // Create profile via API
            $paymentProfile = $vaultHelper->createBankAccountPaymentProfile(
                $platformCustomer->getId(),
                $customerSession->getCustomer(),
                null,
                $postData['payment_profile']['bank_account_number'],
                $postData['payment_profile']['bank_routing_number'],
                $postData['payment_profile']['bank_account_type'],
                $postData['payment_profile']['bank_account_holder_type']
                );

            // Should we attach this new bank account to a subscription?
            $applyToSubscriptionId = isset($postData['payment_profile']['apply_to_subscription_id']) ? $postData['payment_profile']['apply_to_subscription_id'] : '';
            if (strlen($applyToSubscriptionId)) {
                // Call platform to get subscription(s)
                $subscription = $platformSubscriptionHelper->getSubscription($applyToSubscriptionId);
                if ($platformCustomer->getId() != $subscription->getCustomerId()) {
                    $coreSession->addError($this->__('Error applying new bank account for subscription.'));
                }
                else {
                    // Send changes to platform
                    $subscription->setPaymentProfileId($paymentProfile->getId());
                    $platformSubscriptionHelper->updateSubscription($subscription);

                    $coreSession->addSuccess($this->__('Bank account was saved and applied to subscription.'));

                    // Send customer back to grid
                    $this->_redirect('subscriptions/mysubscriptions/');

                    return;
                }

            }

            $coreSession->addSuccess($this->__('Bank account was saved.'));
        }
        catch (SubscribePro\Exception\HttpException $e) {
            $errorBody = json_decode((string)$e->getResponse()->getBody(), true);
            if (isset($errorBody['errors']))
            foreach ($errorBody['errors'] as $error) {
                if (isset($error['message'])) {
                    $coreSession->addError($this->__($error['message']));
                }
            }
            $coreSession->addError($this->__('Failed to save bank account!'));
        }
        catch (Exception $e) {
            SubscribePro_Autoship::log('Error: ' . $e->getMessage(), Zend_Log::ERR);
            $coreSession->addError($this->__('Failed to save bank account!'));
        }

        // Send customer back to grid
        $this->_redirect('subscriptions/mybankaccounts/');
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
                $coreSession->addError($this->__("Your bank account can't be deleted since it is used on 1 or more active subscription(s)."));
            }
            else {
                // Redact profile in vault
                $vaultHelper->deletePaymentProfile($profileId);

                $coreSession->addSuccess($this->__('Your bank account was deleted.'));
            }
        }
        catch (Exception $e) {
            $coreSession->addError($this->__('Failed to delete saved bank account!'));
        }

        // Send customer back to grid
        $this->_redirect('subscriptions/mybankaccounts/');
    }

}
