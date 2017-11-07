<?php
namespace GlobalE\SDK\Models;

use GlobalE\SDK\SDK;
use GlobalE\SDK\Core;
use GlobalE\SDK\Models;
use GlobalE\SDK\API\Processors;
use GlobalE\SDK\API\Common\Response;
use GlobalE\SDK\Models\Common;

/**
 * Class Customer Model flow
 * @package GlobalE\SDK\Models
 */
class Customer extends Singleton {

    /**
     * Customer Information data
     * @var Common\CustomerInfo
     */
    protected $info;

    /**
     * Customer IP address
     * @var string
     * @access protected
     */
    protected $Ip;

    /**
     * Customer Object (singleton)
     * @var object Customer
     * @access static protected
     */
    protected static $instance = null;

     /**
     * @desc Customer initializer.
     * @param array $params
     * @access protected
     */
    protected function initialize(array $params){ }

    /**
     * Get the customer information
     * @return Common\CustomerInfo
     * @access public
     */
    public function getInfo(){
        return $this->info;
    }

    /**
     * Set the information on the Customer
     * @param Common\CustomerInfo $customerDetails
     * @return Customer
     * @access public
     */
    public function setInfo(Common\CustomerInfo $customerDetails) {

        $this->info = $customerDetails;
        return $this;
    }

    /**
     * Update the customer information cookie
     * @param Common\CustomerInfo $customerDetails
     * @access public
     */
    public function updateCustomerCookie(Common\CustomerInfo $customerDetails) {

            $CookieDomain = Core\Settings::get('Cookies.Domain');
            if(!empty($CookieDomain)){
				$CookieDomain = ltrim($CookieDomain,'.');
            	$CookieDomain = "." . $CookieDomain;

            }
            else{
                $CookieDomain = $_SERVER['HTTP_HOST'];
            }
            setcookie(
                Core\Settings::get('Cookies.DefaultName'),
                Models\Json::encode($customerDetails),
                (time() + Core\Settings::get('Cookies.Expire')),
                Core\Settings::get('Cookies.Path'),
                $CookieDomain
            );
    }

    /**
     * Check if has cookie with the customer information
     * @return bool
     * @access public
     */
    public function hasCustomerCookie() {
        return (!empty($_COOKIE[Core\Settings::get('Cookies.DefaultName')]));
    }

    /**
     * Get the Customer information object from cookie
     * @return \stdClass
     * @throws \Exception
     * @access public
     */
    public function fetchCustomerCookie(){
        return Models\Json::decode($_COOKIE[Core\Settings::get('Cookies.DefaultName')]);
    }

    /**
     * Initialize the customer with customer information
     * @access public
     */
    public function initCustomerInfo() {

		$GeUrlParams = $this->getGeUrlParams();

        if(!empty($GeUrlParams)){
			$CountryCode = $GeUrlParams['country'];
			$CurrencyCode = $GeUrlParams['currency'];

            Core\Log::log('Custom Customer details taken from URL Params',
				Core\Log::LEVEL_DEBUG,
				array(
					"country" => $CountryCode,
					"currency" => $CurrencyCode
				)
			);
        }
        else{
            // get LocationByIp
            $ApiParams = new Common\ApiParams();
            $ApiParams->setUri(array("IP" => $this->getIp(),
                "useipv6" => "true"));
            $getLocationByIp = new Processors\LocationByIp($ApiParams);
            $locationByIp = $getLocationByIp->processRequest();
            /**@var $locationByIpObj Response\LocationByIp */
            $locationByIpObj = $locationByIp[0];
            Core\Log::log('api call getLocationByIp', Core\Log::LEVEL_DEBUG, array("locationByIp" => Models\Json::encode($getLocationByIp)));
        }


        // getLocationDefaultCulture
        $ApiParams = new Common\ApiParams();

		if(!empty($CountryCode)){
            $ApiParams->setUri(array("countryCode" => $CountryCode));
        }
        else{
            /**@var $CountryByIp \stdClass */
            $CountryByIp = $locationByIpObj->getCountry();
            $ApiParams->setUri(array("countryCode" => $CountryByIp->Code));
        }

        $getLocationDefaultCulture = new Processors\LocationDefaultCulture($ApiParams);
        $locationDefaultCulture = $getLocationDefaultCulture->processRequest();
		$locationDefaultCulture = $locationDefaultCulture[0];
		Core\Log::log('api call getLocationDefaultCulture', Core\Log::LEVEL_DEBUG, array("locationDefaultCulture" => Models\Json::encode($locationDefaultCulture)));

        if(!empty($CountryByIp->DefaultCurrencyCode) && $CountryByIp->DefaultCurrencyCode == '___'){
            //if IP not recognize reset the customer information from base Magento settings.
            $CountryByIp->Code                = SDK::$baseInfo->getCountryISO();
            $CountryByIp->DefaultCurrencyCode = SDK::$baseInfo->getCurrencyCode();
            $locationDefaultCulture->Code     = SDK::$baseInfo->getCultureCode();
            Core\Log::log('Customer information was undefined! New customer information was loaded from the SDK base settings!', Core\Log::LEVEL_DEBUG);
        }

		// set user info from API calls
        if(!empty($CountryCode)){
            $customerInfo = new Common\CustomerInfo(
                $CountryCode,
                $CurrencyCode,
                $locationDefaultCulture->Code
            );
        }
        else{
            $customerInfo = new Common\CustomerInfo(
                $CountryByIp->Code,
                $CountryByIp->DefaultCurrencyCode,
                $locationDefaultCulture->Code
            );
        }

        $this->setInfo($customerInfo);
        $this->updateCustomerCookie($customerInfo);
    }

    /**
     * Get if user supported/unsupported by Globale
     * @return bool
     * @access public
     */
    public function IsUserSupportedByGlobale(){
        $CustomerDetails = $this->getInfo();
        /**@var $Country Country */
        $Country = Country::getSingleton();
        $IsCountryOperatedByGlobale = $Country->IsCountryOperatedByGlobale($CustomerDetails->getCountryISO());
        return $IsCountryOperatedByGlobale;
    }

    /**
     * Get the customer IP address
     * @return string
     * @access protected
     */
    protected function getCustomerIP(){
        if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        Core\Log::log("Customer ip: {$ip}", Core\Log::LEVEL_DEBUG, array($ip));
        return $ip;
    }

    /**
     * Get the customer IP address
     * @return string
     * @access public
     */
    public function getIp()
    {
        return $this->Ip;
    }

    /**
     * Set IP address for the customer
     * @return Customer
     * @access public
     */
    public function setIp()
    {
        $this->Ip = $this->getCustomerIP();
        return $this;
    }

	/**
	 * get country/currency params by url params
	 * @return array
	 */
    public function getGeUrlParams(){
    	if(!empty($_GET['ge_country']) && strlen($_GET['ge_country']) == 2 && !empty($_GET['ge_currency']) && strlen($_GET['ge_currency']) == 3){
    		return array('country' => $_GET['ge_country'], 'currency' => $_GET['ge_currency']);
		}

		if(!empty($_GET['glCountry']) && strlen($_GET['glCountry']) == 2 && !empty($_GET['glCurrency']) && strlen($_GET['glCurrency']) == 3){
			return array('country' => $_GET['glCountry'], 'currency' => $_GET['glCurrency']);
		}
		return array();
	}

}