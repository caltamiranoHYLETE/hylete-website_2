<?php
namespace GlobalE\SDK\Models\Common;

/**
 * Interface ResponseDataInterface
 * @package GlobalE\SDK\Models\Common
 *
 * @method getOriginalListPrice()
 * @method getListPrice()
 * @method getOriginalSalePrice()
 * @method getSalePrice()
 * @method getSalePriceBeforeRounding()
 */
interface ItemDataResponseInterface {

	public function setOriginalListPrice($OriginalListPrice);

	public function setListPrice($ListPrice);

	public function setOriginalSalePrice($OriginalSalePrice);

	public function setSalePrice($SalePrice);

	public function setSalePriceBeforeRounding($SalePriceBeforeRounding);


}