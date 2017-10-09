<?php
namespace GlobalE\SDK\Models\Common\Request;

use GlobalE\SDK\Models\Common\VatRateType;

/**
 * Interface RequestDataInterface
 * @package GlobalE\SDK\Models\Common
 */
interface ItemDataRequestInterface {

	/**
	 * @desc Get the Original List Price of the item
	 * @return float
	 * @access public
	 */
	public function getOriginalListPrice();


	/**
	 * @desc Get the Original Sale Price of the item
	 * @return float
	 */
	public function getOriginalSalePrice();

	/**
	 * Get the Global-e vat rate for product
	 * @return VatRateType|null
	 * @access public
	 */
	public function getVATRateType();

	/**
	 * Get the calculated local vat rate for the item
	 * @return VatRateType|null
	 * @access public
	 */
	public function getLocalVATRateType();


	/**
	 * @desc Get the fixed price flag
	 * @return boolean
	 */
	public function getIsFixedPrice();

}