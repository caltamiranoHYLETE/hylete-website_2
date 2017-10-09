<?php
namespace GlobalE\SDK\API\Drivers;

use GlobalE\SDK\Core;

/**
 * Class Curl
 * @package GlobalE\SDK\API\Drivers
 */
class Curl implements DriverInterface {

    /**
     * HTTP code for successful response
     */
    const HTTP_STATUS_OK = 200;
    /**
     * Connection type
     */
    const CONNECTION_TYPE = 'Curl';

    /**
     * Send request to the API service
     * @param $Uri
     * @param $Body
     * @return mixed
     * @throws \Exception
     * @access public
     */
    public function sendRequest($Uri, $Body) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Uri);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Core\Settings::get('API.Timeout'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Body);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-length: ".strlen($Body)));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $HttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if(curl_error($ch)){
            curl_close($ch);
            $msg = 'Curl error: '.curl_error($ch);
            Core\Log::log($msg,Core\Log::LEVEL_CRITICAL);
            throw new \Exception($msg);
        }
        elseif ($HttpCode !== self::HTTP_STATUS_OK){
            curl_close($ch);
            $msg = 'Http status is different from 200: '.$HttpCode;
            Core\Log::log($msg,Core\Log::LEVEL_ERROR,array($Uri,$Body,$response));
            throw new \Exception($msg);
        }
        curl_close($ch);
        return $response;
    }

    /**
     * Parse the response headers
     * @param $response
     * @return int|mixed
     * @access public
     */
    public function parseHeaders(&$response){

        $parts = explode("\r\n\r\nHTTP/", $response);
        $parts = (count($parts) > 1 ? 'HTTP/' : '').array_pop($parts);
        list($headers, $body) = explode("\r\n\r\n", $parts, 2);

        if(preg_match('/Cache-Control:\ (.*)(max-age=(\d+))(.*)/',$headers,$m)){
            $ttl = (int)$m[3];
        }
        else{
            $ttl = null;
        }

        // Remove the headers from the response body
        $response = $body;

        return $ttl;
    }
}