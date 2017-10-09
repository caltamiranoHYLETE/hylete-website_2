<?php
namespace GlobalE\SDK\Models\Common;

use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common;

/**
 * Class ProductResponseData
 * @package GlobalE\SDK\Models\Common
 * @method getProductCode()
 * @method $this setProductCode($ProductCode)
 * @method getListPrice()
 * @method getOriginalListPrice()
 * @method getSalePrice()
 * @method getOriginalSalePrice()
 * @method getMarkedAsFixedPrice()
 * @method getVATRateType()
 * @method getLocalVATRateType()
 * @method getSalePriceBeforeRounding()
 * @method $this setMarkedAsFixedPrice($MarkedAsFixedPrice)
 * @method $this setVATRateType($VATRateType)
 * @method $this setLocalVATRateType($LocalVATRateType)
 * @method getIsForbidden()
 * @method $this setIsForbidden($IsForbidden)
 * @method getIsRestricted()
 * @method $this setIsRestricted($IsRestricted)
 * @method getRestrictionMessage()
 * @method $this setRestrictionMessage($RestrictionMessage)
 */
class ProductResponseData extends Common implements ItemDataResponseInterface {

	/**
	 * SKU code used to identify the product on the Merchant’s site
	 * @var string
	 */
	public $ProductCode;


	/**
	 * Original Price Converted according to system algorithm
	 * @var float
	 */
	public $ListPrice;


	/**
	 * Original price of the product
	 * @var float
	 */
	public $OriginalListPrice;

	/**
	 * Final price to display to the customer
	 * @var float
	 */
	public $SalePrice;

	/**
	 * @var $SalePriceBeforeRounding - Final price before rounding
	 */
	public $SalePriceBeforeRounding;

	/**
	 * Price Displayed to customer in Original Currency
	 * @var float
	 */
	public $OriginalSalePrice;


	/**
	 * True in the case of fixed price
	 * @var bool
	 */
	public $MarkedAsFixedPrice = false;

	/**
	 * Forbidden flag
	 * Indicates whether this product cannot be shipped to the customer country
	 * @var bool

	 */
	public $IsForbidden;

	/**
	 * Restricted flag
	 * Indicates whether this product is restricted
	 * @var bool

	 */
	public $IsRestricted;

	/**
	 * Restricted message
	 * If the product is forbidden or restricted, and this message is configured in Global-e system
	 * @var string

	 */
	public $RestrictionMessage;

	/**
	 * Calculated Global-e vat rate for the item
	 * @var VatRateType | null
	 */
	public $VATRateType;

	/**
	 * Calculated local vat rate for the item
	 * @var VatRateType | null
	 **/
	public $LocalVATRateType;


	/**
	 * @param float $ListPrice
	 * @return ProductResponseData
	 */
	public function setListPrice($ListPrice)
	{
		$this->ListPrice = $ListPrice;
		return $this;
	}

	/**
	 * @param float $OriginalListPrice
	 * @return ProductResponseData
	 */
	public function setOriginalListPrice($OriginalListPrice)
	{
		$this->OriginalListPrice = $OriginalListPrice;
		return $this;
	}

	/**
	 * Set the The final price to display to the customer
	 * @param float $SalePrice
	 * @return ProductResponseData

	 */
	public function setSalePrice($SalePrice)
	{
		$this->SalePrice = $SalePrice;
		return $this;
	}


	/**
	 * Set the The final price Before Rounding
	 * @param float $SalePriceBeforeRounding
	 * @return ProductResponseData

	 */
	public function setSalePriceBeforeRounding($SalePriceBeforeRounding)
	{
		$this->SalePriceBeforeRounding = $SalePriceBeforeRounding;
		return $this;
	}


	/**
	 * @param float $OriginalSalePrice
	 * @return ProductResponseData
	 */
	public function setOriginalSalePrice($OriginalSalePrice)
	{
		$this->OriginalSalePrice = $OriginalSalePrice;
		return $this;
	}

}