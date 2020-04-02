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

use \SubscribePro\Service\PaymentProfile\PaymentProfileInterface;

class SubscribePro_Autoship_Helper_Platform_Vault extends SubscribePro_Autoship_Helper_Platform_Abstract
{

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param array $filters
     * @return \SubscribePro\Service\PaymentProfile\PaymentProfileInterface[]
     */
    public function getPaymentProfilesForCustomer(Mage_Customer_Model_Customer $customer, $filters = array())
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Customer $platformCustomerHelper */
        $platformCustomerHelper = Mage::helper('autoship/platform_customer');

        $spCustomerId = $platformCustomerHelper->fetchSubscribeProCustomerId($customer);
        if (strlen($spCustomerId)) {
            return $this->getPaymentProfilesForCustomerById($spCustomerId, $filters);
        }
        else {
            return array();
        }
    }

    /**
     * @param string $spCustomerId
     * @param array $filters
     * @return \SubscribePro\Service\PaymentProfile\PaymentProfileInterface[]
     */
    public function getPaymentProfilesForCustomerById($spCustomerId, $filters = array())
    {
        // If empty SP customer ID, return empty array
        if (!strlen($spCustomerId)) {
            return array();
        }
        // Merge filters
        $filters = array_merge($filters, array(
            'customer_id' => $spCustomerId
        ));

        // Query & Return results from API
        return $this->getPaymentProfileService()->loadProfiles($filters);
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return array
     */
    public function getCreditCardProfilesForCustomer(Mage_Customer_Model_Customer $customer)
    {
        $paymentProfiles = $this->getPaymentProfilesForCustomer($customer);
        // Filter by profile type
        $filteredProfiles = array_filter(
            $paymentProfiles,
            function($profile) {
                /** @var SubscribePro\Service\PaymentProfile\PaymentProfileInterface $profile */
                return  $profile->getPaymentMethodType() == PaymentProfileInterface::TYPE_CREDIT_CARD
                            ||
                        $profile->getPaymentMethodType() == PaymentProfileInterface::TYPE_THIRD_PARTY_TOKEN;
            });

        return $filteredProfiles;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return \SubscribePro\Service\PaymentProfile\PaymentProfileInterface[]
     */
    public function getBankAccountProfilesForCustomer(Mage_Customer_Model_Customer $customer)
    {
        $paymentProfiles = $this->getPaymentProfilesForCustomer($customer, array(
            'profile_type' => PaymentProfileInterface::TYPE_SPREEDLY_VAULT,
            'payment_method_type' => PaymentProfileInterface::TYPE_BANK_ACCOUNT,
        ));

        return $paymentProfiles;
    }

    /**
     * @param integer $paymentProfileId
     * @return PaymentProfileInterface
     */
    public function getPaymentProfile($paymentProfileId)
    {
        return $this->getPaymentProfileService()->loadProfile($paymentProfileId);
    }

    /**
     * @param $paymentToken
     * @return PaymentProfileInterface
     */
    public function getPaymentProfileByToken($paymentToken)
    {
        return $this->getPaymentProfileService()->loadProfileByToken($paymentToken);
    }

    /**
     * Init profile with customer data from customer record
     *
     * @param PaymentProfileInterface $paymentProfile
     * @param $customer
     * @return PaymentProfileInterface
     */
    public function initProfileWithCustomerDefault(PaymentProfileInterface $paymentProfile, $customer)
    {
        // Load customer
        if (!$customer instanceof Mage_Customer_Model_Customer) {
            /** @var Mage_Customer_Model_Customer $model */
            $customer = Mage::getModel('customer/customer')->load($customer);
        }
        // Grab billing address
        $addressId = $customer->getData('default_billing');
        // Add address data if default billing addy exists
        if ($addressId) {
            // Get address
            $billingAddress = Mage::getModel('customer/address')->load($addressId);
            // Map
            $this->mapMagentoAddressToPlatform($billingAddress, $paymentProfile->getBillingAddress());
        }
        else {
            // Empty(ish) billing address
            $paymentProfile->getBillingAddress()->setFirstName($customer->getData('firstname'));
            $paymentProfile->getBillingAddress()->setLastName($customer->getData('lastname'));
        }

        return $paymentProfile;
    }

    /**
     * @param Mage_Customer_Model_Address_Abstract $billingAddress
     * @param $cardNumber
     * @param $expMonth
     * @param $expYear
     * @param null $cvv
     * @return \SubscribePro\Service\Token\TokenInterface
     */
    public function createPaymentToken(Mage_Customer_Model_Address_Abstract $billingAddress, $cardNumber, $expMonth, $expYear, $cvv = null)
    {
        // Build request data
        $requestData = array(
            'billing_address' => array(
                'first_name' => $billingAddress->getData('firstname'),
                'last_name' => $billingAddress->getData('lastname'),
            ),
            'creditcard_number' => $cardNumber,
            'creditcard_month' => $expMonth,
            'creditcard_year' => $expYear,
        );
        // Optionally add CVV
        if (strlen($cvv)) {
            $requestData['creditcard_verification_value'] = $cvv;
        }
        // Add optional fields - billing address
        $optionalFields = array('company' => 'company', 'city' => 'city', 'postcode' => 'postcode', 'country' => 'country_id', 'phone' => 'telephone', );
        foreach ($optionalFields as $fieldKey => $magentoFieldKey) {
            if (strlen($billingAddress->getData($magentoFieldKey))) {
                $requestData['billing_address'][$fieldKey] = $billingAddress->getData($magentoFieldKey);
            }
        }
        if (strlen($billingAddress->getStreet1())) {
            $requestData['billing_address']['street1'] = $billingAddress->getStreet1();
        }
        if (strlen($billingAddress->getStreet2())) {
            $requestData['billing_address']['street2'] = $billingAddress->getStreet2();
        }
        if (strlen($billingAddress->getRegionCode())) {
            $requestData['billing_address']['region'] = $billingAddress->getRegionCode();
        }

        // Create token
        $token = $this->getTokenService()->createToken($requestData);
        $token = $this->getTokenService()->saveToken($token);

        return $token;
    }

    /**
     * @param Mage_Customer_Model_Address_Abstract $billingAddress
     * @param array $applePayPaymentData
     * @return \SubscribePro\Service\Token\TokenInterface
     */
    public function createApplePayPaymentToken(Mage_Customer_Model_Address_Abstract $billingAddress, array $applePayPaymentData)
    {
        // Build request data
        $requestData = array(
            'billing_address' => [
                'first_name' => $billingAddress->getData('firstname'),
                'last_name' => $billingAddress->getData('lastname'),
            ],
            'applepay_payment_data' => $applePayPaymentData,
        );
        // Add optional fields - billing address
        $optionalFields = ['company' => 'company', 'city' => 'city', 'postcode' => 'postcode', 'country' => 'country_id', 'phone' => 'telephone', ];
        foreach ($optionalFields as $fieldKey => $magentoFieldKey) {
            if (strlen($billingAddress->getData($magentoFieldKey))) {
                $requestData['billing_address'][$fieldKey] = $billingAddress->getData($magentoFieldKey);
            }
        }
        if (strlen($billingAddress->getStreet1())) {
            $requestData['billing_address']['street1'] = $billingAddress->getStreet1();
        }
        if (strlen($billingAddress->getStreet2())) {
            $requestData['billing_address']['street2'] = $billingAddress->getStreet2();
        }
        if (strlen($billingAddress->getRegionCode())) {
            $requestData['billing_address']['region'] = $billingAddress->getRegionCode();
        }

        // Create token
        $token = $this->getTokenService()->createToken($requestData);
        $token = $this->getTokenService()->saveToken($token);

        return $token;
    }

    /**
     * @param string $subscribeProCustomerId
     * @param string $spreedlyToken
     * @param null|Mage_Customer_Model_Customer $customer
     * @param null|Mage_Customer_Model_Address_Abstract $billingAddress
     * @param null|string $expMonth
     * @param null|string $expYear
     * @return PaymentProfileInterface
     */
    public function createPaymentProfileFromToken($subscribeProCustomerId, $spreedlyToken, $customer = null, $billingAddress = null, $expMonth = null, $expYear = null)
    {
        // New payment profile
        $paymentProfile = $this->getPaymentProfileService()->createCreditCardProfile();
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $paymentProfile = $this->initProfileWithCustomerDefault($paymentProfile, $customer);
        }
        if ($billingAddress instanceof Mage_Customer_Model_Address_Abstract) {
            $this->mapMagentoAddressToPlatform($billingAddress, $paymentProfile->getBillingAddress());
        }
        $paymentProfile->setCustomerId($subscribeProCustomerId);
        $paymentProfile->setCreditcardMonth($expMonth);
        $paymentProfile->setCreditcardYear($expYear);
        // Turn token into payment profile
        $paymentProfile = $this->getPaymentProfileService()->saveToken($spreedlyToken, $paymentProfile);

        return $paymentProfile;
    }

    /**
     * @param int $subscribeProCustomerId
     * @param null|Mage_Customer_Model_Customer $customer
     * @param null|Mage_Customer_Model_Address_Abstract $billingAddress
     * @param string $bankAccountNumber
     * @param string $bankRoutingNumber
     * @param string $bankAccountType
     * @param string $bankAccountHolderType
     * @return PaymentProfileInterface
     */
    public function createBankAccountPaymentProfile($subscribeProCustomerId, $customer = null, $billingAddress = null, $bankAccountNumber, $bankRoutingNumber, $bankAccountType, $bankAccountHolderType)
    {
        // New payment profile
        $paymentProfile = $this->getPaymentProfileService()->createBankAccountProfile();
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $paymentProfile = $this->initProfileWithCustomerDefault($paymentProfile, $customer);
        }
        if ($billingAddress instanceof Mage_Customer_Model_Address_Abstract) {
            $this->mapMagentoAddressToPlatform($billingAddress, $paymentProfile->getBillingAddress());
        }
        // Set SP customer id
        $paymentProfile->setCustomerId($subscribeProCustomerId);
        // Update payment profile with post data
        $paymentProfile->setBankAccountNumber($bankAccountNumber);
        $paymentProfile->setBankRoutingNumber($bankRoutingNumber);
        $paymentProfile->setBankAccountType($bankAccountType);
        $paymentProfile->setBankAccountHolderType($bankAccountHolderType);

        // Create and save profile via API
        $this->getPaymentProfileService()->saveProfile($paymentProfile);

        return $paymentProfile;
    }

    /**
     * @param int $subscribeProCustomerId
     * @param null|Mage_Customer_Model_Customer $customer
     * @param null|Mage_Customer_Model_Address_Abstract $billingAddress
     * @param array $applePayPaymentData
     * @return PaymentProfileInterface
     */
    public function createApplePayPaymentProfile($subscribeProCustomerId, $customer = null, $billingAddress = null, array $applePayPaymentData)
    {
        // New payment profile
        $paymentProfile = $this->getPaymentProfileService()->createApplePayProfile();
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $paymentProfile = $this->initProfileWithCustomerDefault($paymentProfile, $customer);
        }
        if ($billingAddress instanceof Mage_Customer_Model_Address_Abstract) {
            $spBillingAddress = $paymentProfile->getBillingAddress();
            $this->mapMagentoAddressToPlatform($billingAddress, $spBillingAddress);
            $paymentProfile->setBillingAddress($spBillingAddress);
        }
        // Set SP customer id
        $paymentProfile->setCustomerId($subscribeProCustomerId);
        // Update payment profile with post data
        $paymentProfile->setApplePayPaymentData($applePayPaymentData);

        // Create and save profile via API
        $this->getPaymentProfileService()->saveProfile($paymentProfile);

        return $paymentProfile;
    }

    /**
     * @param PaymentProfileInterface $paymentProfile
     */
    public function updatePaymentProfile(PaymentProfileInterface $paymentProfile)
    {
        $this->getPaymentProfileService()->saveProfile($paymentProfile);
    }

    /**
     * @param $paymentProfileId
     */
    public function deletePaymentProfile($paymentProfileId)
    {
        $this->getPaymentProfileService()->redactProfile($paymentProfileId);
    }

    /**
     * @param string $paymentToken
     * @param float $amount
     * @param string $currencyCode
     * @param null|Mage_Customer_Model_Address_Abstract $billingAddress
     * @param array $additionalFields
     * @return \SubscribePro\Service\Token\TokenInterface|\SubscribePro\Service\Transaction\TransactionInterface
     */
    public function authorizeOneTime($paymentToken, $amount, $currencyCode, $billingAddress = null, $additionalFields = array())
    {
        // Build request data
        $requestData = array_merge($additionalFields, array(
            'amount' => number_format(100 * $amount, 0, "", ""),
            'currency_code' => $currencyCode,
        ));

        if ($billingAddress instanceof Mage_Customer_Model_Address_Abstract) {
            $spBillingAddress = $this->getAddressService()->createAddress();
            $this->mapMagentoAddressToPlatform($billingAddress, $spBillingAddress);
        }
        else {
            $spBillingAddress = null;
        }

        // Run transaction
        $transaction = $this->getTransactionService()->createTransaction($requestData);
        $transaction = $this->getApiHelper()->getSdk('payments_api')->getTransactionService()->authorizeByToken($paymentToken, $transaction, $spBillingAddress);

        // Check response
        if ($transaction->getState() != \SubscribePro\Service\Transaction\TransactionInterface::STATE_SUCCEEDED) {
            Mage::throwException($this->__('Failed to authorize!'));
        }

        return $transaction;
    }

    /**
     * @param string $paymentToken
     * @param float $amount
     * @param string $currencyCode
     * @param null|Mage_Customer_Model_Address_Abstract $billingAddress
     * @param array $additionalFields
     * @return \SubscribePro\Service\Token\TokenInterface|\SubscribePro\Service\Transaction\TransactionInterface
     */
    public function purchaseOneTime($paymentToken, $amount, $currencyCode, $billingAddress = null, $additionalFields = array())
    {
        // Build request data
        $requestData = array_merge($additionalFields, array(
            'amount' => number_format(100 * $amount, 0, "", ""),
            'currency_code' => $currencyCode,
        ));

        if ($billingAddress instanceof Mage_Customer_Model_Address_Abstract) {
            $spBillingAddress = $this->getAddressService()->createAddress();
            $this->mapMagentoAddressToPlatform($billingAddress, $spBillingAddress);
        }
        else {
            $spBillingAddress = null;
        }

        // Run transaction
        $transaction = $this->getTransactionService()->createTransaction($requestData);
        $transaction = $this->getApiHelper()->getSdk('payments_api')->getTransactionService()->purchaseByToken($paymentToken, $transaction, $spBillingAddress);

        // Check response
        if ($transaction->getState() != \SubscribePro\Service\Transaction\TransactionInterface::STATE_SUCCEEDED) {
            Mage::throwException($this->__('Failed to purchase!'));
        }

        return $transaction;
    }

    /**
     * @param $paymentProfile
     * @param $amount
     * @param $currencyCode
     * @param array $additionalFields
     * @return \SubscribePro\Service\Transaction\TransactionInterface
     */
    public function authorize($paymentProfile, $amount, $currencyCode, $additionalFields = array())
    {
        // Build request data
        $requestData = array_merge($additionalFields, array(
            'amount' => number_format(100 * $amount, 0, "", ""),
            'currency_code' => $currencyCode,
        ));

        // Get profile ID
        if ($paymentProfile instanceof PaymentProfileInterface) {
            $paymentProfileId = $paymentProfile->getId();
        }
        else {
            $paymentProfileId = $paymentProfile;
        }

        // Run transaction
        $transaction = $this->getTransactionService()->createTransaction($requestData);
        $transaction = $this->getApiHelper()->getSdk('payments_api')->getTransactionService()->authorizeByProfile(array('profile_id' => $paymentProfileId), $transaction);

        // Check response
        if ($transaction->getState() != \SubscribePro\Service\Transaction\TransactionInterface::STATE_SUCCEEDED) {
            Mage::throwException($this->__('Failed to authorize!'));
        }

        return $transaction;
    }

    /**
     * @param $priorTransactionId
     * @param $amount
     * @param $currencyCode
     * @param array $additionalFields
     * @return \SubscribePro\Service\Transaction\TransactionInterface
     */
    public function capture($priorTransactionId, $amount, $currencyCode, $additionalFields = array())
    {
        // Build request data
        $requestData = array_merge($additionalFields, array(
            'amount' => number_format(100 * $amount, 0, "", ""),
            'currency_code' => $currencyCode,
        ));

        // Run transaction
        $transaction = $this->getTransactionService()->createTransaction($requestData);
        $transaction = $this->getApiHelper()->getSdk('payments_api')->getTransactionService()->capture($priorTransactionId, $transaction);

        // Check response
        if ($transaction->getState() != \SubscribePro\Service\Transaction\TransactionInterface::STATE_SUCCEEDED) {
            Mage::throwException($this->__('Failed to capture!'));
        }

        return $transaction;
    }

    /**
     * @param $priorTransactionId
     * @return \SubscribePro\Service\Transaction\TransactionInterface
     */
    public function void($priorTransactionId)
    {
        // Run transaction
        $transaction = $this->getApiHelper()->getSdk('payments_api')->getTransactionService()->void($priorTransactionId);

        // Check response
        if ($transaction->getState() != \SubscribePro\Service\Transaction\TransactionInterface::STATE_SUCCEEDED) {
            Mage::throwException($this->__('Failed to void!'));
        }

        return $transaction;
    }

    /**
     * @param PaymentProfileInterface|int $paymentProfile
     * @param $amount
     * @param $currencyCode
     * @param array $additionalFields
     * @return \SubscribePro\Service\Transaction\TransactionInterface
     */
    public function purchase($paymentProfile, $amount, $currencyCode, $additionalFields = array())
    {
        // Build request data
        $requestData = array_merge($additionalFields, array(
            'amount' => number_format(100 * $amount, 0, "", ""),
            'currency_code' => $currencyCode,
        ));

        // Get profile ID
        if ($paymentProfile instanceof PaymentProfileInterface) {
            $paymentProfileId = $paymentProfile->getId();
        }
        else {
            $paymentProfileId = $paymentProfile;
        }

        // Run transaction
        $transaction = $this->getTransactionService()->createTransaction($requestData);
        $transaction = $this->getApiHelper()->getSdk('payments_api')->getTransactionService()->purchaseByProfile(array('profile_id' => $paymentProfileId), $transaction);

        // Check response
        if ($transaction->getState() != \SubscribePro\Service\Transaction\TransactionInterface::STATE_SUCCEEDED) {
            Mage::throwException($this->__('Failed to purchase!'));
        }

        return $transaction;
    }

    /**
     * @param $priorTransactionId
     * @param $amount
     * @param $currencyCode
     * @param array $additionalFields
     * @return \SubscribePro\Service\Transaction\TransactionInterface
     */
    public function credit($priorTransactionId, $amount, $currencyCode, $additionalFields = array())
    {
        // Build request data
        $requestData = array_merge($additionalFields, array(
            'amount' => number_format(100 * $amount, 0, "", ""),
            'currency_code' => $currencyCode,
        ));

        // Run transaction
        $transaction = $this->getTransactionService()->createTransaction($requestData);
        $transaction = $this->getApiHelper()->getSdk('payments_api')->getTransactionService()->credit($priorTransactionId, $transaction);

        // Check response
        if ($transaction->getState() != \SubscribePro\Service\Transaction\TransactionInterface::STATE_SUCCEEDED) {
            Mage::throwException($this->__('Failed to credit!'));
        }

        return $transaction;
    }

    /**
     * @param PaymentProfileInterface|int $paymentProfile
     * @param $amount
     * @param $currencyCode
     * @param array $additionalFields
     * @return \SubscribePro\Service\Transaction\TransactionInterface
     */
    public function verify($paymentProfile, $amount, $currencyCode, $additionalFields = array())
    {
        // Build request data
        $requestData = array_merge($additionalFields, array(
            'amount' => number_format(100 * $amount, 0, "", ""),
            'currency_code' => $currencyCode,
        ));

        // Get profile ID
        if ($paymentProfile instanceof PaymentProfileInterface) {
            $paymentProfileId = $paymentProfile->getId();
        }
        else {
            $paymentProfileId = $paymentProfile;
        }

        // Run transaction
        $transaction = $this->getTransactionService()->createTransaction($requestData);
        $transaction = $this->getApiHelper()->getSdk('payments_api')->getTransactionService()->verifyProfile($paymentProfileId, $transaction);

        // Check response
        if ($transaction->getState() != \SubscribePro\Service\Transaction\TransactionInterface::STATE_SUCCEEDED) {
            Mage::throwException($this->__('Failed to verify!'));
        }

        return $transaction;
    }

    /**
     * @param $type
     * @param bool $throwExceptionOnTypeNotFound
     * @return null
     */
    public function mapMagentoCardTypeToSubscribePro($type, $throwExceptionOnTypeNotFound = true)
    {
        // Map of card types
        $cardTypes = $this->getAllCardTypeMappings();
        $cardTypes = array_flip($cardTypes);

        if (isset($cardTypes[$type])) {
            return $cardTypes[$type];
        }
        else {
            if ($throwExceptionOnTypeNotFound) {
                Mage::throwException($this->__('Invalid credit card type: %s', $type));
            }
        }

        return null;
    }

    /**
     * @param $type
     * @param bool $throwExceptionOnTypeNotFound
     * @return mixed|null
     */
    public function mapSubscribeProCardTypeToMagento($type, $throwExceptionOnTypeNotFound = true)
    {
        // Map of card types
        $cardTypes = $this->getAllCardTypeMappings();

        if (isset($cardTypes[$type])) {
            return $cardTypes[$type];
        }
        else {
            if ($throwExceptionOnTypeNotFound) {
                Mage::throwException($this->__('Invalid credit card type: ' . $type));
            }
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getAllCardTypeMappings()
    {
        // Map of card types
        // Subscribe Pro / Spreedly type => Magento type
        $cardTypes = array(
            'visa' => 'VI',
            'master' => 'MC',
            'american_express' => 'AE',
            'discover' => 'DI',
            'jcb' => 'JCB',
        );

        return $cardTypes;
    }

    /**
     * @param Mage_Customer_Model_Address_Abstract $magentoAddress
     * @param \SubscribePro\Service\Address\AddressInterface $platformAddress
     */
    protected function mapMagentoAddressToPlatform(Mage_Customer_Model_Address_Abstract $magentoAddress, SubscribePro\Service\Address\AddressInterface $platformAddress)
    {
        $platformAddress->setFirstName($magentoAddress->getData('firstname'));
        $platformAddress->setLastName($magentoAddress->getData('lastname'));
        $platformAddress->setCompany($magentoAddress->getData('company'));
        $platformAddress->setStreet1((string) $magentoAddress->getStreet(1));
        if(strlen($magentoAddress->getStreet(2))) {
            $platformAddress->setStreet2((string) $magentoAddress->getStreet(2));
        }
        else {
            $platformAddress->setStreet2(null);
        }
        $platformAddress->setCity($magentoAddress->getData('city'));
        $platformAddress->setRegion($magentoAddress->getRegionCode());
        $platformAddress->setPostcode($magentoAddress->getData('postcode'));
        $platformAddress->setCountry($magentoAddress->getData('country_id'));
        $platformAddress->setPhone($magentoAddress->getData('telephone'));
    }

    /**
     * @param array $response
     * @param string $defaultMessage
     * @throws Mage_Core_Exception
     * @throws SubscribePro_Autoship_Helper_PaymentError_Exception
     */
    protected function throwErrorResponseException(array $response, $defaultMessage)
    {
        /** @var SubscribePro_Autoship_Helper_PaymentError $errorHelper */
        $errorHelper = Mage::helper('autoship/paymentError');
        // Check that response is well formed
        if (isset($response['result']) &&
            isset($response['result']['errors']) &&
            is_array($response['result']['errors'])
        ) {
            // Get 1st error
            $error = $response['result']['errors'][0];
            if (isset($error['attribute']) && $error['key']) {
                // Get attribute & key
                $attribute = $error['attribute'];
                $key = $error['key'];
                // If attribute and key translate to an error message, throw it in exception
                $message = $errorHelper->getCreditCardErrorMessage($attribute, $key);
                if (strlen($message)) {
                    throw new SubscribePro_Autoship_Helper_PaymentError_Exception($message);
                }
            }
        }

        // Otherwise throw default message
        Mage::throwException($this->__($defaultMessage));
    }

    /**
     * @param array $platformTransaction
     * @param $defaultMessage
     * @throws SubscribePro_Autoship_Helper_PaymentError_Exception
     */
    protected function throwTransactionErrorException(array $platformTransaction, $defaultMessage)
    {
        /** @var SubscribePro_Autoship_Helper_PaymentError $errorHelper */
        $errorHelper = Mage::helper('autoship/paymentError');
        // Check that response is well formed and has error type
        if (isset($platformTransaction['subscribe_pro_error_type'])) {
            // Map error type
            $message = $errorHelper->getGatewayErrorMessage($platformTransaction['subscribe_pro_error_type']);
            if (strlen($message)) {
                throw new SubscribePro_Autoship_Helper_PaymentError_Exception($message);
            }
        }

        // Otherwise throw default message
        Mage::throwException($this->__($defaultMessage));
    }

}
