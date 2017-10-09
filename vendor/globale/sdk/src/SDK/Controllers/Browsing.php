<?php
namespace GlobalE\SDK\Controllers;

use GlobalE\SDK\Core;
use GlobalE\SDK\Core\Profiler;
use GlobalE\SDK;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\API\Processors;
use GlobalE\SDK\Models\Common\Response;
use GlobalE\SDK\Models\Common\Request;

/**
 * Class Browsing
 * Interface Methods
 * @package GlobalE\SDK\Controllers
 */
class Browsing extends BaseController {

    /**
     * Init client SDK
     * @return Response
     */
    protected function LoadClientSDK(){

    	$AppModel = new Models\App();

        // get app version settings and app client Settings
        $WebClientVersion = Core\Settings::get('AppVersion.WebClientVersion');
        if(empty($WebClientVersion)){
			$AppModel->initAppVersion();
			$WebClientVersion = Core\Settings::get('AppVersion.WebClientVersion');
		}

        $ClientSettings = Core\Settings::get('AppSettings.ClientSettings');
		if(empty($ClientSettings)){
			$AppModel->initAppSettings();
			$ClientSettings = Core\Settings::get('AppSettings.ClientSettings');
		}


        if(!empty($WebClientVersion) && !empty($ClientSettings)) {

            // get customer information in order to set the javascript configuration
            $Customer = Models\Customer::getSingleton();
            /**@var $Customer Models\Customer */
            $CustomerDetails = $Customer->getInfo();
            if (!empty($CustomerDetails)) {
                // throw the Client SDK to screen
                // set javascript configuration from settings and customer information
                $JsConfig = array(
                    "MerchantId" => Core\Settings::get('MerchantID'),
                    "CookieDomain" => Core\Settings::get('Cookies.Domain'),
                    "WebClientVersion" => $WebClientVersion,
                    "MerchantClientUrl" => Core\Settings::get('Frontend.BaseUrl') . Core\Settings::get('Frontend.MerchantClient'),
                    "MerchantFrontBaseUrl" => Core\Settings::get('Frontend.BaseUrl'),
                    "CountryISO" => $CustomerDetails->getCountryISO(),
                    "CultureCode" => $CustomerDetails->getCultureCode(),
                    "CurrencyCode" => $CustomerDetails->getCurrencyCode(),
                    "MerchantParameters" => stripslashes(Models\Json::encode($ClientSettings))
                );

                $Output = "//Global-e script initializer
                       (function (w, d, u, t,o, h, m, s, l) {
                       w['globaleObject'] = o;
                       w[o] = w[o] || function () {{(w[o].q = w[o].q || []).push(arguments)}};
                       w[o].m = m,  w[o].v = h; s = d.createElement(t);
                       l = d.getElementsByTagName(t)[0];
                       s.async = true;
                       s.src = u + '?v=' + h;
                       l.parentNode.insertBefore(s, l);
                       })(window, document, '{$JsConfig['MerchantClientUrl']}', 'script','gle' ,'{$JsConfig['WebClientVersion']}', {$JsConfig['MerchantId']} );
                       gle('ScriptsURL','{$JsConfig['MerchantFrontBaseUrl']}');\r\n";

                if (!empty($JsConfig['CookieDomain']) && substr($JsConfig['CookieDomain'], 0, 1) !== '.') {
                    $Output .= "gle(\"SetCookieDomain\", '{$JsConfig['CookieDomain']}');\r\n";
                }
                if (!empty($JsConfig['MerchantParameters'])) {
                    $Output .= "gle(\"SetMerchantParameters\", {$JsConfig['MerchantParameters']});\r\n";
                }

                //Call to LoadWelcome for GE operated countries only
				$Country = Models\Country::getSingleton();
				$IsCountryOperatedByGlobale = $Country->IsCountryOperatedByGlobale($CustomerDetails->getCountryISO());

                if($IsCountryOperatedByGlobale){
					$Output .= "gle('LoadWelcome', '{$JsConfig['CountryISO']}', '{$JsConfig['CultureCode']}', '{$JsConfig['CurrencyCode']}');\r\n";
				}


                $Output .= "gle(\"LoadShippingSwitcher\", '{$JsConfig['CountryISO']}', '{$JsConfig['CultureCode']}', '{$JsConfig['CultureCode']}',false);";
                return new Response\Data(true, $Output);

            } else {
                return new Response(false,'Customer information was not initialized');
            }
        }else{
            return new Response(false,'Version Settings and Client Settings were not initialized');
        }
    }

    /**
     * Initialize the Global-e business logic and API.
     * @param float $VatRate      Base VAT Rate to use when SDK initialize.
     * @param string $BaseCurrency Base Currency code to use when SDK initialize.
     * @param string $BaseCountry  Base Country code to use when SDK initialize.
     * @param string $BaseCulture  Base Culture code to use when SDK initialize.
     * @return Response
     */
    protected function Initialize($VatRate = null, $BaseCurrency = null, $BaseCountry = null, $BaseCulture = null){

    	$AppModel = new Models\App();
		$AppModel->initBaseInfo($VatRate,$BaseCurrency, $BaseCountry, $BaseCulture);
        return new Response(true);
    }

    /**
     * Initialize SDK configuration and Global-e business logic
     * @return Response
     */
    protected function OnPageLoad(){

    	$AppModel = new Models\App();

		$AppModel->initCustomer();
		$AppModel->initAppVersion();
		$AppModel->initAppSettings();

        return new Response(true);
    }

    /**
     * Get customer information: CurrencyCode, CountryISO, CultureCode
     * @return Response
     */
    protected function GetCustomerInformation(){

        /**@var $customer Models\Customer */
        $customer = Models\Customer::getSingleton();
        $customerInfo = $customer->getInfo();
        if (!empty($customerInfo)) {
            return new Response\Data(true, $customerInfo);
        } else {
            return new Response(false,'public method GetCustomerInformation customer information model was not Initialized');
        }
    }

    /**
     * Get countries functionality object
     * - IsCountryOperatedByGlobale() check country supported by Global-e
     * - IsUserSupportedByGlobale() check user supported by Global-e
     * @return Response
     */
    protected function GetCountries(){

        // get country model
        /**@var $country Models\Country */
        $CountryModel = Models\Country::getSingleton();
        return new Response\Data(true, $CountryModel);
    }

    /**
     * Get currencies functionality object
     * - getCurrencies() get currencies collection
     * - getShortSymboledAmount() get the amount with the current currency [short]
     * - getLongSymboledAmount()  get the amount with the current currency [long]
     * @return Response
     */
    protected function GetCurrencies(){

        $currency = Models\Currency::getSingleton();
        return new Response\Data(true, $currency);
    }

    /**
     * Get products information collection
     * @param Request\ProductRequestData[] $Products
     * @param bool $PriceIncludesVAT
     * @return Response
     */
    protected function GetProductsInformation(array $Products, $PriceIncludesVAT = false){

        if ($this->IsUserSupportedByGlobale()) {
            $ProductModel = new Models\Product($Products, $PriceIncludesVAT);
            $ProductsResponse = $ProductModel->buildProductsInformationResult();
        } else {
            return new Response\Data(false, $Products, 'Country is NOT supported by Global-e');
        }

        return new Response\Data(true, $ProductsResponse);
    }


	/**
	 * Get calculated raw price
	 * @param array Request\RawPriceRequestData[]
	 * @param bool $PriceIncludesVAT
	 * @param bool $UseRounding
	 * @param bool $IsDiscount
	 * @return Response\Data
	 */
    protected function GetCalculatedRawPrice(array $RawPrices, $PriceIncludesVAT = false, $UseRounding = false, $IsDiscount = false ){

		$ProductModel = new Models\RawPrice($RawPrices,$PriceIncludesVAT,$UseRounding,$IsDiscount);
		$RawPricesResponse = $ProductModel->buildRawPricesInformationResult();

		return new Response\Data(true, $RawPricesResponse);
	}

	/**
	 * Beautifier Amount by Formatting and Rounding by Global-e logic
	 * @param $Amount
	 * @param bool $UseRounding
	 * @return Response\Data
	 */
	public function GetBeautyAmount($Amount,$UseRounding = true){

		$BeautifierModel = new Models\AmountBeautifier();
		$BeautyAmount = $BeautifierModel->beautifierAmount($Amount,$UseRounding);
		return new Response\Data(true, $BeautyAmount);

	}


	/**
     * Get if the country is operated by Global-e
     * @param string $countryCode Country Code to check on
     * @return Response
     */
    protected function IsCountryOperatedByGlobale($countryCode){

        /**@var $country Models\Country */
        // get country model
        $country = Models\Country::getSingleton();
        $isCountryOperatedByGlobale = $country->IsCountryOperatedByGlobale($countryCode);
        return new Response\Data(true, $isCountryOperatedByGlobale);
    }

    /**
     * Get if the user support by Global-e
     * @return Response
     */
    protected function IsUserSupportedByGlobale(){

        // get customer model
        /**@var $Customer Models\Customer */
        $Customer = Models\Customer::getSingleton();
        $isUserSupportedByGlobale = $Customer->IsUserSupportedByGlobale();
        return new Response\Data(true, $isUserSupportedByGlobale);
    }

    /**
     * Upated the customer information
     * @param Common\CustomerInfo $newCustomerInfo
     * @param bool $autoFillData
     * @return Response
     */
    protected function setUserInfo(Common\CustomerInfo $newCustomerInfo, $autoFillData = true){

        /**@var $customer Models\Customer */
        $customer = Models\Customer::getSingleton();
        $customerInfo = $customer->getInfo();
        if(!empty($customerInfo)) {
            if ($autoFillData) {
                $newCountyISO = $newCustomerInfo->getCountryISO();
                if (empty($newCountyISO)) {
                    $newCustomerInfo->setCountryISO($customerInfo->getCountryISO());
                }
                $newCultureCode = $newCustomerInfo->getCultureCode();
                if (empty($newCultureCode)) {
                    $newCustomerInfo->setCultureCode($customerInfo->getCultureCode());
                }
                $newCurrencyCode = $newCustomerInfo->getCurrencyCode();
                if (empty($newCurrencyCode)) {
                    $newCustomerInfo->setCurrencyCode($customerInfo->getCurrencyCode());
                }
            }
            $customer->setInfo($newCustomerInfo);
            $customer->updateCustomerCookie($customer->getInfo());
            return new Response\Data(true, true);
        }else{
            return new Response(false, 'Customer was not initialized');
        }
    }
}