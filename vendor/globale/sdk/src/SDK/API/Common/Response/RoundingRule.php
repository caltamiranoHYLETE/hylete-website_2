<?php
namespace GlobalE\SDK\API\Common\Response;

use GlobalE\SDK\Models\Common;

/**
 * Class RoundingRule
 * @method getRoundingRuleId()
 * @method getCurrencyCode()
 * @method getCountryCode()
 * @method getRoundingRanges()
 * @method setRoundingRuleId($RoundingRuleId)
 * @method setCurrencyCode($CurrencyCode)
 * @method setCountryCode($CountryCode)
 * @method setRoundingRanges($RoundingRanges)
 * @package GlobalE\SDK\API\Common\Response
 */
class RoundingRule extends Common {

	/**
	 * Rule identifier denoting the respective Rounding Rule on Global-e side.
	 * This value must be further specified when calling SaveProductsList and SendCart methods.
	 * @var int $RoundingRuleId
	 * @access public
	 */
	public $RoundingRuleId;

	/**
	 * 3-char ISO currency code.
	 * @var string $CurrencyCode
	 * @access public
	 */
	public $CurrencyCode;

	/**
	 * 2-char ISO country code
	 * @var string $CountryCode
	 * @access public
	 */
	public $CountryCode;

	/**
	 * Array of decimal ranges and their respective rounding behaviors
	 * @var \stdClass $RoundingRanges
	 * @access public
	 */
	public $RoundingRanges;
}