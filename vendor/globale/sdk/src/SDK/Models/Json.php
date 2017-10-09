<?php
namespace GlobalE\SDK\Models;

use GlobalE\SDK\Core;

/**
 * Class Json
 * @package GlobalE\SDK\Models
 */
class Json{

    /**
     * Decode Json string to an stdClass
     * @param string $Json
     * @param bool $Assoc
     * @return \stdClass
     * @throws \Exception
     * @access public
     */
    public static function decode($Json, $Assoc = false){
        $Json = json_decode($Json, $Assoc);

        $Error = self::getLastError();
        if(!empty($Error)){
            $Msg = 'Error while decoding Json: '.$Error;
            Core\Log::log($Msg, Core\Log::LEVEL_ERROR);
            throw new \Exception($Msg);
        }

        return $Json;
    }

    /**
     * Encode object into string json
     * @param string $Json
     * @return string
     * @throws \Exception
     * @access public
     */
    public static function encode($Json){
        $Json = json_encode($Json);

        $Error = self::getLastError();
        if(!empty($Error)){
            $Msg = 'Error while encoding Json with big int: '.$Error;
            Core\Log::log($Msg, Core\Log::LEVEL_ERROR);
            throw new \Exception($Msg);
        }

        return $Json;
    }

    /**
     * Find errors in string json
     * @return string
     * @access public
     */
    public static function getLastError(){
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return '';
                break;
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                return 'Unknown error';
                break;
        }
    }

    /**
     * Decode json with big int
     * @param string $Json
     * @param bool $Assoc
     * @return object
     * @throws \Exception
     * @access public
     */
    public static function decodeWithBigInt($Json, $Assoc = false){
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) { // if on PHP 5.4 or newer, use JSON "bigint" option
            $Obj = json_decode ($Json, $Assoc, 512, JSON_BIGINT_AS_STRING);
        } else { // otherwise try workaround (convert number to string first)
            $JsonCleared = preg_replace ('/:\s?(\d{14,})/', ': "${1}"', $Json);
            $Obj = json_decode ($JsonCleared, $Assoc);
        }

        $Error = self::getLastError();
        if(!empty($Error)){
            $Msg = 'Error while decoding Json with big int: '.$Error;
            Core\Log::log($Msg, Core\Log::LEVEL_ERROR);
            throw new \Exception($Msg);
        }

        return $Obj;
    }

    /**
     * Gets JSON and common object and returns array of commons with properties from JSON.
     * @param string $ResponseJson
     * @param object $ObjectResponse
     * @param bool $DecodeBigInt
     * @return object[]
     */
    public static function decodeToCommons($ResponseJson,$ObjectResponse,$DecodeBigInt = false){

        $DecodedResponse = $DecodeBigInt ?  Json::decodeWithBigInt($ResponseJson) : Json::decode($ResponseJson);

        // Response have an array of commons or a common.
        // This method should return array of commons either way.

		//Will use decodeWithBigInt with $Assoc = false
		// - we want use stdClass structure and not Array structure way in Common object properties
        if(is_array($DecodedResponse)){
            $ResponseArray = $DecodeBigInt ?  Json::decodeWithBigInt($ResponseJson, false) : Json::decode($ResponseJson, false);
        }
        else{
            $ResponseArray = $DecodeBigInt ?  Json::decodeWithBigInt($ResponseJson, false) : Json::decode($ResponseJson, false);
            $ResponseArray = array($ResponseArray);
        }

        $CommonsResponse = array();
        foreach ($ResponseArray as $Response){

            $CommonResponse = clone $ObjectResponse;
            foreach ($Response as $key => $value){
                $CommonResponse->{"set$key"}($value);
            }

            $CommonsResponse[] = $CommonResponse;
        }

        return $CommonsResponse;
    }
}