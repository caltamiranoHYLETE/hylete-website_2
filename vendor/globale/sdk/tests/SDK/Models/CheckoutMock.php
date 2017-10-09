<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\Models;
use GlobalE\Test\MockTrait;

class CheckoutMock extends Models\Checkout {
    use MockTrait;

    public function  getCultureCode(){
        if ($this->isMethodReturnExist(__FUNCTION__)) {
            return $this->methodReturn(__FUNCTION__);
        }
        return parent::getCultureCode();
    }
    
    public function getCountryModel(){
        if ($this->isMethodReturnExist(__FUNCTION__)) {
            return $this->methodReturn(__FUNCTION__);
        }
        return parent::getCountryModel();
    }

}