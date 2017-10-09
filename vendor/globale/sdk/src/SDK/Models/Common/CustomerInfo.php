<?php
namespace GlobalE\SDK\Models\Common;

use GlobalE\SDK\Models\Common;

/**
 * Class CustomerInfo
 * @package GlobalE\SDK\Models\Common
 */
class CustomerInfo extends Common {

    /**
     * Country code
     * @var string $countryISO
     * @access public
     */
    public $countryISO;

    /**
     * Currency code
     * @var string $currencyCode
     * @access public
     */
	public $currencyCode;

    /**
     * Culture code
     * @var string $cultureCode
     * @access public
     */
	public $cultureCode;

    /**
     * CustomerInfo constructor, requires country ISO, currency code and culture code, those are mandatory fields for this common.
     * @param string $countryISO    country code
     * @param string $currencyCode   currency code
     * @param string $cultureCode    culture code
     */
    public function __construct($countryISO, $currencyCode, $cultureCode) {

        $this->setCountryISO($countryISO)
             ->setCurrencyCode($currencyCode)
             ->setCultureCode($cultureCode);
    }


	/**
	 * @return string
	 */
	public function getCountryISO()
	{
		return $this->countryISO;
	}

	/**
	 * @param string $countryISO
	 * @return CustomerInfo
	 */
	public function setCountryISO($countryISO)
	{
		$this->countryISO = $countryISO;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCurrencyCode()
	{
		return $this->currencyCode;
	}

	/**
	 * @param string $currencyCode
	 * @return CustomerInfo
	 */
	public function setCurrencyCode($currencyCode)
	{
		$this->currencyCode = $currencyCode;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCultureCode()
	{
		return $this->cultureCode;
	}

	/**
	 * @param string $cultureCode
	 * @return CustomerInfo
	 */
	public function setCultureCode($cultureCode)
	{
		$this->cultureCode = $cultureCode;
		return $this;
	}
}