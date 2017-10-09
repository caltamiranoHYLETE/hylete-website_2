<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\SDK;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Customer;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\Request;
use GlobalE\SDK\API\Common as CommonAPI;

/**
 * Class CheckoutTest
 * @package GlobalE\Test\SDK\Models
 */
class CheckoutTest  extends \PHPUnit_Framework_TestCase {

    /**
     *
     */
    public function testBuildApiParamsForSendCart(){

        // Set cart token to session
        $_SESSION['GlobalE_CartToken'] = 'cart token from session';

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
        $sendCartRequest = $this->BuildSendCartRequest();
        $checkoutMock = new CheckoutMock();
        $checkoutMock->setMethodReturns(
            array(
                'getCountryCoefficients' => $this->getCountryCoefficients(),
                'getCultureCode'         => 'en-GB',
                'getCountryModel'        => $CountryModelMock
            )
        );

        $actual = $checkoutMock->buildApiParamsForSendCart($sendCartRequest);
        $expected = $this->expectedApiParams();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    private function CustomerInfo(){
        return array("CountryISO" => "IL",
                     "CurrencyCode" => "ILS",
                     "CultureCode" => "en-GB");
    }

    /**
     * @return mixed
     */
    private function BuildSendCartRequest(){
        $sendCartRequest = Models\Json::decode($this->sendCartRequestJson(),true);

        $shippingDetails = new CommonAPI\Address();
        $billingDetails = new CommonAPI\Address();
        $shippingOptionsList = new CommonAPI\ShippingOption();
        $Product = new Request\Product();

        foreach ($sendCartRequest['shippingDetails'] as $key => $value) {
            $shippingDetails->$key = $value;
        }
        foreach ($sendCartRequest['billingDetails'] as $key => $value) {
            $billingDetails->$key = $value;
        }
        foreach ($sendCartRequest['shippingOptionsList'] as $key => $value) {
            $shippingOptionsList->$key = $value;
        }
        foreach ($sendCartRequest['ProductsList'][0] as $key => $value) {
            $Product->$key = $value;
        }

        $sendCartRequestObj = new Request\SendCart();
        $sendCartRequestObj->setShippingDetails($shippingDetails);
        $sendCartRequestObj->setBillingDetails($billingDetails);
        $sendCartRequestObj->setShippingOptionsList($shippingOptionsList);
        $sendCartRequestObj->setProductsList(array($Product));
        $sendCartRequestObj->setOriginalCurrencyCode($sendCartRequest['originalCurrencyCode']);
        $sendCartRequestObj->setMerchantCartToken($sendCartRequest['merchantCartToken']);
        $sendCartRequestObj->setMerchantCartHash($sendCartRequest['merchantCartHash']);
        $sendCartRequestObj->setDoNotChargeVAT($sendCartRequest['doNotChargeVAT']);

        return $sendCartRequestObj;
    }

    /**
     * @return string
     */
    private function sendCartRequestJson(){
        return '
                {
                  "shippingDetails": {
                    "UserId": "45787",
                    "UserIdNumber": "",
                    "UserIdNumberType":
                    {
                      "UserIdNumberTypeId": "",
                      "Name": ""
                    },
                    "FirstName": "Kirill",
                    "LastName": "Chud",
                    "Email": "asda@asda.com",
                    "Phone1": "0000000000",
                    "Address1": "US",
                    "City": "San-Francisco",
                    "StateOrProvince": "California",
                    "Zip": "94115",
                    "CountryCode": "US"
                  },
                  "billingDetails": {
                    "UserId": "45787",
                    "UserIdNumberType":
                    {
                      "UserIdNumberTypeId": "",
                      "Name": ""
                    },
                    "FirstName": "Kirill",
                    "LastName": "Chud",
                    "Email": "asda@asda.com",
                    "Phone1": "0000000000",
                    "Address1": "US",
                    "City": "San-Francisco",
                    "StateOrProvince": "California",
                    "Zip": "94115",
                    "CountryCode": "US"
                  },
                  "merchantCartToken": "085666e3569dc616806760825a30c2ec",
                  "originalCurrencyCode": "GBP",
                  "preferedCultureCode": "en-GB",
                  "shippingOptionsList": {
                    "Carrier": "globaleintegration",
                    "CarrierTitle": "globaleintegration",
                    "CarrierName": "globaleintegration",
                    "Code": "globaleintegration_standard",
                    "Method": "standard",
                    "MethodTitle": "Standard",
                    "MethodDescription": "",
                    "Price": 0
                  },
                  "merchantCartHash": "085666e3569dc616806760825a30c2ec",
                  "doNotChargeVAT": 0,
                  "ProductsList": [
                    {
                      "ProductCode": "LR023964.C8",
                      "Name": "LR023964",
                      "OriginCountryCode": "GB",
                      "Weight": "3.87",
                      "Length": "320.00",
                      "Categories": null,
                      "ListPrice": 389.27,
                      "OriginalListPrice": 299.87,
                      "SalePrice": 389.27,
                      "OriginalSalePrice": 299.87,
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
                  ]
                }';
    }

    /**
     * @return Common\ApiParams
     */
    private function expectedApiParams(){
        // This address will be used for expected shipping and billing details
        $address = new CommonAPI\Address();
        $address->setUserId('45787');
        $address->setUserIdNumberType(array ('UserIdNumberTypeId' => '', 'Name' => ''));
        $address->setFirstName('Kirill');
        $address->setLastName('Chud');
        $address->setPhone1('0000000000');
        $address->setEmail('asda@asda.com');
        $address->setAddress1('US');
        $address->setCity('San-Francisco');
        $address->setStateOrProvince('California');
        $address->setZip('94115');
        $address->setCountryCode('US');

        // Setting expected shipping option
        $shippingOption = new CommonAPI\ShippingOption();
        $shippingOption->setCarrier('globaleintegration');
        $shippingOption->setCarrierTitle('globaleintegration');
        $shippingOption->setCarrierName('globaleintegration');
        $shippingOption->setCode('globaleintegration_standard');
        $shippingOption->setMethod('standard');
        $shippingOption->setMethodTitle('Standard');
        $shippingOption->setPrice(0);

        // Setting expected product
        $product = new Request\Product();
        $product->setProductCode('LR023964.C8');
        $product->setName('LR023964');
        $product->setWeight('3.87');
        $product->setLength('320.00');
        $product->setOriginCountryCode('GB');
        $product->setListPrice(389.27);
        $product->setOriginalListPrice(299.87);
        $product->setSalePrice(389.27);
        $product->setOriginalSalePrice(299.87);
        $product->setVATRateType(array ('VATRateTypeCode' => '2', 'Name' => 'VATworld', 'Rate' => '20.000'));
        $product->setLocalVATRateType(array ('VATRateTypeCode' => '2', 'Name' => 'VATworld', 'Rate' => '20.000'));
        $product->setOrderedQuantity(1);

        // Setting the expected ApiParams
        $apiParams = new Common\ApiParams();
        $apiParams->setUri(array (
            'clientIP' => '212.143.40.246',
            'currencyCode' => 'ILS',
            'cultureCode' => 'en-GB',
            'countryCode' => 'IL',
            'originalCurrencyCode' => 'GBP',
            'preferedCultureCode' => 'en-GB',
            'inputDataCultureCode' => 'en-GB',
            'priceCoefficientRate' => 1,
            'roundingRuleId' => 29,
            'includeVAT' => 0,
            'shippingDetails' => $address,
            'billingDetails' => $address,
            'merchantCartToken' => '085666e3569dc616806760825a30c2ec',
            'shippingOptionsList' => $shippingOption,
            'merchantCartHash' => '085666e3569dc616806760825a30c2ec',
            'doNotChargeVAT' => 0,
            'cartToken' => 'cart token from session',
        ));
        $apiParams->setBody(array($product));

        return $apiParams;
    }

    /**
     * @return \stdClass
     */
    private function getCountryCoefficients(){
        $countryCoefficients = new \stdClass();
        $countryCoefficients->Rate = 1;
        $countryCoefficients->IncludeVAT = 0;
        return $countryCoefficients;
    }

    private function fetchCountryCoefficients(){
        return json_decode('{"Rate":1,"IncludeVAT":0}');
    }
}