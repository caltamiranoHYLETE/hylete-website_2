<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\Models;
use GlobalE\Test\MockTrait;

class CurrencyMock extends Models\Currency {
    use MockTrait;

    public function setCurrencies($Currencies) {
        parent::setCurrencies($Currencies);
    }

}