<?php
namespace GlobalE\SDK\Models;
use GlobalE\SDK\Core;


/**
 * Class Culture, handle culture code mapping between platform and Global-e
 * @package GlobalE\SDK\Models
 */
class Culture {

    /**
     * Get the Global-e culture code by platfom culture code
     * @return string
     */
    public static function getGlobaleCulture($CultureCode = null) {

        $CultureMap = self::getCultureMap();
        if(isset($CultureMap[$CultureCode])) {
            return $CultureMap[$CultureCode];
        }else{
            // return the given culture code
            return $CultureCode;
        }
    }

    /**
     * Map culture code from platform to Global-e
     * @return array
     * @access public
     */
    public static function getCultureMap()
    {
        return Core\Settings::get('Culture');
    }
}