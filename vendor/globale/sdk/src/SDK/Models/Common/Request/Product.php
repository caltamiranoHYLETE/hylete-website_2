<?php
namespace GlobalE\SDK\Models\Common\Request;

use GlobalE\SDK\API\Common\Attribute;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\VatRateType;

/**
 * Class Product
 * @method getProductCode()
 * @method getProductGroupCode()
 * @method getName()
 * @method getNameEnglish()
 * @method getKeywords()
 * @method getURL()
 * @method getDescription()
 * @method getDescriptionEnglish()
 * @method getGenericHSCode()
 * @method getWeight()
 * @method getNetWeight()
 * @method getHeight()
 * @method getLength()
 * @method getWidth()
 * @method getVolume()
 * @method getNetVolume()
 * @method getImageURL()
 * @method getImageWidth()
 * @method getImageHeight()
 * @method getOriginCountryCode()
 * @method getListPrice()
 * @method getOriginalListPrice()
 * @method getSalePrice()
 * @method getSalePriceBeforeRounding()
 * @method getOriginalSalePrice()
 * @method getVATRateType()
 * @method getLocalVATRateType()
 * @method getBrand()
 * @method getCategories()
 * @method getOrderedQuantity()
 * @method getCartItemId()
 * @method getParentCartItemId()
 * @method getCartItemOptionId()
 * @method getIsBlockedForGlobalE()
 * @method getIsBundle()
 * @method getAttributes()
 * @method getAttributesEnglish()
 * @method getIsFixedPrice()
 * @method getIsVirtual()
 * @method getProductCodeSecondary()
 * @method getProductGroupCodeSecondary()
 * @method $this setProductCode($ProductCode)
 * @method $this setProductGroupCode($ProductGroupCode)
 * @method $this setName($Name)
 * @method $this setNameEnglish($NameEnglish)
 * @method $this setKeywords($Keywords)
 * @method $this setURL($URL)
 * @method $this setDescription($Description)
 * @method $this setDescriptionEnglish($DescriptionEnglish)
 * @method $this setGenericHSCode($GenericHSCode)
 * @method $this setWeight($Weight)
 * @method $this setNetWeight($NetWeight)
 * @method $this setHeight($Height)
 * @method $this setLength($Length)
 * @method $this setWidth($Width)
 * @method $this setVolume($Volume)
 * @method $this setNetVolume($NetVolume)
 * @method $this setImageURL($ImageURL)
 * @method $this setImageWidth($ImageWidth)
 * @method $this setImageHeight($ImageHeight)
 * @method $this setOriginCountryCode($OriginCountryCode)
 * @method $this setListPrice($ListPrice)
 * @method $this setOriginalListPrice($OriginalListPrice)
 * @method $this setSalePrice($SalePrice)
 * @method $this setSalePriceBeforeRounding($SalePriceBeforeRounding)
 * @method $this setOriginalSalePrice($OriginalSalePrice)
 * @method $this setVATRateType($VATRateType)
 * @method $this setLocalVATRateType($LocalVATRateType)
 * @method $this setBrand($Brand)
 * @method $this setCategories($Categories)
 * @method $this setOrderedQuantity($OrderedQuantity)
 * @method $this setCartItemId($CartItemId)
 * @method $this setParentCartItemId($ParentCartItemId)
 * @method $this setCartItemOptionId($CartItemOptionId)
 * @method $this setIsBlockedForGlobalE($IsBlockedForGlobalE)
 * @method $this setIsBundle($IsBundle)
 * @method $this setAttributes($Attributes)
 * @method $this setAttributesEnglish($AttributesEnglish)
 * @method $this setIsFixedPrice($IsFixedPrice)
 * @method $this setIsVirtual($IsVirtual)
 * @method $this setProductCodeSecondary($ProductCodeSecondary)
 * @method $this setProductGroupCodeSecondary($ProductGroupCodeSecondary)
 * @package GlobalE\SDK\Models\Common
 */
class Product extends Common {

    /**
     * SKU code used to identify the product on the Merchant’s site
     * @var string $ProductCode
     */
    public $ProductCode;

    /**
     * Product’s group code on the Merchant’s site
     * @var string $ProductGroupCode
     */
    public $ProductGroupCode;

    /**
     * Name of the Product
     * @var string $Name
     */
    public $Name;

	/**
	 * Name of the Product in English
	 * @var string $NameEnglish
	 */
	public $NameEnglish;

    /**
     * Product’s keywords
     * @var string $Keywords
     */
    public $Keywords;

    /**
     * Product’s information page URL
     * @var string $URL
     */
    public $URL;

    /**
     * Description of the Product
     * @var string $Description
     */
    public $Description;

	/**
	 * Description of the Product in English
	 * @var string $DescriptionEnglish
	 */
	public $DescriptionEnglish;

    /**
     * GenericHSCode may help for mapping the product
     *       for duties and taxes calculation purposes
     * @var string $GenericHSCode
     */
    public $GenericHSCode;

    /**
     * Product weight, default unit of weight measure
     * @var float $Weight
     */
    public $Weight;

    /**
     * Product net weight, default unit of weight measure
     * @var float $NetWeight
     */
    public $NetWeight;

    /**
     * Product height, default unit of height measure
     * @var float $Height
     */
    public $Height;

    /**
     * Product length, default unit of length measure
     * @var float $Height
     */
    public $Length;

    /**
     * Product Width, default unit of width measure
     * @var float $Width
     */
    public $Width;

    /**
     * Product volume, default unit of volume measure
     * @var float $Volume
     */
    public $Volume;

    /**
     * Product net volume, default unit of volume measure
     * @var float $NetVolume
     */
    public $NetVolume;

    /**
     * Product image URL path
     * @var string $ImageURL
     */
    public $ImageURL;

    /**
     * Product image width in pixels
     * @var int $ImageWidth
     */
    public $ImageWidth;

    /**
     * Product image height in pixels
     * @var int $ImageHeight
     */
    public $ImageHeight;

    /**
     * Origin country code of the product
     * @var string $OriginCountryCode
     */
    public $OriginCountryCode;

    /**
     * Product list price (before any discounts) as displayed to the customer
     * @var float $ListPrice
     */
    public $ListPrice;

    /**
     * Product list price (before any discounts)
     *       in original Merchant’s currency including the local VAT
     * @var float $OriginalListPrice
     */
    public $OriginalListPrice;

    /**
     * Product sale price
     * @var float $SalePrice
     */
    public $SalePrice;

    /**
     * Product sale price, before rounding rules have been applied
     * @var float $SalePriceBeforeRounding
     */
    public $SalePriceBeforeRounding;

    /**
     * Product sale price in original Merchant’s currency
     *       including the local VAT
     * @var float $OriginalSalePrice
     */
    public $OriginalSalePrice;

    /**
     * Product vat rate type
     * @var VATRateType $VATRateType
     */
    public $VATRateType;

    /**
     * Product vat rate type .
     *       Must be specified if UseCountryVAT for the current country is TRUE
     * @var VATRateType $LocalVATRateType
     */
    public $LocalVATRateType;

    /**
     * Product brand
     * @var string $Brand
     */
    public $Brand;

    /**
     * Product categories, array of Category classes
     * @var array $Categories
     */
    public $Categories;

    /**
     * Product quantity
     * @var int $OrderedQuantity
     */
    public $OrderedQuantity;

    /**
     * Identifier of the cart (Quote) item
     * @var int $CartItemId
     */
    public $CartItemId;

    /**
     * Identifier of the current item’s parent cart (Quote) item
     * Mostly for Configurable products, Bundle products
     * @var int $ParentCartItemId
     */
    public $ParentCartItemId;

    /**
     * Identifier of the child cart (Quote) item “option”.
     * Mostly for Configurable products, Bundle products
     * @var int $ParentCartItemId
     */
    public $CartItemOptionId;

    /**
     * Setting flag indicates if product is not available for international shipping
     * @var bool $IsBlockedForGlobalE
     */
    public $IsBlockedForGlobalE;

    /**
     * Setting flag indicates if product type is bundle product.
     * @var bool $IsBundle
     */
    public $IsBundle;

    /**
     * Product’s custom attributes (such as Color, Size, etc.)
     * @var Attribute[] $Attributes
     */
    public $Attributes;

	/**
	 * Product’s custom attributes (such as Color, Size, etc.) on English
	 * @var Attribute[] $AttributesEnglish
	 */
	public $AttributesEnglish;


    /**
     * Setting flag indicates that the product price is fixed by the Merchant.
     * @var bool $IsFixedPrice
     */
    public $IsFixedPrice;

    /**
     * Setting flag indicates if product type is a virtual product.
     * @var bool $IsVirtual
     */
    public $IsVirtual;

    /**
     * Secondary code that may be used to refer to the product
     * @var string $ProductCodeSecondary
     */
    public $ProductCodeSecondary;

    /**
     * Secondary code that may be used to refer to the group of products
     * @var string $ProductGroupCodeSecondary
     */
    public $ProductGroupCodeSecondary;


}