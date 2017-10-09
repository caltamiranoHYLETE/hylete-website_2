<?php
namespace GlobalE\Test\SDK\Controllers;

use GlobalE\SDK\Core;

class CheckoutTest extends \PHPUnit_Framework_TestCase {

    /**
     * @desc test LoadClientSDK public interface method
     */
    public function testGenerateCheckoutPage(){

        $CheckoutMock = new CheckoutMock();
        $token = 'c5068760-f662-49a7-9e49-cab98c097fd1';
        $actual = $CheckoutMock->GenerateCheckoutPage($token)->getData();
        $expected = 'gle("Checkout", "' . $token . '","checkoutContainer");';
        $this->assertEquals($expected, $actual);
    }
}