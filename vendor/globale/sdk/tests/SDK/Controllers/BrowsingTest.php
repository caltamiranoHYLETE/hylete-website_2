<?php
namespace GlobalE\Test\SDK\Controllers;

use GlobalE\SDK\Core;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\API\Processors;
use GlobalE\SDK\Controllers;
use GlobalE\SDK\Models\Common\Response;
use GlobalE\Test\SDK\Models\CurrencyMock;

class BrowsingTest extends \PHPUnit_Framework_TestCase {

    private $mock;
    public function __construct()
    {
        $this->mock = new BrowsingMock();
        // Initialize SDK
        $this->mock->OnPageLoad();
    }

    /**
     * @desc test LoadClientSDK public interface method
     */
    public function testLoadClientSDK(){

        // build javascript output
        $jsConfig = $this->_buildTestConfiguration();
        $expected = $this->_buildOutputScript($jsConfig);
        // call browsing interface method
        /**@var $actual Models\Common\Response\Data */
        $actual = $this->mock->loadClientSDK();

        $expectedTrim = preg_replace('/\s+/', ' ', trim($expected->getData()));
        $actualTrim = preg_replace('/\s+/', ' ', trim($actual->getData()));

        $this->assertEquals($expectedTrim, $actualTrim);
    }

    /**
     * @desc test LoadClientSDK public interface method
     */
    public function testGetCustomerInformation(){

        // get customer information
        /**@var $customer Models\Customer */
        $customer = Models\Customer::getSingleton();
        $customerInfo = $customer->getInfo();
        $expected = new Response\Data(true, $customerInfo);
        // call browsing interface method
        $actual = $this->mock->GetCustomerInformation();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @desc test LoadClientSDK public interface method
     */
    public function testGetCountries(){

        $country = Models\Country::getSingleton();
        /**@var $country Models\Country */
        $countries = $country->getCountries();
        $expected = new Response\Data(true, $countries);
        // call browsing interface method
        $actual = $this->mock->GetCountries();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @desc test LoadClientSDK public interface method
     */
    public function testGetCurrencies(){

        // get currency model
        $currency =  Models\Currency::getSingleton();
        $currenciesMethods =  new Response\Data(true, $currency);
        $expected = $currenciesMethods->getData()->getCurrencies();
        // call browsing interface method
        $actual = $this->mock->GetCurrencies()->getData()->getCurrencies();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @desc test GetShortSymboledAmount public interface method
     */
    public function testGetShortSymboledAmount(){

        // get currency model
        $currency =  Models\Currency::getSingleton();
        $currenciesMethods =  new Response\Data(true, $currency);
        $expected = $currenciesMethods->getData()->getShortSymboledAmount(20);

        // call browsing interface method
        $actual = $this->mock->GetCurrencies()->getData()->getShortSymboledAmount(20);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @desc test GetLongSymboledAmount public interface method
     */
    public function testGetLongSymboledAmount(){

        // get currency model
        $currency =  CurrencyMock::getSingleton();
        $currenciesMethods =  new Response\Data(true, $currency);
        $expected = $currenciesMethods->getData()->getLongSymboledAmount(20);

        // call browsing interface method
        
        $actual = $this->mock->GetCurrencies()->getData()->getLongSymboledAmount(20);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @desc test IsCountryOperatedByGlobale public interface method
     */
    public function testIsCountryOperatedByGlobale() {

        // get currency model
        /**@var $country Models\Country */
        $country = Models\Country::getSingleton();
        $isCountryOperatedByGlobale =  $country->IsCountryOperatedByGlobale('AD');
        $expected =  new Response\Data(true, $isCountryOperatedByGlobale);

        // call browsing interface method
        $actual = $this->mock->IsCountryOperatedByGlobale('AD');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @desc test IsUserSupportedByGlobale public interface method
     */
    public function testIsUserSupportedByGlobale() {

        // get currency model
        /**@var $customer Models\Customer */
        $customer = Models\Customer::getSingleton();
        /**@var $customerDetails Common\CustomerInfo */
        $customerDetails = $customer->getInfo();
        $expected = $this->mock->IsCountryOperatedByGlobale($customerDetails->getCountryISO());

        // call browsing interface method
        $actual = $this->mock->IsUserSupportedByGlobale();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @desc build configuration for testing
     * @access private
     * @return array
     */
    private function _buildTestConfiguration() {

        // get app version settings
        $ApiParams = new Common\ApiParams();
        $getAppVersion = new Processors\AppVersion($ApiParams);
        $appVersionSettings = $getAppVersion->processRequest();
        $appVersionSettings = $appVersionSettings[0];
        $ClientSettings = Core\Settings::get('AppSettings.ClientSettings');

        // get customer information in order to set the javascript configuration
        /**@var $customer Models\Customer */
        $customer = Models\Customer::getSingleton();
        $customerDetails = $customer->getInfo();

        $jsConfig = array(
            "merchantId" => Core\Settings::get('MerchantID'),
            "MerchantParameters" => stripslashes(Models\Json::encode($ClientSettings)),
            "cookieDomain" => Core\Settings::get('Cookies.Domain'),
            "webClientVersion" => $appVersionSettings->WebClientVersion,
            "merchantClientUrl" => Core\Settings::get('Frontend.BaseUrl') . Core\Settings::get('Frontend.MerchantClient'),
            "merchantFrontBaseUrl" => Core\Settings::get('Frontend.BaseUrl'),
            "countryISO" => $customerDetails->getCountryISO(),
            "cultureCode" => $customerDetails->getCultureCode(),
            "currencyCode" => $customerDetails->getCurrencyCode()
        );
        return $jsConfig;
    }

    /**
     * @desc build javascript output script by configuration
     * @access private
     * @param array $jsConfig
     * @return Response\Data
     */
    private function _buildOutputScript($jsConfig) {


        $output = "//Global-e script initializer
                           (function (w, d, u, t,o, h, m, s, l) {
                          w['globaleObject'] = o;
                           w[o] = w[o] || function () {{(w[o].q = w[o].q || []).push(arguments)}};
                           w[o].m = m,  w[o].v = h; s = d.createElement(t);
                           l = d.getElementsByTagName(t)[0];
                           s.async = true;
                           s.src = u + '?v=' + h;
                           l.parentNode.insertBefore(s, l);
                           })(window, document, '{$jsConfig['merchantClientUrl']}', 'script','gle' ,'{$jsConfig['webClientVersion']}', {$jsConfig['merchantId']} );
                           gle('ScriptsURL','{$jsConfig['merchantFrontBaseUrl']}');";
        

        if (!empty($jsConfig['cookieDomain']) && substr($jsConfig['cookieDomain'], 0, 1) !== '.') {
            $output .= "gle(\"SetCookieDomain\", {$jsConfig['cookieDomain']});";
        }
        if (!empty($jsConfig['MerchantParameters'])) {
            $output .= "
            gle(\"SetMerchantParameters\", {$jsConfig['MerchantParameters']});";
        }
        $output .= " gle('LoadWelcome', '{$jsConfig['countryISO']}', '{$jsConfig['cultureCode']}', '{$jsConfig['currencyCode']}');\r\n
            gle(\"LoadShippingSwitcher\", '{$jsConfig['countryISO']}', '{$jsConfig['cultureCode']}', '{$jsConfig['cultureCode']}',false);";
        // end profiler testing
        return new Response\Data(true,$output);
    }
}
