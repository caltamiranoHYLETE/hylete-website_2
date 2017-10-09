<?php
namespace GlobalE\SDK\API\Drivers;

/**
 * Interface for API Connection Drivers
 * @package GlobalE\SDK\API\Drivers
 */
interface DriverInterface
{
    /**
     * Send request to the API service
     * @param $Uri
     * @param $Body
     * @return mixed
     * @access public
     */
    public function sendRequest($Uri,$Body);

    /**
     * Parse the response headers
     * @param $response
     * @return mixed
     * @access public
     */
    public function parseHeaders(&$response);
}