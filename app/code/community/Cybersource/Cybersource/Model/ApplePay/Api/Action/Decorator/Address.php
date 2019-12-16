<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_Address
{
    /**
     * Does the adapter consider the address valid?
     *
     * @param Mage_Customer_Model_Address_Abstract $address
     * @return bool
     */
    public function isValidAddress(Mage_Customer_Model_Address_Abstract $address)
    {
        return $address->hasCountryId()
            && $address->hasRegion()
            && $address->hasCity()
            && $address->hasPostcode();
    }

    /**
     * Build an address node
     *
     * @param Mage_Customer_Model_Address_Abstract $address
     * @param $payload stdClass The API payload to decorate
     * @param $nodeName string The name of the node to append the address to
     */
    public function decorate(Mage_Customer_Model_Address_Abstract $address, stdClass $payload, $nodeName)
    {
        if (!$this->isValidAddress($address)) {
            return;
        }
        $optionalItems = array(
            'firstName' => 'getFirstname',
            'lastName' => 'getLastname',
            'company' => 'getCompany',
            'customerID' => 'getCustomerId',
            'phoneNumber' => 'getTelephone',
            'email' => 'getEmail',
            'street1' => 'getStreet1',
            'street2' => 'getStreet2',
        );

        $addressPayload = new stdClass();
        $addressPayload->city = $address->getCity();
        $addressPayload->state = $address->hasRegionId() ? $address->getRegionCode() : $address->getRegion();
        $addressPayload->postalCode = $address->getPostcode();
        $addressPayload->country = $address->getCountryId();

        foreach ($optionalItems as $parameter => $method) {
            if ($address->$method()) {
                $addressPayload->$parameter = $address->$method();
            }
        }

        $payload->$nodeName = $addressPayload;
    }

}
