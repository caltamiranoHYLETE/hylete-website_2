<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\SDK;
use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Customer;
use GlobalE\SDK\Models\Common\Request;

/**
 * Class AdminTest
 * @package GlobalE\Test\SDK\Models
 */
class AdminTest extends \PHPUnit_Framework_TestCase {

    /**
     *
     */
    public function testBuildApiParamsForSaveProductsList(){

        // Sets base info in SDK
        SDK::$baseInfo = new Common\BaseInfo($this->CustomerInfo()['CountryISO'],
                                             $this->CustomerInfo()['CurrencyCode'],
                                             $this->CustomerInfo()['CultureCode']);

        // Sets customer info
        $customer_info = new Common\CustomerInfo($this->CustomerInfo()['CountryISO'],
                                                 $this->CustomerInfo()['CurrencyCode'],
                                                 $this->CustomerInfo()['CultureCode']);
        Customer::getSingleton()->setInfo($customer_info);

        // Sets ip for customer
        Customer::getSingleton()->setIp();

        // Set mocks
        $CountryModelMock = CountryMock::getSingleton();
        $CountryModelMock->setMethodReturns(
            array(
                'fetchCountryCoefficients' => $this->fetchCountryCoefficients(),
                'getRoundingRuleId'        => 29
            )
        );
        $Products = $this->getProducts();
        $AdminMock = new AdminMock();
        $AdminMock->setMethodReturns(
            array(
                'getCultureCode'         => 'en-GB',
                'getCountryModel'        => $CountryModelMock
            )
        );

        $actual = $AdminMock->buildApiParamsForSaveProductsList($Products);
        $expected = $this->buildExpectedApiParams();
        $this->assertEquals($expected, $actual);
    }

    public function testGetOrderInvoiceHeaders(){

        $AdminMock = new AdminMock();

        $expected = array('Cache-Control: public',
                          'Content-type: application/pdf',
                          'Content-Length: 122');
        $actual = $AdminMock->getOrderInvoiceHeaders(122);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    private function CustomerInfo(){
        return array("CountryISO"   => "IL",
                     "CurrencyCode" => "ILS",
                     "CultureCode"  => "en-GB");
    }

    /**
     * @return mixed
     */
    private function fetchCountryCoefficients(){
        return json_decode('{"Rate":1,"IncludeVAT":0}');
    }

    /**
     * @return array
     */
    private function getProducts(){
        $ProductsJson = '[
                            {
                              "IsBlockedForGlobalE": false,
                              "ProductCode": "LR023964.C8",
                              "Name": "LR023964",
                              "ProductGroupCode": null,
                              "Description": null,
                              "IsBundle": false,
                              "URL": "",
                              "GenericHSCode": "",
                              "OriginCountryCode": "GB",
                              "Weight": "3.87",
                              "Width": "0.00",
                              "Height": "0.00",
                              "Length": "320.00",
                              "Volume": "",
                              "ImageURL": "",
                              "ImageHeight": "",
                              "ImageWidth": "",
                              "Categories": null,
                              "Brand": "",
                              "ListPrice": 389.28,
                              "OriginalListPrice": 299.88,
                              "SalePrice": 389.28,
                              "OriginalSalePrice": 299.88,
                              "LocalVATRateType":
                              {
                                  "VATRateTypeCode": "2",
                                "Name": "VATworld",
                                "Rate": "20.000"
                              },
                              "VATRateType":
                              {
                                  "VATRateTypeCode": "2",
                                "Name": "VATworld",
                                "Rate": "20.000"
                              },
                              "OrderedQuantity": 1
                            }
                        ]';

        $ProductsJson = json_decode($ProductsJson,true);
        $Product = new Request\Product();

        foreach ($ProductsJson[0] as $key => $value) {
            $Product->$key = $value;
        }

        $Products = array($Product);

        return $Products;
    }

    /**
     * @return Common\ApiParams
     */
    private function buildExpectedApiParams(){
        $ApiParams = new Common\ApiParams();
        $ApiParams->setUri(
            Array (
                'merchantGUID'         => Core\Settings::get('MerchantGUID'),
                'clientIP'             => '212.143.40.246',
                'cultureCode'          => 'en-GB',
                'countryCode'          => 'IL',
                'originalCurrencyCode' => 'ILS',
                'inputDataCultureCode' => 'en-GB',
                'priceCoefficientRate' => 1,
                'roundingRuleId'       => 29,
                'includeVAT'           => 0,
            )
        );

        $ApiParams->setBody($this->getProducts());

        return $ApiParams;
    }
}