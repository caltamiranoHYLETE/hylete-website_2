<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\Models;
use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common;

/**
 * Class GenerateCheckout
 * @package GlobalE\Test\SDK\Models
 */
class GenerateCheckout extends \PHPUnit_Framework_TestCase {

    /**
     * @desc check given parameter by method
     */
    public function testGenerateCheckoutTokenParameter(){


        $token = 'c5068760-f662-49a7-9e49-cab98c097fd1';
        $chekcoutModel = new Models\Checkout();
        $actual = $chekcoutModel->GenerateCheckout($token);
        $expected = 'gle("ScriptsURL","' . Core\Settings::get('Frontend.BaseUrl') . '"); gle("Checkout", "' . $token . '","checkoutContainer");';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @desc check given $_SESSION parameter
     */
    public function testGenerateCheckoutTokenSessionParameter(){


        $_SESSION['GlobalE_CartToken'] = 'c5068760-f662-49a7-9e49-cab98c097fd1';
        $chekcoutModel = new Models\Checkout();
        $actual = $chekcoutModel->GenerateCheckout(null);
        $expected = 'gle("ScriptsURL","' . Core\Settings::get('Frontend.BaseUrl') . '"); gle("Checkout", "' . $_SESSION['GlobalE_CartToken'] . '","checkoutContainer");';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @desc check given $_GET parameter
     */
    public function testGenerateCheckoutTokenGetParameter(){


        $_GET['token'] = 'c5068760-f662-49a7-9e49-cab98c097fd1';
        $chekcoutModel = new Models\Checkout();
        $actual = $chekcoutModel->GenerateCheckout(null);
        $expected = 'gle("ScriptsURL","' . Core\Settings::get('Frontend.BaseUrl') . '"); gle("Checkout", "' . $_GET['token'] . '","checkoutContainer");';;
        $this->assertEquals($expected, $actual);
    }

    /**
     * @desc check throw exception if no parameters where given
     */
    public function testGenerateCheckoutTokenWithoutParameters(){

        try {
            $chekcoutModel = new Models\Checkout();
            $actual = $chekcoutModel->GenerateCheckout(null);

        }catch(\Exception $e){
            $expected = new \Exception('Cart token is empty, Cart token is mandatory for generate the checkout page', 0);
            $this->assertEquals($expected, $e);
        }
    }
}