<?php
namespace GlobalE\SDK\Models\Common;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Core\Settings;

/**
 * Class ApiParams
 * @method getUri()
 * @method getBody()
 * @method getPath()
 * @method $this setBody($Body)
 * @method $this setPath($Path)
 * @package GlobalE\SDK\Models\Common
 */
class ApiParams extends Common {

    /**
     * Post information which sent to the API service
     * @var array $Body
     * @access public
     */
    public $Body = array();
    /**
     * Uri parameters for the API service
     * @var array $Uri
     * @access public
     */
    public $Uri = array();

    /**
     * Path URI to the API service
     * @var string
     * @access public
     */
    public $Path;

    /**
     * Always will add to Uri merchantGUID from settings.
     * ApiParams constructor
     * @access public
     */
    public function __construct()
    {
        $this->setUri(array('merchantGUID' => Settings::get('MerchantGUID')));
    }

    /**
     * Set Uri parameters for the API service
     * @param array $Uri
     * @return ApiParams
     * @access public
     */
    public function setUri(array $Uri)
    {
        $this->Uri = array_merge($this->Uri, $Uri);
        return $this;
    }

}