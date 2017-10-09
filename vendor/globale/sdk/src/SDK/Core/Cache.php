<?php
namespace GlobalE\SDK\Core;

use \Monolog\Logger;
use \Stash\Pool;
use \Stash\Driver\FileSystem;
use \Stash\Driver\BlackHole;

/**
 * Class Cache
 * @package GlobalE\SDK\Core
 */
class Cache {

	/**
	 * Cache namespace
	 */
	const CACHE_NAMESPACE = 'globale';

	const CACHE_DELETE_TAGS_FLAG = 'globale_delete_by_tags';

	/**
	 * Cache instance object
	 * @var Cache
	 * @access protected
	 */
	protected static $cacheObj;

	/**
	 * External cache object, for cases when we get cache object from the extension.
	 * @var $externalCacheObj
	 * @access protected
	 */
	protected static $externalCacheObj;

	/**
	 * Cache library object
	 * @var Pool
	 * @access protected
	 */
	protected $pool;

	/**
	 * Cache constructor.
	 * @access public
	 */
	public function __construct()
	{
		$this->initPool();
	}

	/**
	 * Initialize cache pool
	 * @access public
	 */
	protected function initPool(){

		$type = Settings::get('Cache.Type');
		$driver = null;

		// If cache is disabled, change the type to null.
		if(Settings::get('Cache.Enable') !== true){
			$type = 'Null';
		}

		switch($type){
			case 'Magento1':
				if(empty(self::$externalCacheObj)){
					$msg = 'Selected cache type requires external cache object. Use setExternalCacheObject method.';
					Log::log($msg,Log::LEVEL_ERROR);
					throw new \Exception($msg);
				}
				$driver = new Cache\Magento(self::$externalCacheObj);
				break;
			case 'Magento2':
				if(empty(self::$externalCacheObj)){
					$msg = 'Selected cache type requires external cache object. Use setExternalCacheObject method.';
					Log::log($msg,Log::LEVEL_ERROR);
					throw new \Exception($msg);
				}
				$driver = self::$externalCacheObj;
				break;

			case 'Null':
				$driver = new BlackHole();
				break;
			case 'File':
			default:
				//@TODO ADD additional options if is needed
				$options = [
					'path'            => Settings::get('Cache.Path'),
				    'filePermissions' => Settings::get('Cache.FilePermissions'),
				    'dirPermissions'  => Settings::get('Cache.DirPermissions')
				];
				$driver = new FileSystem();
				$driver->setOptions($options);
		}

		$this->pool = new Pool($driver);
		$this->pool->setNamespace(self::CACHE_NAMESPACE);
	}

	/**
	 * Set Logger
	 * @param Logger $logger
	 * @access static public
	 */
	public static function setLogger(Logger $logger){
		if (!isset(self::$cacheObj)) {
			self::$cacheObj = new static();
		}
		self::$cacheObj->pool->setLogger($logger);
	}

	/**
	 * For cases when we get cache object from the extension.
	 * @param $externalCacheObj
	 */
	public static function setExternalCacheObject($externalCacheObj) {
		self::$externalCacheObj = $externalCacheObj;
		Log::log('Loaded external cache object into SDK: '.get_class($externalCacheObj),Log::LEVEL_DEBUG);
	}

	/**
	 * Set cache key
	 * @param $key string Cache key
	 * @param mixed $value Cache value
	 * @param int $expiration Int is time (seconds), DateTime a future expiration date
	 * @param array $tags Array of tags
	 * @return bool Returns whether the object was successfully stored or not.
	 * @access static public
	 */
	public static function set($key,$value,$expiration = null,$tags = array()){
		if (!isset(self::$cacheObj)) {
			self::$cacheObj = new static();
		}

		array_unshift($tags,$key);
		$item = self::$cacheObj->pool->getItem($tags);
		return $item->set($value,$expiration);
	}

	/**
	 * Get value by cache key
	 * @param string $key          Cache key
	 * @return bool|mixed|null
	 * @access static public
	 */
	public static function get($key){
		if (!isset(self::$cacheObj)) {
			self::$cacheObj = new static();
		}
		$item = self::$cacheObj->pool->getItem($key);
		if($item->isMiss()){
			$item->clear();
			return false;
		}
		$value = $item->get($key);
		return $value;
	}

	/**
	 * Empties the entire cache of all items keys !!!
	 * @access static public
	 */
	public static function flush(){
		if (!isset(self::$cacheObj)) {
			self::$cacheObj = new static();
		}
		return self::$cacheObj->pool->flush();
	}

	/**
	 * Clear cache Item by key
	 * @param string $key
	 * @return bool
	 * @access static public
	 */
	public static function clear($key){
		if (!isset(self::$cacheObj)) {
			self::$cacheObj = new static();
		}
		$item = self::$cacheObj->pool->getItem($key);
		return $item->clear();
	}

    /**
     * Clear cache Item by tags for cases the cache type is magento,
     * for other cases it will delete by key(no tags in Stash cache, more info here: PHPSDK-146)
     * @param string $tags
     * @return bool
     * @access static public
     */
    public static function clearTags($tags){

        if (!isset(self::$cacheObj)) {
            self::$cacheObj = new static();
        }

		// Will tell magento cache driver to delete by tags
        if(Settings::get('Cache.Type') === 'Magento'){
            array_unshift($tags,self::CACHE_DELETE_TAGS_FLAG);
		}

        $item = self::$cacheObj->pool->getItem($tags);
        return $item->clear();
    }
}