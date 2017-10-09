<?php
namespace GlobalE\SDK\API\Common\Response;

use GlobalE\SDK\Models\Common;

/**
 * Class Country
 * @method getCode()
 * @method getName()
 * @method getSiteURL()
 * @method getIsStateMandatory()
 * @method getIsOperatedByGlobalE()
 * @method getDefaultCurrencyCode()
 * @method getDefaultVATRateType()
 * @method getUseCountryVAT()
 * @method getSupportsFixedPrices()
 * @method setCode($Code)
 * @method setName($Name)
 * @method setSiteURL($SiteURL)
 * @method setIsStateMandatory($IsStateMandatory)
 * @method setIsOperatedByGlobalE($IsOperatedByGlobalE)
 * @method setDefaultCurrencyCode($DefaultCurrencyCode)
 * @method setDefaultVATRateType($DefaultVATRateType)
 * @method setUseCountryVAT($UseCountryVAT)
 * @method setSupportsFixedPrices($SupportsFixedPrices)
 * @package GlobalE\SDK\API\Common\Response
 */
class Country extends Common {

	/**
	 * 2-char ISO country code
	 * @var string $Code
	 * @access public
	 */
	public $Code;

	/**
	 * Country name
	 * @var string $Name
	 * @access public
	 */
	public $Name;

	/**
	 * URL of a country-specific site owned by the merchant.
	 * Used to allow redirection to the merchant’s country-specific domain for a selected country.
	 * @var string $SiteURL
	 * @access public
	 */
	public $SiteURL;

	/**
	 * TRUE if State or province (region) is mandatory for addresses in this country.
	 * @var bool $IsStateMandatory
	 * @access public
	 */
	public $IsStateMandatory;

	/**
	 * Flag to be used as a Mode indicator.
	 * Countries that are not operated by Global-e must still be available for selection as a shipping destination on the merchant’s site.
	 * However Global-e functionality must be disabled for such countries.
	 * @var bool $IsOperatedByGlobalE
	 * @access public
	 */
	public $IsOperatedByGlobalE;

	/**
	 * 3-char ISO currency code
	 * @var string $DefaultCurrencyCode
	 * @access public
	 */
	public $DefaultCurrencyCode;

	/**
	 * Default (most widely used) VAT rate type or class for this country.
	 * @var \stdClass $DefaultVATRateType
	 * @access public
	 */
	public $DefaultVATRateType;

	/**
	 * TRUE if VAT rate specific to this shipping country must be applied to the product prices.
	 * Otherwise, VAT rates defined for the products on the merchant’s site must be used.
	 * This setting is used to support trade agreements between the countries such as EEA,
	 * when end customer must pay VAT for the shipping country in certain cases.
	 * @var bool $UseCountryVAT
	 * @access public
	 */
	public $UseCountryVAT;

	/**
	 * TRUE if fixed product prices are allowed for this country.
	 * Product prices may be fixed only in the Default Currency for this Country.
	 * @var bool $SupportsFixedPrices
	 * @access public
	 */
	public $SupportsFixedPrices;


}