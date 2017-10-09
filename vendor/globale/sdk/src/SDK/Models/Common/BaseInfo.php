<?php
namespace GlobalE\SDK\Models\Common;

use GlobalE\SDK\Models\Common;

/**
 * Class BaseInfo
 * @method getCountryISO()
 * @method getCurrencyCode()
 * @method getCultureCode()
 * @method $this setCountryISO($CountryISO)
 * @method $this setCurrencyCode($CurrencyCode)
 * @method $this setCultureCode($CultureCode)
 * @package GlobalE\SDK\Models\Common
 */
class BaseInfo extends Common {

    /**
     * Country code
     * @var string $CountryISO
     * @access public
     */
    public $CountryISO;
    /**
     * Currency code
     * @var string $CurrencyCode
     * @access public
     */
    public $CurrencyCode;
    /**
     * Culture code
     * @var string $CultureCode
     * @access public
     */
    public $CultureCode;

    /**
     * BaseInfo constructor, requires country ISO, currency code and culture code, those are mandatory fields for this common.
     * @param $CountryISO [string]   country code
     * @param $CurrencyCode [string] currency code
     * @param $CultureCode [string]  culture code
     * @access public
     */
    public function __construct($CountryISO, $CurrencyCode, $CultureCode) {

        $this->setCountryISO($CountryISO)
            ->setCurrencyCode($CurrencyCode)
            ->setCultureCode($CultureCode);
    }
}