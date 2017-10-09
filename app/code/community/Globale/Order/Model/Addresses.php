<?php

/**
 * Class Globale_Order_Model_Addresses
 */
class Globale_Order_Model_Addresses extends Mage_Core_Model_Abstract
{

    const BILLING   = 'Billing';
    const SHIPPING  = 'Shipping';

    public function _construct()
    {
        parent::_construct();
        $this->_init('globale_order/addresses'); // this is location of the resource file.
    }

    /**
     * Save Addresses in DB, in globale_order_addresses
     * @param $Address
     * @param $IncrementId
     * @param $Type
     * @param $IsPrimary
     */
    public function saveAddresses($Address, $IncrementId, $Type, $IsPrimary){

        $this->setOrderId($IncrementId)
             ->setType($Type)
             ->setIsPrimary($IsPrimary)
             ->setAddress1($Address->Address1)
             ->setAddress2($Address->Address2)
             ->setCompany($Address->Company)
             ->setCountryCode($Address->CountryCode)
             ->setCountryCode3($Address->CountryCode3)
             ->setCountryName($Address->CountryName)
             ->setCity($Address->City)
             ->setEmail($Address->Email)
             ->setFax($Address->Fax)
             ->setFirstName($Address->FirstName)
             ->setLastName($Address->LastName)
             ->setMiddleName($Address->MiddleName)
             ->setPhone1($Address->Phone1)
             ->setPhone2($Address->Phone2)
             ->setSalutation($Address->Salutation)
             ->setStateCode($Address->StateCode)
             ->setStateOrProvince($Address->StateOrProvince)
             ->setZip($Address->Zip);

        $this->save();
    }

}