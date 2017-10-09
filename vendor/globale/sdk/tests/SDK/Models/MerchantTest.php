<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\Core;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common\Response;

class MerchantTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider providerHandleOrder
     * @param $Data
     * @param $Expected
     */
    public function testHandleOrder($Data, $Expected){

        $HandleAction = new HandleActionMock();
        $MerchantModel = new Models\Merchant();

        $Actual = $MerchantModel->handleOrder($Data, $HandleAction, "Any Name");
        $this->assertEquals($Expected, $Actual);
    }

    /**
     *
     */
    public function testHandleOrderException(){

        $Data = '{"MerchantGUID": "'.Core\Settings::get('MerchantGUID').'"}';
        $HandleAction = new HandleActionMock();
        $HandleAction->setMethodReturns(
            array(
                'handleAction' => ''
            )
        );
        $MerchantModel = new Models\Merchant();

        $ResponseActual = $MerchantModel->handleOrder($Data, $HandleAction, "Any Name");
        $ResponseExpected = new Response(false, 'Exception in merchant\'s Any Name handle action. Test exception');
        $this->assertEquals($ResponseExpected, $ResponseActual);
    }

    /**
     * @return array
     */
    public function providerHandleOrder(){
        return array(
            array('{}',new Response(false, 'Wrong merchant GUID provided.')),
            array('{"MerchantGUID": "'.Core\Settings::get('MerchantGUID').'"}',new Response\Order(true, null, '1234', '1234'))
        );
    }
}