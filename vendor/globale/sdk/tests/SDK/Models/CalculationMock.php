<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\Models\Calculation;
use GlobalE\Test\MockTrait;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\ItemDataResponseInterface;

class CalculationMock extends Calculation {
	use MockTrait;

	public function roundPrice($Amount)
	{
		return parent::roundPrice($Amount);
	}

	public function formatPrice($Amount)
	{
		return parent::formatPrice($Amount);
	}

	public function convertPrice($Amount)
	{
		return parent::convertPrice($Amount);
	}

	public function convertToOriginalPrice($Amount)
	{
		return parent::convertToOriginalPrice($Amount);
	}

	public function applyPriceCoefficients($Amount)
	{
		return parent::applyPriceCoefficients($Amount);
	}

	public function addVat($Amount, $Rate)
	{
		return parent::addVat($Amount, $Rate);
	}

	public function removeVat($Amount, $Rate)
	{
		return parent::removeVat($Amount, $Rate);
	}

	public function calculateItemTax(Common\Request\ItemDataRequestInterface $PriceItem, $OriginalPrice, $PriceIncludesVAT)
	{
		return parent::calculateItemTax($PriceItem, $OriginalPrice, $PriceIncludesVAT);
	}

	public function calculateRawPrice(Common\Request\RawPriceRequestData $priceRequestData, $OriginalPrice, $PriceIncludesVAT, $UseRounding, $IsDiscount)
	{
		if ($this->isMethodReturnExist(__FUNCTION__)) {
			return $this->methodReturn(__FUNCTION__);
		}
		return parent::calculateRawPrice($priceRequestData,$OriginalPrice, $PriceIncludesVAT, $UseRounding, $IsDiscount);
	}

	public function calculatePrice(Common\Request\ProductRequestData $Product, $OriginalPrice, $PriceIncludesVAT)
	{
		if ($this->isMethodReturnExist(__FUNCTION__)) {
			return $this->methodReturn(__FUNCTION__);
		}
		return parent::calculatePrice($Product,$OriginalPrice, $PriceIncludesVAT);
	}

	public function calculateDataPrice(Common\Request\ItemDataRequestInterface $PriceRequestData, $OriginalPrice, $PriceIncludesVAT, $UseRounding, $IsDiscount)
	{
		if ($this->isMethodReturnExist(__FUNCTION__)) {
			return round($OriginalPrice *1.6 ,2) ;
		}
		return parent::calculateDataPrice($PriceRequestData, $OriginalPrice, $PriceIncludesVAT, $UseRounding, $IsDiscount);
	}

	public function calculateDataPrices(Common\Request\ItemDataRequestInterface $PriceRequestData, ItemDataResponseInterface $PriceResultData, $PriceIncludesVAT, $UseRounding = false, $IsDiscount = false)
	{
		parent::calculateDataPrices($PriceRequestData, $PriceResultData, $PriceIncludesVAT, $UseRounding, $IsDiscount);
	}

}