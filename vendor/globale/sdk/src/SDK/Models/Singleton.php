<?php
namespace GlobalE\SDK\Models;

/**
 * Class Singleton
 * @package GlobalE\SDK\Models
 */
abstract class Singleton {

    /**
     * Singleton object
     * @var array
     * @access private
     */
    private static $instances = array();

    /**
     * Get the singleton collection objects
     * @param array $params
     * @return array
     * @access public
     */
    public static function getSingleton($params = array()) {
        $class = get_called_class();
        if (array_key_exists($class, self::$instances) === false){
            self::$instances[$class] = new $class($params);
        }
        return self::$instances[$class];
    }

    /**
     * Singleton constructor.
     * @param array $params
     * @throws \Exception
     * @access final private
     */
    final private function __construct(array $params) {
        $class = get_called_class();
        if (array_key_exists($class, self::$instances)){
            throw new \Exception('An instance of '. $class .' already exists !');
        }

        static::initialize($params); //In PHP 5.3
    }

    /**
     * Clone magic method to extend
     * @access final private
     */
    final private function __clone() { }

    /**
     * @desc Initialize abstract method to extend
     * @param array $params
     * @access abstract protected
     */
    abstract protected function initialize(array $params);
}