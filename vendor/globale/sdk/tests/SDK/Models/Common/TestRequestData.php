<?php
namespace GlobalE\Test\SDK\Models\Common;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\VatRateType;
use GlobalE\SDK\Models\Common\Request;

class TestRequestData implements Request\ItemDataRequestInterface
{

	/**
	 * @desc Get the Original List Price of the item
	 * @return float
	 * @access public
	 */
	public function getOriginalListPrice()
	{
		return 0;
	}

	/**
	 * @desc Get the Original Sale Price of the item
	 * @return float
	 */
	public function getOriginalSalePrice()
	{
		return 0;
	}

	/**
	 * Get the Global-e vat rate for product
	 * @return VatRateType|null
	 * @access public
	 */
	public function getVATRateType()
	{
		return null;
	}

	/**
	 * Get the calculated local vat rate for the item
	 * @return VatRateType|null
	 * @access public
	 */
	public function getLocalVATRateType()
	{
		return null;
	}

	/**
	 * @desc Get the fixed price flag
	 * @return boolean
	 */
	public function getIsFixedPrice()
	{
		return false;
	}
}