<?php
namespace GlobalE\SDK\Models;
use GlobalE\SDK\Core;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\API\Processors;
use GlobalE\SDK\API;

/**
 * Class Country Model flow
 * @package GlobalE\SDK\Models
 */
class Country extends Singleton {

    /**
     * Countries from api request
     * @var API\Common\Response\Country[]
     * @access protected
     */
    protected $Countries;

	/**
	 * Array of Operated by Global-e Countries
	 * @var array
	 */
	protected $OperatedCountries;

    /**
     * Vat rate type
     * @var Common\VatRateType $VatRateType
     * @access protected
     */
    protected $VatRateType;

    /**
     * Default country
     * @var API\Common\Response\Country
     * @access protected
     */
    protected $Country;

    /**
     * @desc Country initializer.
     * @param array $params
     * @throws \Exception
     * @access public
     */
    protected function initialize(array $params){
        $this->initCountries();
    }

    /**
     * Initialize countries collection
     * @return bool|array|null
     * @throws \Exception
     * @access public
     */
    public function initCountries() {

        try {
            if(empty($this->Countries)){
                // get all countries from API call
                $ApiParams = new Common\ApiParams();
                $getCountries = new Processors\Countries($ApiParams);
                $countries = $getCountries->processRequest();
                $this->setCountries($countries);
            }
            else{
                $countries = $this->getCountries();
            }
            $this->initCountry();
            $this->initVatRateType();
            $this->initOperatedCountries();
            return $countries;
        }
        catch (\Exception $e) {
            Core\Log::log('Api call to getCountries failed ' . $e->getMessage(), Core\Log::LEVEL_ERROR);
            throw new \Exception('Api call to getCountries failed ' . $e->getMessage());
        }
    }

    /**
     * Initialize country by customer information
     * @access protected
     */
    protected function initCountry(){
        /**@var $customer Models\Customer */
        $customer = Models\Customer::getSingleton();
        $customerInfo = $customer->getInfo();
        if(empty($customerInfo)){
            $msg = "Customer info isn't set, probably because Browsing->OnPageLoad wasn't called.";
            Core\Log::log($msg,Core\Log::LEVEL_NOTICE);
            throw new \Exception($msg);
        }
        $customerCountry = $customerInfo->getCountryISO();

        foreach($this->Countries as $Country){
            if($Country->Code == $customerCountry){
                // set country in class property
                $this->setCountry($Country);
                break;
            }
        }
    }

    /**
     * Check the given country if is operated/not operated by Global-e
     * @param string $countryCode
     * @return bool
     * @access public
     */
    public function IsCountryOperatedByGlobale($countryCode) {

		return in_array($countryCode,$this->getOperatedCountries());
    }


	/**
	 * Init OperatedCountries according to IsOperatedByGlobalE value
	 */
    protected function initOperatedCountries(){
    	$OperatedCountries = array();
    	$AllCountries = $this->getCountries();

    	if(!empty($AllCountries)){
    		foreach ($AllCountries AS $Country){
    			if($Country->IsOperatedByGlobalE){
					$OperatedCountries[] = $Country->getCode();
				}
			}
		}
		$this->setOperatedCountries($OperatedCountries);
	}


    /**
     * Get countries collection
     * @return API\Common\Response\Country[]
     * @access public
     */
    public function getCountries() {
        return $this->Countries;
    }

    /**
     * Get the current country
     * @return API\Common\Response\Country
     * @access public
     */
    public function getCountry() {
        return $this->Country;
    }

	/**
	 * Get Array of Operated by Global-e Countries
	 * @return array
	 */
	public function getOperatedCountries()
	{
		return $this->OperatedCountries;
	}

	/**
	 * @param array $OperatedCountries
	 * @return Country
	 */
	protected function setOperatedCountries($OperatedCountries)
	{
		$this->OperatedCountries = $OperatedCountries;
		return $this;
	}

    /**
     * Initialize vat rate with the current country
     * @access protected
     */
    protected function initVatRateType() {
        $country = $this->getCountry();

        if (isset($country) && $country->DefaultVATRateType != null) {

            // set VatRateType in class property
            $this->setVatRateType(new Common\VatRateType(
                    $country->DefaultVATRateType->Rate,
                    $country->DefaultVATRateType->Name,
                    $country->DefaultVATRateType->VATRateTypeCode
				)
            );
        }

    }

    /**
     * @desc Get country coefficients from API service
     * @return API\Common\Response\CountryCoefficient
     * @access protected
     */
    public function fetchCountryCoefficients(){

        /**@var $customer Customer */
        $customer = Customer::getSingleton();
        $ApiParams = new Common\ApiParams();
        $ApiParams->setUri(array('countryCode' => $customer->getInfo()->getCountryISO()));
        $processor = new Processors\CountryCoefficients($ApiParams);
        $CurrencyCoefficients = $processor->processRequest();
        return $CurrencyCoefficients[0];
    }

    /**
     * Get rounding rules from API service
     * @return API\Common\Response\RoundingRule $RoundingRules
     * @access public
     */
    public function fetchRoundingRules(){

        /**@var $customer Customer */
        $customer = Customer::getSingleton();
        $ApiParams = new Common\ApiParams();
        $ApiParams->setUri(array('countryCode' => $customer->getInfo()->getCountryISO()));
        $ApiParams->setUri(array('currencyCode' => $customer->getInfo()->getCurrencyCode()));
        $processor = new Processors\RoundingRules($ApiParams);
        $RoundingRules = $processor->processRequest();
        return $RoundingRules;
    }

    /**
     * @desc Get rounding rules id
     * @return int|null
     * @access public
     */
    public function getRoundingRuleId(){
        $RoundingRules = $this->fetchRoundingRules();
        if(is_object($RoundingRules) && isset($RoundingRules->RoundingRuleId)){
            return $RoundingRules->RoundingRuleId;
        }
        else{
            Core\Log::log('No rounding rule id found', Core\Log::LEVEL_NOTICE);
            return null;
        }
    }

    /**
     * Set countries collection
     * @param API\Common\Response\Country[] $countries
     * @return Country
     * @access protected
     */
    protected function setCountries($countries) {
        $this->Countries = $countries;
        return $this;
    }

    /**
     * @desc set the current country
     * @param API\Common\Response\Country $Country
     * @return Country
     * @access protected
     */
    protected function setCountry($Country) {
        $this->Country = $Country;
        return $this;
    }

    /**
     * Set vat rate object
     * @param Common\VatRateType $VatRateType
     * @return Country
     * @access protected
     */
    protected function setVatRateType(Common\VatRateType $VatRateType){
        $this->VatRateType = $VatRateType;
        return $this;
    }

    /**
     * Get vat rate object
     * @return Common\VatRateType
     * @access public
     */
    public function getVatRateType()
    {
        return $this->VatRateType;
    }

}