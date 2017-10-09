<?php
namespace GlobalE\SDK\API\Common\Response;

use GlobalE\SDK\Models\Common;

/**
 * Class CountryCoefficient
 * @method getCountryCode()
 * @method getProductClassCode()
 * @method getRate()
 * @method getIncludeVAT()
 * @method setCountryCode($CountryCode)
 * @method setProductClassCode($ProductClassCode)
 * @method setRate($Rate)
 * @method setIncludeVAT($IncludeVAT)
 * @package GlobalE\SDK\API\Common\Response
 */
class CountryCoefficient extends Common {

	/**
	 * 2-char ISO country code
	 * @var string $CountryCode
	 * @access public
	 */
	public $CountryCode;

	/**
	 * Product class code used by the merchant to classify products for using different country coefficient rates per classes of products
	 * instead of the single country level default (when no rate is defined for a certain product class,
	 * the country level default rate should be used by the merchant for the respective product).
	 * @var string $ProductClassCode
	 * @access public
	 */
	public $ProductClassCode;

	/**
	 * Country Coefficient rate decimal value.
	 * All the prices displayed to the end customer shipping to the country involved must be multiplied by this value.
	 * @var float $Rate
	 * @access public
	 */
	public $Rate;

	/**
	 * This attribute is applicable only to Country level (i.e. not applicable to ProductClass level).
	 * One of the possible values of IncludeVATOptions enumeration designated to control the way
	 * VAT is handled in browsing on the Merchant’s site and in checkout on Global-e.
	 * @var int $IncludeVAT
	 * @access public
	 */
	public $IncludeVAT;
}