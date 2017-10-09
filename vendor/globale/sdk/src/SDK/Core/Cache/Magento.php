<?php
namespace GlobalE\SDK\Core\Cache;

use GlobalE\SDK\Core;
use Stash\Interfaces\DriverInterface;
use Varien_Cache_Core;

/**
 * Class Magento
 * Implements the Stash cache driver interface, works as driver for Magento cache.
 * @package GlobalE\SDK\Core\Cache
 */
class Magento implements DriverInterface {

    /**
     * Holds magento cache object.
     * @var Varien_Cache_Core $cacheObj
     * @access private
     */
    private $cacheObj;

    /**
     * Magento cache adapter constructor method.
     * @param Varien_Cache_Core $cacheObj
     */
    public function __construct(Varien_Cache_Core $cacheObj)
    {
        $this->cacheObj = $cacheObj;
    }

    /**
     * This is a tricky method, it handles 2 cases from SDK\Core\Cache:
     *  1. Somebody tried to delete specific key.
     *  2. Somebody tries to delete by tags.
     * @param mixed $key
     * @return bool
     */
    public function clear($key = null)
    {

        // Clear all sdk cache by namespace if no additional keys/tags are given.
        if(!is_array($key) || count($key) <= 2){
            return $this->cacheObj->clean('matchingAnyTag',array(Core\Cache::CACHE_NAMESPACE));
        }

        // In $key[0]: sp/cache
        // In $key[1]: namespace (in our case always globale)
        // In $key[2]: key
        // In $key[>2]: tags
        unset($key[0],$key[1]);

        $tags = $key;
        $key = $key[2];

        // Checks if we need to delete by key or tags
        if($key === Core\Cache::CACHE_DELETE_TAGS_FLAG){
            return $this->cacheObj->clean('matchingAnyTag',$tags);
        }
        else{
            return $this->cacheObj->remove($key);
        }
    }

    /**
     * This method will be called each time somebody tries to get data from cache by key, and his cache type is Magento.
     * @param array $key has the cache key and tags
     * @return array
     */
    public function getData($key)
    {
        $key = $key[2];
        $data = $this->cacheObj->load($key);
        $data = unserialize($data);
        $result['data'] = $data;
        $result['expiration'] = 1854158707; // timestamp for year 2028, there is no need to pass real date
        if($data === false) {
            return false;
        }
        return $result;
    }
    
    /**
     * This method will be called each time somebody tries to set data to cache, and his cache type is Magento.
     * @param array $key
     * @param mixed $data
     * @param int $expiration - linux timestamp
     * @return bool
     */
    public function storeData($key, $data, $expiration)
    {
        $tags = $key;
        $key = $key[2];
        $data = serialize($data);

        // convert linux timestamp (time of expiration) to absolute time in second that Magento make usage - lifetime.
		$StartTime = time() ;
		$expiration = abs($expiration - $StartTime );

        return $this->cacheObj->save($data, $key, $tags, $expiration);
    }

    /**
     * Checks if cache is enabled in our settings,
     * this method is required by the interface.
     * @return bool
     */
    public static function isAvailable()
    {
        return Core\Settings::get('Cache.Enable');
    }

    /**
     * Magento cache handles expired cache by himself
     * @return bool
     */
    public function purge()
    {
        return false;
    }

    /**
     * No options need to pass to magento cache
     * @param array $options
     * @return bool
     */
    public function setOptions(array $options = array())
    {
        return false;
    }

}
