<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common;
use GlobalE\Test\MockTrait;

class CountryMock extends Models\Country {
    use MockTrait;

    public function setCountries($Countries) {
        return parent::setCountries($Countries);
    }

    public function initCountry() {
         parent::initCountry();
    }

    public function getCountry(){
        return parent::getCountry();
    }

    public function initVatRateType(){
         parent::initVatRateType();
    }

    public function fetchCountryCoefficients(){
        if ($this->isMethodReturnExist(__FUNCTION__)) {
            return $this->methodReturn(__FUNCTION__);
        }
        return parent::fetchCountryCoefficients();
    }

    public function getRoundingRuleId(){
        if ($this->isMethodReturnExist(__FUNCTION__)) {
            return $this->methodReturn(__FUNCTION__);
        }
        return parent::getRoundingRuleId();
    }

    public function setVatRateType(Common\VatRateType $VatRateType)
    {
        return parent::setVatRateType($VatRateType); 
    }
    public function nullifyVatRateType(){
        $this->VatRateType = null;
    }
}