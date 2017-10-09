<?php
namespace GlobalE\SDK\API\Common\Response;

use GlobalE\SDK\Models\Common;

/**
 * Class Currency
 * @method getCode()
 * @method getName()
 * @method getSymbol()
 * @method getMaxDecimalPlaces()
 * @method setCode($Code)
 * @method setName($Name)
 * @method setSymbol($Symbol)
 * @method setMaxDecimalPlaces($MaxDecimalPlaces)
 * @package GlobalE\SDK\API\Common\Response
 */
class Currency extends Common {

	/**
	 * 3-char ISO currency code
	 * @var string $Code
	 * @access public
	 */
	public $Code;

	/**
	 * Currency name
	 * @var string $Name
	 * @access public
	 */
	public $Name;

	/**
	 * Currency symbol
	 * @var string $Symbol
	 * @access public
	 */
	public $Symbol;

	/**
	 * Number of decimal places indicating the fractional (“cents”) part of the price.
	 * For example Bahraini Dinar “cents” has 3 decimal places, US Dollar has 2, and Japanese Yen has 0.
	 * @var int $MaxDecimalPlaces
	 * @access public
	 */
	public $MaxDecimalPlaces;

}