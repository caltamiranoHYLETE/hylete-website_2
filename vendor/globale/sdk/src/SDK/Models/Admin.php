<?php
namespace GlobalE\SDK\Models;

use GlobalE\SDK\SDK;
use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common\Request;


/**
 * Class Admin
 * @package GlobalE\SDK\Models
 */
class Admin {
    
    /**
     * Collect all information in order to send to the API service
     * @param Request\Product[] $Products
     * @return Common\ApiParams
     * @access public
     */
    public function buildApiParamsForSaveProductsList(array $Products){

        $ApiParams = new Common\ApiParams();

        // Set Uri fields
        $Country = $this->getCountryModel();
        $CountryCoefficients = $Country->fetchCountryCoefficients();
        /**@var $Customer Customer */
        $Customer = Customer::getSingleton();
        $ApiParams->setUri(
            array(
                'clientIP'             => $Customer->getIp(),
                'cultureCode'          => $Customer->getInfo()->getCultureCode(),
                'countryCode'          => $Customer->getInfo()->getCountryISO(),
                'originalCurrencyCode' => SDK::$baseInfo->getCurrencyCode(),
                'inputDataCultureCode' => $this->getCultureCode(),
                'priceCoefficientRate' => $CountryCoefficients->Rate,
                'roundingRuleId'       => $Country->getRoundingRuleId(),
                'includeVAT'           => $CountryCoefficients->IncludeVAT
            )
        );

        $ApiParams->setBody($Products);

        return $ApiParams;
    }

    /**
     * Get the singleton country model
     * @return Country
     * @access public
     */
    protected function getCountryModel(){
    	/**@var $Country Country */
        $Country = Country::getSingleton();
        return $Country;
    }

    /**
     * Get headers for the order invoice
     * @param $ContentLength
     * @return array
     * @access public
     */
    public function getOrderInvoiceHeaders($ContentLength){
        return array('Cache-Control: public',
                     'Content-type: application/pdf',
                     'Content-Length: '. $ContentLength);
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

}