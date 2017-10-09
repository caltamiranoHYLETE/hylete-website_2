<?php
namespace GlobalE\SDK\API\Common\Response;

use GlobalE\SDK\Models\Common;

/**
 * Class AppSettings
 * @method getClientSettings()
 * @method getServerSettings()
 * @method setClientSettings($ClientSettings)
 * @method setServerSettings($ServerSettings)
 * @package GlobalE\SDK\API\Common\Response
 */
class AppSettings extends Common {

	/**
	 * @var string $ClientSettings
	 * @access public
	 */
	public $ClientSettings;

	/**
	 * @var string $ServerSettings
	 * @access public
	 */
	public $ServerSettings;


}