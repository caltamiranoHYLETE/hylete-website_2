<?php
namespace GlobalE\Test\SDK\Controllers;

use GlobalE\SDK\Controllers;
use GlobalE\Test\MockTrait;

class BrowsingMock extends Controllers\Browsing{
    use MockTrait;

    /**
     * @return \GlobalE\SDK\Models\Response
     * @throws \Exception
     */
    public function loadClientSDK(){
        return parent::LoadClientSDK();
    }

    /**
     * @param null $vatRate
     * @param null $baseCurrency
     * @param null $baseCountry
     * @param null $baseCulture
     * @return \GlobalE\SDK\Models\Response
     */
    public function Initialize($vatRate = null, $baseCurrency = null, $baseCountry = null, $baseCulture = null){
        return parent::Initialize($vatRate, $baseCurrency, $baseCountry, $baseCulture);
    }

    /**
     * @return \GlobalE\SDK\Models\Response\Data
     * @throws \Exception
     */
    public function GetCustomerInformation(){
        return parent::GetCustomerInformation();
    }

    /**
     * @return \GlobalE\SDK\Models\Response\Data
     */
    public function GetCountries(){
        return parent::GetCountries();
    }

    /**
     * @return \GlobalE\SDK\Models\Response\Data
     */
    public function GetCurrencies(){
        return parent::GetCurrencies();
    }

    /**
     * @return \GlobalE\SDK\Models\Currency
     */
    public function IsCountryOperatedByGlobale($countryCode){
        return parent::IsCountryOperatedByGlobale($countryCode);
    }

    /**
     * @return \GlobalE\SDK\Models\Response\Data
     */
    public function IsUserSupportedByGlobale(){
        return parent::IsUserSupportedByGlobale();
    }

    /**
     * @return \GlobalE\SDK\Models\Response\Data
     */
    public function OnPageLoad(){
        return parent::OnPageLoad();
    }
}