<?php
namespace GlobalE\SDK\API\Common;
use GlobalE\SDK\Models\Common;

/**
 * Class Address
 * @method getUserId()
 * @method getUserIdNumber()
 * @method getUserIdNumberType()
 * @method getFirstName()
 * @method getLastName()
 * @method getMiddleName()
 * @method getSalutation()
 * @method getPhone1()
 * @method getPhone2()
 * @method getFax()
 * @method getEmail()
 * @method getAddress1()
 * @method getAddress2()
 * @method getCity()
 * @method getStateOrProvince()
 * @method getZip()
 * @method getCountryCode()
 * @method getStateCode()
 * @method getCompany()
 * @method getIsBilling()
 * @method getIsDefaultBilling()
 * @method getIsShipping
 * @method getIsDefaultShipping
 * @method setUserId($UserId)
 * @method setUserIdNumber($UserIdNumber)
 * @method setUserIdNumberType($UserIdNumberType)
 * @method setFirstName($FirstName)
 * @method setLastName($LastName)
 * @method setMiddleName($MiddleName)
 * @method setSalutation($Salutation)
 * @method setPhone1($Phone1)
 * @method setPhone2($Phone2)
 * @method setFax($Fax)
 * @method setEmail($Email)
 * @method setAddress1($Address1)
 * @method setAddress2($Address2)
 * @method setCity($City)
 * @method setStateOrProvince($StateOrProvince)
 * @method setZip($Zip)
 * @method setCountryCode($CountryCode)
 * @method setStateCode($StateCode)
 * @method setCompany($Company)
 * @method setIsBilling($Flag)
 * @method setIsDefaultBilling($Flag)
 * @method setIsShipping($Flag)
 * @method setIsDefaultShipping($Flag)
 * @package GlobalE\SDK\API\Common
 */
class Address extends Common {

    /**
     * Internal User identifier
     * @var string $UserId
     * @access public
     */
    public $UserId;

    /**
     * User’s personal ID document number
     * @var string $UserIdNumber
     * @access public
     */
    public $UserIdNumber;

    /**
     * User’s personal ID document class type (e.g. Passport, ID card, etc.)
     * @var UserIdNumberType $UserIdNumberType
     * @access public
     */
    public $UserIdNumberType;

    /**
     * User first name
     * @var string $FirstName
     * @access public
     */
    public $FirstName;

    /**
     * User last name
     * @var string $LastName
     * @access public
     */
    public $LastName;

    /**
     * User middle name
     * @var string $MiddleName
     * @access public
     */
    public $MiddleName;

    /**
     * Salutation or title
     * @var string $Salutation
     * @access public
     */
    public $Salutation;

    /**
     * Phone #1
     * @var string $Phone1
     * @access public
     */
    public $Phone1;

    /**
     * Phone #2
     * @var string $Phone1
     * @access public
     */
    public $Phone2;

    /**
     * Fax
     * @var string $Fax
     * @access public
     */
    public $Fax;

    /**
     * User email
     * @var string $Email
     * @access public
     */
    public $Email;

    /**
     * User address line 1
     * @var string $Address1
     * @access public
     */
    public $Address1;

    /**
     * User address line 2
     * @var string $Address2
     * @access public
     */
    public $Address2;

    /**
     * City name
     * @var string $City
     * @access public
     */
    public $City;

    /**
     * State or province name
     * @var string $StateOrProvince
     * @access public
     */
    public $StateOrProvince;

    /**
     * Zip or postal code
     * @var string $Zip
     * @access public
     */
    public $Zip;

    /**
     * Country code
     * @var string $CountryCode
     * @access public
     */
    public $CountryCode;

    /**
     * Zip or postal code
     * @var string $Zip
     * @access public
     */
    public $StateCode;

    /**
     * Company name
     * @var string $Company
     * @access public
     */
    public $Company;

    /**
     * Billing address marker
     * @var bool
     */
    public $IsBilling;

    /**
     * Default billing address marker
     * @var bool
     */
    public $IsDefaultBilling;

    /**
     * Shipping address marker
     * @var bool
     */
    public $IsShipping;

    /**
     * Default shipping address marker
     * @var bool
     */
    public $IsDefaultShipping;
}