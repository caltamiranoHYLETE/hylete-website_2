<?php
namespace GlobalE\SDK\Models\Common;

use GlobalE\SDK\Models\Common;

/**
 * Class RawPriceResponseData
 * @method getListPrice()
 * @method getOriginalListPrice()
 * @method getSalePrice()
 * @method getOriginalSalePrice()
 * @method getMarkedAsFixedPrice()
 * @method getVATRateType()
 * @method $this getLocalVATRateType()
 * @method getSalePriceBeforeRounding()
 * @method $this setMarkedAsFixedPrice($MarkedAsFixedPrice)
 * @method $this setVATRateType($VATRateType)
 * @method $this setLocalVATRateType($LocalVATRateType)
 * @package GlobalE\SDK\Models\Common
 */
class RawPriceResponseData extends Common implements ItemDataResponseInterface {

	/**
	 * Original Price Converted to current currency
	 * @var float
	 */
	public $ListPrice;


	/**
	 * Original price of the item
	 * @var float
	 */
	public $OriginalListPrice;

	/**
	 * Final Price value of Item
	 * @var float
	 * @access public
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
	 * @desc True in the case of fixed price
	 * @var bool
	 */
	public $MarkedAsFixedPrice = false;

	/**
	 * Calculated Global-e vat rate for the item
	 * @var VatRateType | null
	 */
	public $VATRateType;

	/**
	 * Calculated local vat rate for the item
	 * @var VatRateType | null
	 */
	public $LocalVATRateType;
	
	/**
	 * @param float $ListPrice
	 * @return RawPriceResponseData
	 */
	public function setListPrice($ListPrice)
	{
		$this->ListPrice = $ListPrice;
		return $this;
	}

	/**
	 * @param float $OriginalListPrice
	 * @return RawPriceResponseData
	 */
	public function setOriginalListPrice($OriginalListPrice)
	{
		$this->OriginalListPrice = $OriginalListPrice;
		return $this;
	}

	/**
	 * Set the final Price value of Item
	 * @param float $SalePrice
	 * @return RawPriceResponseData
	 */
	public function setSalePrice($SalePrice)
	{
		$this->SalePrice = $SalePrice;
		return $this;
	}

	/**
	 * Set the The final price Before Rounding
	 * @param float $SalePriceBeforeRounding
	 * @return RawPriceResponseData

	 */
	public function setSalePriceBeforeRounding($SalePriceBeforeRounding)
	{
		$this->SalePriceBeforeRounding = $SalePriceBeforeRounding;
		return $this;
	}

	/**
	 * @param float $OriginalSalePrice
	 * @return RawPriceResponseData
	 */
	public function setOriginalSalePrice($OriginalSalePrice)
	{
		$this->OriginalSalePrice = $OriginalSalePrice;
		return $this;
	}
	
}