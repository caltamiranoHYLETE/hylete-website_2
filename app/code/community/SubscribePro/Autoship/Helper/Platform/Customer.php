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

use \SubscribePro\Service\Customer\CustomerInterface;

class SubscribePro_Autoship_Helper_Platform_Customer extends SubscribePro_Autoship_Helper_Platform_Abstract
{

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $saveMagentoCustomer
     * @return CustomerInterface
     */
    public function createOrUpdatePlatformCustomer(Mage_Customer_Model_Customer $customer, $saveMagentoCustomer = true)
    {
        // Check if customer exists
        $spCustomer = $this->loadSubscribeProCustomer($customer);
        // If not exists, create new customer
        if (!$spCustomer instanceof CustomerInterface) {
            $spCustomer = $this->getCustomerService()->createCustomer();
            $spCustomer->setEmail($customer->getData('email'));
        }

        // Map fields to customer
        $spCustomer->setFirstName($customer->getData('firstname'));
        $spCustomer->setMiddleName($customer->getData('middlename'));
        $spCustomer->setLastName($customer->getData('lastname'));
        $spCustomer->setMagentoCustomerId($customer->getId());
        $spCustomer->setMagentoCustomerGroupId($customer->getGroupId());
        $spCustomer->setMagentoWebsiteId($customer->getData('website_id'));

        // Save = Create or Update
        $this->getCustomerService()->saveCustomer($spCustomer);

        // Save customer ID in Magento
        $customer->setData('subscribe_pro_customer_id', $spCustomer->getId());
        if ($saveMagentoCustomer) {
            $customer->save();
        }

        return $spCustomer;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $saveMagentoCustomer
     * @return bool
     */
    public function updatePlatformCustomerIfExists(Mage_Customer_Model_Customer $customer, $saveMagentoCustomer = true)
    {
        // Check if customer exists
        $spCustomer = $this->loadSubscribeProCustomer($customer);
        // If not exists, create new customer
        if ($spCustomer instanceof CustomerInterface) {
            // Map fields to customer
            $spCustomer->setEmail($customer->getData('email'));
            $spCustomer->setFirstName($customer->getData('firstname'));
            $spCustomer->setMiddleName($customer->getData('middlename'));
            $spCustomer->setLastName($customer->getData('lastname'));
            $spCustomer->setMagentoCustomerId($customer->getId());
            $spCustomer->setMagentoCustomerGroupId($customer->getGroupId());
            $spCustomer->setMagentoWebsiteId($customer->getData('website_id'));

            // Update
            $this->getCustomerService()->saveCustomer($spCustomer);

            // Save customer ID in Magento
            $customer->setData('subscribe_pro_customer_id', $spCustomer->getId());
            if ($saveMagentoCustomer) {
                $customer->save();
            }

            return true;
        }

        return false;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return string|null
     */
    public function fetchSubscribeProCustomerId(Mage_Customer_Model_Customer $customer)
    {
        if (strlen($customer->getData('subscribe_pro_customer_id'))) {
            return $customer->getData('subscribe_pro_customer_id');
        }
        else {
            $platformCustomers = $this->getCustomerService()->loadCustomers(array(
                CustomerInterface::EMAIL => $customer->getData('email'),
            ));
            if (!empty($platformCustomers)) {
                return $platformCustomers[0]->getId();
            }
            else {
                return null;
            }
        }
    }

    /**
     * @param $customerId
     * @return \SubscribePro\Service\Address\AddressInterface[]
     */
    public function getCustomerAddresses($customerId)
    {
        return $this->getAddressService()->loadAddresses($customerId);
    }

    /**
     * @param $customerId
     * @param array $data
     * @return \SubscribePro\Service\Address\AddressInterface
     */
    public function initCustomerAddress($customerId, array $data)
    {
        $address = $this->getAddressService()->createAddress($data);
        $address->setCustomerId($customerId);

        return $address;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param $customerId
     * @return \SubscribePro\Service\Address\AddressInterface[]
     */
    public function getMergedSubscribeProAndMagentoCustomerAddresses(Mage_Customer_Model_Customer $customer, $customerId)
    {
        $spAddresses = $this->getAddressService()->loadAddresses($customerId);
        $updatedAddresses = $this->updateAddressesFromMagentoAddresses($customer, $spAddresses);
        $mergedAddresses = $this->mergeMagentoAddresses($customer, $customerId, $updatedAddresses);

        return $mergedAddresses;
    }

    /**
     * @return string
     */
    public function getWidgetAccessToken(Mage_Customer_Model_Customer $customer)
    {
        $spCustomerId = $this->fetchSubscribeProCustomerId($customer);
        $accessToken = $this->getApiHelper()->getSdk()->getOauthTool()->retrieveWidgetAccessTokenByCustomerId($spCustomerId);

        return $accessToken;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return null|CustomerInterface
     */
    protected function loadSubscribeProCustomer(Mage_Customer_Model_Customer $customer)
    {
        if (strlen($customer->getData('subscribe_pro_customer_id'))) {
            $spCustomer = $this->getCustomerService()->loadCustomer($customer->getData('subscribe_pro_customer_id'));
        }
        else {
            $spCustomer = $this->loadSubscribeProCustomerByEmail($customer->getData('email'));
        }

        return $spCustomer;
    }

    /**
     * @param $email
     * @return bool|CustomerInterface
     */
    protected function loadSubscribeProCustomerByEmail($email)
    {
        $platformCustomers = $this->getCustomerService()->loadCustomers(array(
            CustomerInterface::EMAIL => $email,
        ));
        if (!empty($platformCustomers)) {
            return $platformCustomers[0];
        }
        else {
            return null;
        }
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param $customerId
     * @param array $spAddresses
     * @return array
     */
    protected function mergeMagentoAddresses(Mage_Customer_Model_Customer $customer, $customerId, array $spAddresses)
    {
        $addressesOut = $spAddresses;
        // Iterate Magento addresses
        $magentoAddresses = $customer->getAddresses();
        /** @var Mage_Customer_Model_Address_Abstract $magentoAddress */
        foreach ($magentoAddresses as $magentoAddress) {
            // Make an SP address out of the Magento address
            $newSpAddress = $this->getAddressService()->createAddress(array(
                'customer_id' => $customerId,
                'first_name' => $magentoAddress->getData('firstname'),
                'last_name' => $magentoAddress->getData('lastname'),
                'company' => $magentoAddress->getData('company'),
                'street1' => $magentoAddress->getStreet1(),
                'street2' => $magentoAddress->getStreet2(),
                'city' => $magentoAddress->getData('city'),
                'region' => $magentoAddress->getRegionCode(),
                'country' => $magentoAddress->getData('country_id'),
                'phone' => $magentoAddress->getData('telephone'),
            ));
            // Iterate sp addresses
            $spAddressMatches = false;
            foreach ($spAddresses as $spAddress) {
                if ($this->compareAddresses($newSpAddress, $spAddress) == 0) {
                    $spAddressMatches = true;
                    break;
                }
            }
            // Insert if no match
            if (!$spAddressMatches) {
                // Create new SP address
                $newSpAddress = $this->getAddressService()->saveAddress($newSpAddress);
                $addressesOut[] = $newSpAddress;
            }
        }

        return $addressesOut;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param array $spAddresses
     * @return array
     */
    protected function updateAddressesFromMagentoAddresses(Mage_Customer_Model_Customer $customer, array $spAddresses)
    {
        $addressesOut = array();
        // Iterate Magento addresses
        $magentoAddresses = $customer->getAddresses();

        /** @var \SubscribePro\Service\Address\AddressInterface $spAddress */
        foreach ($spAddresses as $spAddress) {
            if (strlen($spAddress->getMagentoAddressId())) {
                /** @var Mage_Customer_Model_Address_Abstract $magentoAddress */
                foreach ($magentoAddresses as $magentoAddress) {
                    if ($spAddress->getMagentoAddressId() == $magentoAddress->getEntityId()) {
                        $spAddress->setFirstName($magentoAddress->getData('firstname'));
                        $spAddress->setLastName($magentoAddress->getData('lastname'));
                        $spAddress->setCompany($magentoAddress->getData('company'));
                        $spAddress->setStreet1($magentoAddress->getStreet1());
                        $spAddress->setStreet2($magentoAddress->getStreet2());
                        $spAddress->setCity($magentoAddress->getData('city'));
                        $spAddress->setRegion($magentoAddress->getRegionCode());
                        $spAddress->setCountry($magentoAddress->getData('country_id'));
                        $spAddress->setPhone($magentoAddress->getData('telephone'));
                        // Save address to platform
                        $spAddress = $this->getAddressService()->saveAddress($spAddress);
                    }
                }
            }

            $addressesOut[] = $spAddress;
        }

        return $addressesOut;
    }

    /**
     * @param \SubscribePro\Service\Address\AddressInterface $a
     * @param \SubscribePro\Service\Address\AddressInterface $b
     * @return int
     */
    protected function compareAddresses(\SubscribePro\Service\Address\AddressInterface $a, \SubscribePro\Service\Address\AddressInterface $b)
    {
        $accessorMethods = array(
            'getFirstName',
            'getLastName',
            'getCompany',
            'getStreet1',
            'getStreet2',
            'getCity',
            'getRegion',
            'getCountry',
            'getPhone',
        );

        // Compare all fields
        foreach ($accessorMethods as $method) {
            $result = strcasecmp(
                trim($a->$method()),
                trim($b->$method())
            );
            if ($result != 0) {
                return $result;
            }
        }

        // Otherwise all matched
        return 0;
    }

}
