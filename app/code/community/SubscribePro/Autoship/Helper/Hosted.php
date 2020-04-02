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
class SubscribePro_Autoship_Helper_Hosted extends Mage_Core_Helper_Abstract
{

    /**
     * Frontend js script paths
     */
    const SPREEDLY_IFRAME_SCRIPT_URL = 'https://core.spreedly.com/iframe/iframe-v1.min.js';
    const STRIPE_JQUERY_PAYMENT_SCRIPT = 'jquery.payment.min.js';
    const SUBSCRIBE_PRO_JQUERY_CC_SCRIPT = 'subscribepro-jquery-cc-0.9.1.min.js';
    const SUBSCRIBE_PRO_WIDGETS_SCRIPT = 'subscribepro-widgets-0.9.1.min.js';

    /**
     * Session storage keys
     */
    const CREATED_ADDRESS_DETAILS_SESSION_KEY = 'sp_created_address_details';
    const UPDATED_ADDRESS_DETAILS_SESSION_KEY = 'sp_updated_address_details';
    const DELETED_ADDRESS_DETAILS_SESSION_KEY = 'sp_deleted_address_details';

    /**
     * We have to save this flag from the 'customer_address_save_before' event,
     * then use it in the 'customer_address_save_commit_after event'.
     *
     * @var bool
     */
    private $isNew = false;


    public function getHostedScriptUrl($scriptName)
    {
        return join(
            '/',
            array(
                rtrim(Mage::getStoreConfig('autoship_subscription/hosted_features/hosted_widgets_js_include'), '/'),
                $scriptName
            ));
    }

    public function getWidgetsScriptUrl()
    {
        return $this->getHostedScriptUrl(self::SUBSCRIBE_PRO_WIDGETS_SCRIPT);
    }

    public function getJqueryCcScriptUrl()
    {
        return $this->getHostedScriptUrl(self::SUBSCRIBE_PRO_JQUERY_CC_SCRIPT);
    }

    public function getJqueryPaymentScriptUrl()
    {
        return $this->getHostedScriptUrl(self::STRIPE_JQUERY_PAYMENT_SCRIPT);
    }

    public function getIframeScriptUrl()
    {
        return self::SPREEDLY_IFRAME_SCRIPT_URL;
    }

    public function onCustomerAddressSaveBefore(Mage_Customer_Model_Address $address)
    {
        // Check config to see if hosted functionality is enabled
        if (Mage::getStoreConfig('autoship_subscription/hosted_features/use_hosted_address_book') != '1') {
            return;
        }

        $this->isNew = $address->isObjectNew();
    }

    public function onCustomerAddressSaveCommitAfter(Mage_Customer_Model_Address $address)
    {
        // Check config to see if hosted functionality is enabled
        if (Mage::getStoreConfig('autoship_subscription/hosted_features/use_hosted_address_book') != '1') {
            return;
        }

        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        /** @var SubscribePro_Autoship_Helper_Platform_Customer $platformCustomerHelper */
        $platformCustomerHelper = Mage::helper('autoship/platform_customer');

        // Get customer ID
        $spCustomerId = $platformCustomerHelper->fetchSubscribeProCustomerId($address->getCustomer());

        // Build details for session
        $addressDetails = $this->buildAddressDetails($spCustomerId, $address);

        // Is this a new or updated address?
        if ($this->isNew) {
            // New address created
            SubscribePro_Autoship::log('Saving created address details in customer session: ', Zend_Log::DEBUG);
            SubscribePro_Autoship::log(json_encode($addressDetails), Zend_Log::DEBUG);

            // Store details in customer session
            $details = $this->getAddressDetails(self::CREATED_ADDRESS_DETAILS_SESSION_KEY);
            $details[] = $addressDetails;
            $customerSession->setData(self::CREATED_ADDRESS_DETAILS_SESSION_KEY, $details);
        } else {
            // Address updated
            SubscribePro_Autoship::log('Saving updated address details in customer session: ', Zend_Log::DEBUG);
            SubscribePro_Autoship::log(json_encode($addressDetails), Zend_Log::DEBUG);

            // Store details in customer session
            $details = $this->getAddressDetails(self::UPDATED_ADDRESS_DETAILS_SESSION_KEY);
            $details[] = $addressDetails;
            $customerSession->setData(self::UPDATED_ADDRESS_DETAILS_SESSION_KEY, $details);
        }
    }

    public function onCustomerAddressDeleteCommitAfter(Mage_Customer_Model_Address $address)
    {
        // Check config to see if hosted functionality is enabled
        if (Mage::getStoreConfig('autoship_subscription/hosted_features/use_hosted_address_book') != '1') {
            return;
        }

        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        /** @var SubscribePro_Autoship_Helper_Platform_Customer $platformCustomerHelper */
        $platformCustomerHelper = Mage::helper('autoship/platform_customer');

        // Get customer ID
        $spCustomerId = $platformCustomerHelper->fetchSubscribeProCustomerId($address->getCustomer());

        // Build details for session
        $deletedAddressDetails = $this->buildAddressDetails($spCustomerId, $address);

        SubscribePro_Autoship::log('Saving deleted address details in customer session: ', Zend_Log::DEBUG);
        SubscribePro_Autoship::log(json_encode($deletedAddressDetails), Zend_Log::DEBUG);

        // Store details in customer session
        $details = $this->getAddressDetails(self::DELETED_ADDRESS_DETAILS_SESSION_KEY);
        $details[] = $deletedAddressDetails;
        $customerSession->setData(self::DELETED_ADDRESS_DETAILS_SESSION_KEY, $details);
    }

    public function getAddressDetails($sessionKey)
    {
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');

        // Fetch details from session
        if ($customerSession->hasData($sessionKey)) {
            $details = $customerSession->getData($sessionKey);
        } else {
            $details = array();
        }

        return $details;
    }

    public function wipeAddressDetails($sessionKey)
    {
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');

        $customerSession->unsetData($sessionKey);
    }

    /**
     * @param string|null $spCustomerId
     * @param Mage_Customer_Model_Address $address
     * @return array
     */
    protected function buildAddressDetails($spCustomerId, Mage_Customer_Model_Address $address)
    {
        $addressDetails = array(
            'address' => array(
                'is_default_billing' => (
                    $address->getCustomer()->getDefaultBillingAddress() instanceof Mage_Customer_Model_Address
                    && $address->getId() == $address->getCustomer()->getDefaultBillingAddress()->getId()
                ),
                'is_default_shipping' => (
                    $address->getCustomer()->getDefaultShippingAddress() instanceof Mage_Customer_Model_Address
                    && $address->getId() == $address->getCustomer()->getDefaultShippingAddress()->getId()
                ),
                'first_name' => $address->getData('firstname'),
                'middle_name' => $address->getData('middlename'),
                'last_name' => $address->getData('lastname'),
                'company' => $address->getData('company'),
                'city' => $address->getData('city'),
                'region' => $address->getRegion(),
                'postcode' => $address->getData('postcode'),
                'country' => $address->getData('country_id'),
                'phone' => $address->getData('telephone'),
            ),
        );
        if (strlen($spCustomerId)) {
            $addressDetails['address']['customer_id'] = $spCustomerId;
        }
        if (strlen($address->getStreet1())) {
            $addressDetails['address']['street1'] = $address->getStreet1();
        }
        if (strlen($address->getStreet2())) {
            $addressDetails['address']['street2'] = $address->getStreet2();
        }

        return $addressDetails;
    }

}
