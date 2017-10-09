<?php
namespace GlobalE\SDK\API\Drivers;


use GlobalE\SDK\Core;

/**
 * Class Stub
 * Simple connector which get responses from files (used mostly for tests)
 * @package GlobalE\SDK\API\Drivers
 */
class Stub implements DriverInterface{

    /**
     * Connection type
     */
    const CONNECTION_TYPE = 'Stub';
    /**
     * Stub folder name
     */
    const STUB_FOLDER = 'stubs';
    /**
     * Stub file extension
     */
    const FILE_EXT = '.json';
    /**
     * shorten directory separator constant
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * Simulate a send request to the API service, and get response from files
     * @param $Uri
     * @param $Body
     * @return bool|mixed
     * @access public
     */
    public function sendRequest($Uri,$Body){

        $path = str_replace(Core\Settings::get('API.BaseUrl'),'',$Uri );
        $path = str_replace('/',self::DS,$path );
        $path = substr($path,0,strpos($path,'?'));
        $file = __DIR__.'/../../../../'.self::DS.self::STUB_FOLDER.self::DS.$path.self::FILE_EXT;

        if(!file_exists($file)){
            return false;
        }
        $data =  file_get_contents($file);
        $response = str_replace(PHP_EOL, '', $data);
        return $response;
    }

    /**
     * Get the (TTL) time to live from settings
     * @param $response
     * @return mixed
     * @access public
     */
    public function parseHeaders(&$response){
        $ttl = Core\Settings::get('API.StubTTL');
        return $ttl;
    }
}