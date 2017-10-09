<?php
namespace GlobalE\SDK\API\Common;
use GlobalE\SDK\Models\Common;

/**
 * Class Discount
 * @method getOriginalDiscountValue()
 * @method getVATRate()
 * @method getLocalVATRate()
 * @method getDiscountValue()
 * @method getCouponCode()
 * @method setOriginalDiscountValue($OriginalDiscountValue)
 * @method setVATRate($VATRate)
 * @method setLocalVATRate($LocalVATRate)
 * @method setDiscountValue($DiscountValue)
 * @method setDiscountCode($DiscountCode)
 * @method setName($Name)
 * @method setCouponCode($CouponCode)
 * @method setDescription($Description)
 * @method setDiscountType($DiscountType)
 * @package GlobalE\SDK\API\Common
 */
class Discount extends Common {

    /**
     * Discount value in original Merchant’s currency including the local VAT,
	 * before applying any price modifications.
     * @var float $OriginalDiscountValue
     */
    public $OriginalDiscountValue;


	/**
	 * Discount value as displayed to the customer, after applying country coefficient,
	 * FX conversion and IncludeVAT handling.
	 * @var float $DiscountValue
	 */
	public $DiscountValue;

    /**
     * VAT rate applied to this discount
     * @var float $VATRate
     */
    public $VATRate;

	/**
	 * Discount name
	 * @var string
	 */
	public $Name;

	/**
	 * One of the values of eDiscountTypes enumeration denoting a type of a discount
	 * @var eDiscountTypes
	 * @TODO Implement !!!
	 */
	public $DiscountType;

	/**
	 * Discount code used to identify the discount on the Merchant’s site.
	 * @var string
	 */
	public $DiscountCode;

    /**
     * Local VAT rate.
     * @var float $LocalVATRate
     */
    public $LocalVATRate;

    /**
     * Merchant’s coupon code used for this discount (applicable to coupon-based discounts only)
     * @var string
     */
    public $CouponCode;

	/**
	 * Discount textual description
	 * @var string
	 */
	public $Description;

	/**
	 * Identifier of the product cart item related to this discount on the Merchant’s site.
	 * @var string
	 */
	public $ProductCartItemId;

	/**
	 * Code used on the Merchant’s site to identify the Loyalty Voucher that this discount is based on
	 * @var string
	 */
	public $LoyaltyVoucherCode;


}