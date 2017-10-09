<?php
namespace GlobalE\Test\SDK\Controllers;

use GlobalE\SDK\Controllers;
use GlobalE\Test\MockTrait;

class CheckoutMock extends Controllers\Checkout{
    use MockTrait;

    /**
     * @param null|string $token
     * @return \GlobalE\SDK\Models\Response\Data
     */
    public function GenerateCheckoutPage($token){
        return parent::GenerateCheckoutPage($token);
    }


}