<?php
namespace GlobalE\SDK\API\Common\Response;

use GlobalE\SDK\Models\Common;

/**
 * Class LocationCulture
 * @method getCountryCode()
 * @method getRegionCode()
 * @method getCityCode()
 * @method getCulture()
 * @method setCountryCode($CountryCode)
 * @method setRegionCode($RegionCode)
 * @method setCityCode($CityCode)
 * @method setCulture($Culture)
 * @package GlobalE\SDK\API\Common\Response
 */
class LocationCulture extends Common {

	/**
	 * 2-char ISO country code
	 * @var string $CountryCode
	 * @access public
	 */
	public $CountryCode;

	/**
	 * Region code (unique in the respective Country)
	 * @var string $RegionCode
	 * @access public
	 */
	public $RegionCode;

	/**
	 * City code (unique in the respective Region)
	 * @var string $CityCode
	 * @access public
	 */
	public $CityCode;

	/**
	 * Culture definition for this location
	 * @var object $Culture
	 * @access public
	 */
	public $Culture;
}