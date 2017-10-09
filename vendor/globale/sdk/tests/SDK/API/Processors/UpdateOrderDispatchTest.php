<?php
namespace GlobalE\Test\SDK\API\Processors;

use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\Request;

/**
 * Class UpdateOrderDispatchTest
 * @package GlobalE\Test\SDK\API\Processors
 */
class UpdateOrderDispatchTest extends \PHPUnit_Framework_TestCase {

    /**
     *
     */
    public function testFormatParameters(){
        
        $ApiParams = $this->createApiParams();
        $UpdateOrderStatusMock = new UpdateOrderDispatchMock($ApiParams);

        $expected = 'merchantGUID='.Core\Settings::get('MerchantGUID').'&parcelsList='.urlencode('{"ParcelCode":"1"}').'&parcelsList='.urlencode('{"ParcelCode":"2"}');
        $actual = $UpdateOrderStatusMock->formatParameters($ApiParams->getUri());

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return Common\ApiParams
     */
    private function createApiParams(){

        $Parcel1 = new Request\Parcel();
        $Parcel1->setParcelCode('1');

        $Parcel2 = new Request\Parcel();
        $Parcel2->setParcelCode('2');

        $Uri = array(
            'parcelsList' => array($Parcel1,$Parcel2)
        );

        $params = new Common\ApiParams();
        $params->setUri($Uri);
        return $params;
    }
}