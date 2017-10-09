<?php
namespace GlobalE\SDK\API\Common\Response;

use GlobalE\SDK\Models\Common;

/**
 * Class CurrencyRate
 * @method getSourceCurrencyCode()
 * @method getTargetCurrencyCode()
 * @method getRate()
 * @method getRateData()
 * @method setSourceCurrencyCode($SourceCurrencyCode)
 * @method setTargetCurrencyCode($TargetCurrencyCode)
 * @method setRate($Rate)
 * @method setRateData($RateData)
 * @package GlobalE\SDK\API\Common\Response
 */
class CurrencyRate extends Common {

	/**
	 * 3-char ISO currency code.
	 * @var string $SourceCurrencyCode
	 * @access public
	 */
	public $SourceCurrencyCode;

	/**
	 * Target currency code.
	 * @var string $TargetCurrencyCode
	 * @access public
	 */
	public $TargetCurrencyCode;

	/**
	 * Currency rate decimal value
	 * @var float $Rate
	 * @access public
	 */
	public $Rate;

    /**
     *
     * @var string $RateData
     * @access public
     */
	public $RateData;
}