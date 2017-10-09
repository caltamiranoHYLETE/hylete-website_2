<?php
namespace GlobalE\SDK\Models\Common\Request;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\API;
use GlobalE\SDK\Models\Common\Request;

/**
 * Class SendCart
 * @method API\Common\Address getShippingDetails()
 * @method API\Common\Address getBillingDetails()
 * @method getShippingOptionsList()
 * @method getProductsList()
 * @method getOriginalCurrencyCode()
 * @method getMerchantCartToken()
 * @method getMerchantCartHash()
 * @method getDoNotChargeVAT()
 * @method getCartId()
 * @method getUrlParameters()
 * @method getPreferedCultureCode()
 * @method getIsFreeShipping()
 * @method getDiscountsList()
 * @method getVatRegistrationNumber()
 * @method getFreeShippingCouponCode()
 * @method $this setShippingDetails($ShippingDetails)
 * @method $this setBillingDetails($BillingDetails)
 * @method $this setShippingOptionsList($ShippingOptionsList)
 * @method $this setProductsList($ProductsList)
 * @method $this setOriginalCurrencyCode($OriginalCurrencyCode)
 * @method $this setMerchantCartToken($MerchantCartToken)
 * @method $this setMerchantCartHash($MerchantCartHash)
 * @method $this setDoNotChargeVAT($DoNotChargeVAT)
 * @method $this setVatRegistrationNumber($VatRegistrationNumber)
 * @method $this setIsFreeShipping($IsFreeShipping)
 * @method $this setFreeShippingCouponCode($FreeShippingCouponCode)
 * @method $this setDiscountsList($DiscountsList)
 * @method $this setCartId($CartId)
 * @method $this setUrlParameters($UrlParameters)
 * @method $this setPreferedCultureCode($CultureCode)
 *
 * @package GlobalE\SDK\Models\Common\Request
 */
class SendCart extends Common {

    /**
     * @var API\Common\Address
     */
    public $ShippingDetails;

    /**
     * @var API\Common\Address
     */
    public $BillingDetails;

    /**
     * @var API\Common\ShippingOption
     */
    public $ShippingOptionsList;

    /**
     * @var Request\Product[]
     */
    public $ProductsList;

    /**
     * @var string
     */
    public $OriginalCurrencyCode;

    /**
     * @var string
     */
    public $MerchantCartToken;

    /**
     * @var string
     */
    public $MerchantCartHash;

    /**
     * @var boolean
     */
    public $DoNotChargeVAT;

    /**
     * @var int
     */
    public $CartId;

    /**
     * @var string
     */
    public $VatRegistrationNumber;

    /**
     * @var bool
     */
    public $IsFreeShipping;

    /**
     * @var string
     */
    public $FreeShippingCouponCode;

    /**
     * @var array
     */
    public $DiscountsList;

    /**
     * @var array
     */
    public $UrlParameters;

    /**
     * @var string
     */
    public $PreferedCultureCode;

}