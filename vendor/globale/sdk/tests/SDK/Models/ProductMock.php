<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\SDK;
use GlobalE\Test\MockTrait;
use GlobalE\SDK\Models\Common\Request;

class ProductMock extends Models\Product
{
	use MockTrait;

	public function __construct(array $Products, $PriceIncludesVAT = false)
	{
		$SDK = new SDK();
		$this->setCustomer();
		$this->setProducts($Products);
		$this->setPriceIncludesVAT($PriceIncludesVAT);
		$this->setCalculationModel(new CalculationMock());
		$CountryModel = CountryMock::getSingleton();
		$CountryModel->initCountries();
		$this->setCountryModel($CountryModel);
		$this->setCurrencyModel(CurrencyMock::getSingleton());

	}

	
	private function setCustomer(){
		$customerDetails = new Common\CustomerInfo('DE','EUR' ,'de' );
		/**@var $customer Models\Customer */
		$customer = Models\Customer::getSingleton();
		$customer->setInfo($customerDetails)->setIp();

	}

	public function initCurrencyRules()
	{
		parent::initCurrencyRules();
	}

	public function initCountryRules()
	{
		parent::initCountryRules();
	}

	public function calculateProductVat(Request\ProductRequestData $Product)
	{
		return parent::calculateProductVat($Product);
	}

	public function getCachedProductCountry(array &$NonCachedProductsList)
	{
		return parent::getCachedProductCountry($NonCachedProductsList);
	}


}