<?php
namespace GlobalE\SDK\Models\Common\Request;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\VatRateType;

/**
 * Class RawPriceRequestData
 * @method setOriginalListPrice($OriginalListPrice)
 * @method setOriginalSalePrice($OriginalSalePrice)
 * @method setIsFixedPrice($IsFixedPrice)
 * @method setVATRateType($VATRateType)
 * @method setLocalVATRateType($LocalVATRateType)
 * @package GlobalE\SDK\Models\Common
 */
class RawPriceRequestData extends Common implements ItemDataRequestInterface{

	/**
	 * Original List Price value of Item
	 * @var float
	 * @access public
	 */
	public $OriginalListPrice;

	/**
	 * Original Sale Price of the Item
	 * @var float
	 * @access public
	 */
	public $OriginalSalePrice;

	/**
	 * Fixed price flag
	 * Only when supported, if set to true, then no calculation will take place
	 * and this $Price value will be return as the final price 
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
	 * Local vat rate for product
	 * @var VatRateType | null
	 * @access public
	 */
	public $LocalVATRateType;
	/**
	 * Get the original Price value of Item
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
	 * Get the local vat rate for product
	 * @return VatRateType|null
	 * @access public
	 */
	public function getLocalVATRateType()
	{
		return $this->LocalVATRateType;
	}

}