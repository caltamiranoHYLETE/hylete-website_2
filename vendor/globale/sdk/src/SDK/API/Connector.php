<?php
namespace GlobalE\SDK\API;

use GlobalE\SDK\Core\Settings;
use GlobalE\SDK\API\Drivers;

/**
 * Class Connector
 * @package GlobalE\SDK\API
 */
class Connector {

    /**
     * Connector type
     * @var Drivers\DriverInterface
     * @access protected
     */
    protected $Driver;

    /**
     * Connection driver instance
     * @var Connector
     * @access protected
     */
    protected static $Instance;

    /**
     * Connector constructor.
     * @access private
     */
    private function __construct(){
        $this->initDriverObj();
    }

    /**
     * Initialize the connection driver by type from settings
     * @access private
     */
    private function initDriverObj(){

        switch (Settings::get('API.ConnectionType')){
            case Drivers\Stub::CONNECTION_TYPE:
                $this->setDriver(new Drivers\Stub());
                break;
            default:
                $this->setDriver(new Drivers\Curl());
                break;
        }
    }

    /**
     * Send request to the API service
     * @param $Uri
     * @param $Body
     * @return mixed
     * @access static public
     */
    public static function sendRequest($Uri, $Body){
        if (!isset(self::$Instance)) {
            self::$Instance = new static();
        }

        return self::$Instance->Driver->sendRequest($Uri,$Body);
    }

    /**
     * Parse the response headers
     * @param $response
     * @return mixed
     * @access static public
     */
    public static function parseHeaders(&$response){
        if (!isset(self::$Instance)) {
            self::$Instance = new static();
        }

        return self::$Instance->Driver->parseHeaders($response);
    }

    /**
     * Set the connection driver
     * @param Drivers\DriverInterface $Driver
     * @return Connector
     * @access public
     */
    public function setDriver(Drivers\DriverInterface $Driver)
    {
        $this->Driver = $Driver;
        return $this;
    }

    /**
     * Get connection driver
     * @return Drivers\DriverInterface
     */
    public function getDriver()
    {
        return $this->Driver;
    }
}