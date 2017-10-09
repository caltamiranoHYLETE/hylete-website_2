<?php
namespace GlobalE\SDK\API\Common\Response;

use GlobalE\SDK\Models\Common;

/**
 * Class AppVersion
 * @method getWebClientVersion()
 * @method getAPIVersion()
 * @method setWebClientVersion($WebClientVersion)
 * @method setAPIVersion($APIVersion)
 * @package GlobalE\SDK\API\Common\Response
 */
class AppVersion extends Common {

	/**
	 * Version identifier of Global-e client-side (JS) code.
	 * @var string $WebClientVersion
	 * @access public
	 */
	public $WebClientVersion;

	/**
	 * Version identifier of Global-e API.
	 * @var string $APIVersion
	 * @access public
	 */
	public $APIVersion;


}