<?php
namespace GlobalE\Test\SDK\API\Processors;

use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common;

class GetOrdersVATInvoiceTest extends \PHPUnit_Framework_TestCase {

    public function testFormatParameters(){
        
        $ApiParams = $this->createApiParams();
        $GetOrdersVatInvoiceMock = new GetOrdersVATInvoiceMock($ApiParams);

        $expected = 'merchantGUID='.Core\Settings::get('MerchantGUID').'&orderId=7&orderId=1&orderId=4&orderId=5';
        $actual = $GetOrdersVatInvoiceMock->formatParameters($ApiParams->getUri());

        $this->assertEquals($expected, $actual);
    }

    private function createApiParams(){
        $Uri = array(
            'orderId' => array('7','1','4','5')
        );

        $params = new Common\ApiParams();
        $params->setUri($Uri);
        return $params;
    }
}