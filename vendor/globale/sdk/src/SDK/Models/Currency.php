<?php
namespace GlobalE\SDK\Models;
use GlobalE\SDK;
use GlobalE\SDK\Core;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\Response;
use GlobalE\SDK\API\Processors;
use GlobalE\SDK\API;


/**
 * Class Currency Model flow
 * @package GlobalE\SDK\Models
 */
class Currency extends Singleton {

    //VAT Types

    /**
     * Hidden vat
     */
    const HideVAT         = 0;
    /**
     * Included vat
     */
    const ShowVAT         = 2;
    /**
     * Poket vat
     */
    const PocketVAT       = 4;
    /**
     * Force vat
     */
    const ForceVAT        = 6;
    /**
     * Force the hidden vat
     */
    const ForceAndHideVAT = 8;

    /**
     * Currencies collection
     * @var API\Common\Response\Currency[]
     * @access protected
     */
    protected $Currencies;

    /**
     * Current currency
     * @var API\Common\Response\Currency
     * @access protected
     */
    protected $CurrentCurrency;

     /**
     * @desc Currency initializer.
     * @param array $params
     * @throws \Exception
     * @access protected
     */
    protected function initialize(array $params){
        $this->initCurrencies();
        $this->initCurrentCurrency();
    }

    /**
     * Initialize currencies collection
     */
	protected function initCurrencies() {

        // get all currency from API call
        $ApiParams = new Common\ApiParams();
        $Processor = new Processors\Currencies($ApiParams);
        $Currencies = $Processor->processRequest();
        $this->setCurrencies($Currencies);

    }

    /**
     * Initialize current currency by customer information
     * @throws \Exception
     */
    protected function initCurrentCurrency() {

        $currentCurrency = $this->getCurrentCurrency();
        if(empty($currentCurrency)) {

            /**@var $customer Customer */
            $customer = Customer::getSingleton();
            $customerDetails = $customer->getInfo();
            if(!empty($customerDetails)) {
                foreach ($this->getCurrencies() as $currency) {
                    // find customer currency details
                    if ($customerDetails->getCurrencyCode() == $currency->Code) {
                        $this->setCurrentCurrency($currency);
                        break;
                    }
                }
            }else{
                // throw exception when the customer object is empty
                Core\Log::log('Customer was not initialized', Core\Log::LEVEL_ERROR);
                throw new \Exception('Customer was not initialized');
            }
        }
		Core\Log::log('CurrentCurrency '.json_encode($this->getCurrentCurrency()), Core\Log::LEVEL_INFO);
    }

    /**
     * Get currencies collection
     * @return API\Common\Response\Currency[]
     * @access public
     */
    public function getCurrencies() {
        return $this->Currencies;
    }

    /**
     * Display the price amount with the customer currency, short
     * @param float $Amount
     * @return Response|string
     * @access public
     */
    public function getShortSymboledAmount($Amount) {

        $Currencies = $this->getCurrencies();
        if(empty($Currencies)) {
            $this->initCurrencies();
        }
        /**@var $Customer Customer */
        $Customer = Models\Customer::getSingleton();
        $customerDetails = $Customer->getInfo();

        $Currency = $this->getCurrentCurrency();
        if(!empty($Currency)){
            $AmountString = $Currency->Symbol . ' ' . $Amount;
            return $AmountString;
        }

        foreach($this->Currencies as $Currency) {
            if($Currency->Code == $customerDetails->getCurrencyCode()){
                $this->setCurrentCurrency($Currency);
                $AmountString = $Currency->Symbol . ' ' . $Amount;
                return $AmountString;
            }
        }

        // Customer Currency was not found in the supported currencies
        Core\Log::log("Customer Currency code: '{$customerDetails->getCurrencyCode()}' was not found in the supported currencies", Core\Log::LEVEL_ERROR);
        return new Response(false, "Customer Currency code: '{$customerDetails->getCurrencyCode()}' was not found in the supported currencies");
    }

    /**
     * Display the price amount with the customer currency, long
     * @param float $Amount
     * @return string
     * @access public
     */
    public function getLongSymboledAmount($Amount) {

        $Currencies = $this->getCurrencies();
        if(empty($Currencies)) {
            $this->initCurrencies();
        }
        /**@var $Customer Customer */
        $Customer = Models\Customer::getSingleton();
        $CustomerDetails = $Customer->getInfo();

        $Currency = $this->getCurrentCurrency();
        if(!empty($Currency)){
            $AmountString = "{$Currency->Symbol} {$Amount}($Currency->Name)";
            return $AmountString;
        }

        foreach($this->Currencies as $Currency) {
            if($Currency->Code == $CustomerDetails->getCurrencyCode()){
                $this->setCurrentCurrency($Currency);
                $AmountString = "{$Currency->Symbol} {$Amount}($Currency->Name)";
                return $AmountString;
            }
        }

        // Customer currency was not found in the supported Currencies
        Core\Log::log("Customer currency code: '{$CustomerDetails->getCurrencyCode()}' was not found in the supported Currencies", Core\Log::LEVEL_ERROR);
        return new Response(false, "Customer currency code: '{$CustomerDetails->getCurrencyCode()}' was not found in the supported Currencies");
    }

    /**
     * Fetch CurrencyRates from API
     * @return API\Common\Response\CurrencyRate
     * @access public
     */
    public function fetchCurrencyRates(){

        /**@var $Customer Customer */
        $Customer = Customer::getSingleton();
        $ApiParams = new Common\ApiParams();
        $ApiParams->setUri(array('targetCurrencyCode' => $Customer->getInfo()->getCurrencyCode(),
                                 'sourceCurrencyCode' => SDK\SDK::$baseInfo->getCurrencyCode()));

        $Processor = new Processors\CurrencyRates($ApiParams);
        $CurrencyRates = $Processor->processRequest();
        return $CurrencyRates[0];
    }

    /**
     * Set currencies collection
     * @param API\Common\Response\Currency[] $Currencies
     * @return Currency
     * @access public
     */
    protected function setCurrencies($Currencies) {
        $this->Currencies = $Currencies;
        return $this;
    }

    /**
     * Set the current currency
     * @param  API\Common\Response\Currency
     * @return Currency
     * @access public
     */
    protected function setCurrentCurrency($Currency) {
        $this->CurrentCurrency = $Currency;
        return $this;
    }

    /**
     * Get current currency
     * @return API\Common\Response\Currency
     * @access public
     */
    public function getCurrentCurrency() {
        return $this->CurrentCurrency;
    }


}