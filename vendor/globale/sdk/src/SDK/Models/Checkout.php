<?php
namespace GlobalE\SDK\Models;

use GlobalE\SDK\SDK;
use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common\Request;
use GlobalE\SDK\API\Processors;
use GlobalE\SDK\Models\Common\ApiParams;

/**
 * Class Checkout
 * @package GlobalE\SDK\Models
 */
class Checkout {

    const SEND_CART_VERSION_1 = 1;
    const SEND_CART_VERSION_2 = 2;

    const EXCEPTION_INVALID_VERSION_TXT = 'Requested SendCartVersion - %s is not supported. Supported versions: 1,2';

    /**
     * Generate checkout page
     * @param string $CartToken
     * @param string $ContainerId
     * @return string $generateCheckout
     * @throws \Exception
     * @access public
     */
    public function generateCheckout($CartToken, $ContainerId = 'checkoutContainer') {

        //@TODO: add sanitize check to the $_GET parameter
        if(!empty($_GET['token'])){
            // get cart token from $_GET parameter
            $CartToken = $_GET['token'];
        }
        elseif(!empty($_SESSION['GlobalE_CartToken'])){
            // get cart token from $_SESSION parameter
            $CartToken = $_SESSION['GlobalE_CartToken'];
        }
        // check if any cart token was given
        if(!empty($CartToken)){
            // generate check}\"); ";
            $GenerateCheckout =  "gle(\"Checkout\", \"{$CartToken}\",\"{$ContainerId}\");";
            Core\Log::log('GenerateCheckoutPage  '.json_encode($CartToken), Core\Log::LEVEL_INFO);
            return $GenerateCheckout;
        }
        else{
            // Cart token is empty
            Core\Log::log('GenerateCheckoutPage public method failed ', Core\Log::LEVEL_DEBUG);
            throw new \Exception('Cart token is empty, Cart token is mandatory for generate the checkout page', 0);
        }
    }

    /**
     * Will return original CultureCode only if  flag "AlwaysUseOriginalCultureCode"
     * is set to true in settings, otherwise will take from customer.
     * @return string
     * @access protected
     */
    protected function getCultureCode(){
        if(Core\Settings::get('AlwaysUseOriginalCultureCode') === true){
            return SDK::$baseInfo->getCultureCode();
        }
        else{
            /**@var $Customer Customer */
            $Customer = Customer::getSingleton();
            return $Customer->getInfo()->getCultureCode();
        }
    }

    /**
     * Get the country model functionality
     * @return Country
     * @access protected
     */
    protected function getCountryModel(){
        /**@var $Country Country */
        $Country = Country::getSingleton();
        return $Country;
    }

    /**
     * Get the currency model functionality
     * @return Currency
     * @access protected
     */
    protected function getCurrencyModel(){
        /** @var  $Currency Currency */
        $Currency = Currency::getSingleton();
        return $Currency;
    }

    /**
     * Collect all information in order to send to the API service
     * @param Request\SendCart $SendCartRequest
     * @return Common\ApiParams
     * @access public
     */
    protected function buildApiParamsForSendCartV1(Request\SendCart $SendCartRequest){

        $ApiParams = new Common\ApiParams();

        if(isset($_SESSION['GlobalE_CartToken'])){
            $ApiParams->setUri(array('cartToken' => $_SESSION['GlobalE_CartToken']));
        }

        // Set Uri fields
        $Country = $this->getCountryModel();
        $CountryCoefficients = $Country->fetchCountryCoefficients();

        $Currency = $this->getCurrencyModel();
        $RateData = $Currency->fetchCurrencyRates();

		//add WebStoreCode and WebStoreInstance if exists
		$WebStoreCode = Core\Settings::get('EnvDetails.WebStoreCode') ;
		$WebStoreInstanceCode = Core\Settings::get('EnvDetails.WebStoreInstanceCode');

        /**@var $Customer Customer */
        $Customer = Customer::getSingleton();
        // set API url parameters for send cart
        $ApiParams->setUri(
            array(
                'clientIP'             => $Customer->getIp(),
                'currencyCode'         => $Customer->getInfo()->getCurrencyCode(),
                'cultureCode'          => $Customer->getInfo()->getCultureCode(),
                'countryCode'          => $Customer->getInfo()->getCountryISO(),
                'originalCurrencyCode' => SDK::$baseInfo->getCurrencyCode(),
                'preferedCultureCode'  => Culture::getGlobaleCulture(SDK::$baseInfo->getCultureCode()),
                'inputDataCultureCode' => $this->getCultureCode(),
                'priceCoefficientRate' => $CountryCoefficients->Rate,
                'roundingRuleId'       => $Country->getRoundingRuleId(),
                'includeVAT'           => $CountryCoefficients->IncludeVAT,
                'rateData'             => $RateData->getRateData()
            )
        );

        $ApiParams->setBody($SendCartRequest->getProductsList());
        // Overwrite Uri fields with $SendCartRequest array that we got from extension
        $ApiParams->setUri(array('shippingDetails' => $SendCartRequest->getShippingDetails()));
        $ApiParams->setUri(array('billingDetails' => $SendCartRequest->getBillingDetails()));
        $ApiParams->setUri(array('shippingOptionsList' => $SendCartRequest->getShippingOptionsList()));
        $ApiParams->setUri(array('originalCurrencyCode' => $SendCartRequest->getOriginalCurrencyCode()));
        $ApiParams->setUri(array('merchantCartToken' => $SendCartRequest->getMerchantCartToken()));
        $ApiParams->setUri(array('merchantCartHash' => $SendCartRequest->getMerchantCartHash()));
        $ApiParams->setUri(array('doNotChargeVAT' => $SendCartRequest->getDoNotChargeVAT()));
        $ApiParams->setUri(array('discountsList' => $SendCartRequest->getDiscountsList()));
        $ApiParams->setUri(array('vatRegistrationNumber' => $SendCartRequest->getVatRegistrationNumber()));
        $ApiParams->setUri(array('IsFreeShipping' => $SendCartRequest->getIsFreeShipping()));
        $ApiParams->setUri(array('cartId' => $SendCartRequest->getCartId()));
        $ApiParams->setUri(array('webStoreCode' => $WebStoreCode));
        $ApiParams->setUri(array('webStoreInstanceCode' => $WebStoreInstanceCode));
        if($SendCartRequest->getIsFreeShipping()){
            $ApiParams->setUri(array('FreeShippingCouponCode' => $SendCartRequest->getFreeShippingCouponCode()));
        }
        $ApiParams->setUri(array('urlParameters' => json_encode($SendCartRequest->getUrlParameters())));

        return $ApiParams;
    }

    /**
     * Collect all information in order to send to the API service
     * @param Request\SendCart $SendCartRequest
     * @return Common\ApiParams
     * @access public
     */
    protected function buildApiParamsForSendCartV2(Request\SendCart $SendCartRequest){

        $ApiParams = new Common\ApiParams();

        // Set Uri fields
        $Country = $this->getCountryModel();
        $CountryCoefficients = $Country->fetchCountryCoefficients();

        $Currency = $this->getCurrencyModel();
        $RateData = $Currency->fetchCurrencyRates();

        /**@var $Customer Customer */
        $Customer = Customer::getSingleton();

        $BillingAddress = $SendCartRequest->getBillingDetails();
        $BillingAddress->setIsBilling(true);
        $BillingAddress->setIsDefaultBilling(true);
        $ShippingAddress = $SendCartRequest->getShippingDetails();
        $ShippingAddress->setIsShipping(true);
        $ShippingAddress->setIsDefaultShipping(true);


		//add WebStoreCode and WebStoreInstance if exists
		$WebStoreCode = Core\Settings::get('EnvDetails.WebStoreCode') ;
		$WebStoreInstanceCode = Core\Settings::get('EnvDetails.WebStoreInstanceCode');


		// set API url parameters for send cart
        $Params = array(

            'CountryCode'   => $Customer->getInfo()->getCountryISO(),
            'clientIP'      => $Customer->getIp(),
            'Currency' => array(
                'currencyCode'         => $Customer->getInfo()->getCurrencyCode(),
                'originalCurrencyCode' => SDK::$baseInfo->getCurrencyCode(),
            ),
            'PriceModification' => array(
                'RoundingRuleId'        => $Country->getRoundingRuleId(),
                'priceCoefficientRate'  => $CountryCoefficients->Rate,
                'IncludeVAT'            => $CountryCoefficients->IncludeVAT,
            ),
            'Culture'           => array(
                'CultureCode'          => $Customer->getInfo()->getCultureCode(),
                'InputDataCultureCode' => $this->getCultureCode(),
                'PreferedCultureCode'  => Culture::getGlobaleCulture(SDK::$baseInfo->getCultureCode()),
            ),
            'LocalShippingOptions' => array( // 'ShippingOptionsList'
                0 => $SendCartRequest->getShippingOptionsList(),
            ),
            'Products'              => $SendCartRequest->getProductsList(),
            'CartToken'             => $_SESSION['GlobalE_CartToken'] ? $_SESSION['GlobalE_CartToken'] : null,
            'MerchantCartToken'     => $SendCartRequest->getMerchantCartToken(),
            'MerchantCartHash'      => $SendCartRequest->getMerchantCartHash(),
            'HubId'                 => null,
            // -- delayed 'PaymentInstallments'   => null,
            'UserDetails' => array(
                'UserId'            => $SendCartRequest->getShippingDetails()->getUserId(),
                'AddressDetails'    => array(
                    $ShippingAddress, $BillingAddress
                ),
            ),
            'UrlParametrs'      => json_encode($SendCartRequest->getUrlParameters()),
            'Discounts'         => $SendCartRequest->getDiscountsList(),
            'VATRegistration'   => array(
                'VatRegistrationNumber' => $SendCartRequest->getVatRegistrationNumber(),
                'DoNotChargeVAT'        => $SendCartRequest->getDoNotChargeVAT(),
            ),
            'FreeShipping' => array(
                'IsFreeShipping'            => $SendCartRequest->getIsFreeShipping(),
                'FreeShippingCouponCode'    => $SendCartRequest->getIsFreeShipping() ? $SendCartRequest->getFreeShippingCouponCode() : null ,
            ),
            /*
            -- START delayed
            'VoucherData' => [
                'LoyaltyVouchers' => [
                ],
                'OTVoucher' => '',
            ],
            'LoyaltyData' => [
                'LoyaltyCode'           => '',
                'LoyaltyPointsSpent'    => '',
                'LoyaltyPointsEarned'   => '',
                'LoyaltyPointsTotal'    => '',
            ],
            -- delayed EOF
            */
            'rateData'      => $RateData->getRateData(),
            'webStoreCode'  => $WebStoreCode,
            'webStoreInstanceCode' => $WebStoreInstanceCode

        );

        $ApiParams->setBody($Params);
        return $ApiParams;
    }

    public function GetVersion()
    {
        $Version = Core\Settings::get('API.SendCartVersion');
        if (!$Version) {
            $Version = self::SEND_CART_VERSION_1;
        }
        return $Version;
    }

    /**
     * @param ApiParams $ApiParams
     * @return Processors\SendCartV1|Processors\SendCartV2|null
     */
    public function GetSendCartProcessor(ApiParams $ApiParams)
    {
        $Processor = null;

        switch($this->GetVersion()) {
            case self::SEND_CART_VERSION_1:
                $Processor = new Processors\SendCartV1($ApiParams);
                break;
            case self::SEND_CART_VERSION_2:
                $Processor = new Processors\SendCartV2($ApiParams);
                break;
            default:
                Core\Log::log(sprintf(self::EXCEPTION_INVALID_VERSION_TXT, $this->GetVersion()), Core\Log::LEVEL_CRITICAL);
                throw new \RuntimeException(sprintf(self::EXCEPTION_INVALID_VERSION_TXT, $this->GetVersion()));
                break;
        }

        return $Processor;
    }

    /**
     * Collect all information in order to send to the API service
     * @param Request\SendCart $SendCartRequest
     * @return Common\ApiParams
     * @access public
     */
    public function buildApiParamsForSendCart(Request\SendCart $SendCartRequest){

        $ApiParams = null;
        switch($this->GetVersion()) {
            case self::SEND_CART_VERSION_1:
                $ApiParams = $this->buildApiParamsForSendCartV1($SendCartRequest);
                break;
            case self::SEND_CART_VERSION_2:
                $ApiParams = $this->buildApiParamsForSendCartV2($SendCartRequest);
                break;
            default:
                Core\Log::log(sprintf(self::EXCEPTION_INVALID_VERSION_TXT, $this->GetVersion()),Core\Log::LEVEL_CRITICAL);
                throw new \RuntimeException(sprintf(self::EXCEPTION_INVALID_VERSION_TXT, $this->GetVersion()));
                break;
        }
        return $ApiParams;
    }
}