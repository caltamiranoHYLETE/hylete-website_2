<?php
namespace GlobalE\SDK\Models\Common\Request;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\VatRateType;

/**
 * Class ProductRequestData
 * @method getProductCode()
 * @method setProductCode($ProductCode)
 * @method setOriginalListPrice($OriginalListPrice)
 * @method setOriginalSalePrice($OriginalSalePrice)
 * @method setIsFixedPrice($IsFixedPrice)
 * @method setVATRateType($VATRateType)
 * @method setLocalVATRateType($LocalVATRateType)
 * @package GlobalE\SDK\Models\Common
 */
class ProductRequestData extends Common implements ItemDataRequestInterface {
	
	/**
	 * SKU code used to identify the product on the Merchant’s site
	 * @var string $ProductCode
	 * @access public
	 */
	public $ProductCode;

	/**
	 * Original List Price of the product
	 * @var float
	 * @access public
	 */
	public $OriginalListPrice;

	/**
	 * Original Sale Price of the product
	 * @var float
	 * @access public
	 */
	public $OriginalSalePrice;

	/**
	 * Fixed price flag
	 * Only when supported, if set to true, then no calculation will take place
	 * $Price value will be return as the final price of this product
	 * @var bool
	 * @access public
	 */
	public $IsFixedPrice;


	/**
	 * Global-e vat rate for product
	 * @var VatRateType | null
	 * @access public
	 */
	public $VATRateType;

	/**
	 * Local vat rate type for product
	 * @var VatRateType | null
	 * @access public
	 */
	public $LocalVATRateType;

	/**
	 * Get the original price of the product
	 * @return float
	 * @access public
	 */
	public function getOriginalListPrice()
	{
		return $this->OriginalListPrice;
	}

	/**
	 * @return float
	 */
	public function getOriginalSalePrice()
	{
		return $this->OriginalSalePrice;
	}

	/**
	 * @desc Get the fixed price flag
	 * @return boolean
	 * @access public
	 */
	public function getIsFixedPrice()
	{
		return $this->IsFixedPrice;
	}
	

	/**
	 * Get the local vat rate for product
	 * @return VatRateType|null
	 * @access public
	 */
	public function getVATRateType()
	{
		return $this->VATRateType;
	}

	/**
	 * Get the local vat rate type for product
	 * @return VatRateType|null
	 * @access public
	 */
	public function getLocalVATRateType()
	{
		return $this->LocalVATRateType;
	}

}