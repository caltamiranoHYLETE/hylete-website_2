<?php
namespace GlobalE\SDK\API\Common\Response;

use GlobalE\SDK\Models\Common;

/**
 * Class ProductCountry
 * @method getProductCode()
 * @method get$CountryCode()
 * @method getVATRateType()
 * @method getIsRestricted()
 * @method getRestrictionMessage()
 * @method getIsForbidden()
 * @method getForbiddanceMessage()
 * @method getIsVerified()
 * @method getTTL()
 * @method setProductCode($ProductCode)
 * @method setCountryCode($CountryCode)
 * @method setVATRateType($VATRateType)
 * @method setIsRestricted($IsRestricted)
 * @method setRestrictionMessage($RestrictionMessage)
 * @method setIsForbidden($IsForbidden)
 * @method setForbiddanceMessage($ForbiddanceMessage)
 * @method setIsVerified($IsVerified)
 * @method setTTL($TTL)
 * @package GlobalE\SDK\API\Common\Response
 */
class ProductCountry extends Common {

	/**
	 * SKU code used to identify the product on the Merchant’s site (to be mapped on Global-e side)
	 * @var string $ProductCode
	 * @access public
	 */
	public $ProductCode;

	/**
	 * 2-char ISO code of the shipping country
	 * @var string $CountryCode
	 * @access public
	 */
	public $CountryCode;

	/**
	 * Product’s VAT rate type or class to be used for this product in this shipping country.
	 * If not specified, the respective VATCategoryCountry’s VATRateType or Country’s DefaultVATRateType
	 * must be used if applicable according to UseCountryVAT value for this country.
	 * Otherwise, the VAT rate defined for this product on the merchant’s site must be used.
	 * @var object $VATRateType
	 * @access public
	 */
	public $VATRateType;

	/**
	 * TRUE if this product has import restrictions for this shipping country.
	 * If at least one restricted product is included in the cart,
	 * the end customer will not be able to place the order with Global-e until the product
	 * is either removed from the Cart or a shipping country with no restrictions for this product is selected.
	 * @var bool $IsRestricted
	 * @access public
	 */
	public $IsRestricted;

	/**
	 * Textual definition of the restriction (will be empty if IsRestricted is FALSE)
	 * @var string $RestrictionMessage
	 * @access public
	 */
	public $RestrictionMessage;

	/**
	 * TRUE if this product is not supported for shipping to this country by Global-e.
	 * Adding this product to the cart must be disabled completely.
	 * @var bool $IsForbidden
	 * @access public
	 */
	public $IsForbidden;

	/**
	 * Textual definition of the forbiddance (will be empty if IsForbidden is FALSE)
	 * @var string $ForbiddanceMessage
	 * @access public
	 */
	public $ForbiddanceMessage;

	/**
	 * Indicates if product restrictions and other data contained in this object have been verified by Global-e
	 * (i.e. some algorithmic approximations may be used by the system until manually resolved by Global-e personnel)
	 * @var bool $IsVerified
	 * @access public
	 */
	public $IsVerified;

	/**
	 * Time-to-live interval (in seconds) before this object must be refreshed from the Global-e server.
	 * This property overwrites “max-age” header returned by any method call which returns ProductCountry object.
	 * @var int $TTL
	 * @access public
	 */
	public $TTL;


}