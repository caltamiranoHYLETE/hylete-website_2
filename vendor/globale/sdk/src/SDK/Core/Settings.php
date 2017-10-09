<?php
namespace GlobalE\SDK\Core;

use GlobalE\SDK\Models;
use \Weew\Config\ConfigLoader;

/**
 * Class Settings
 * @package GlobalE\SDK\Core
 */
class Settings {

    /**
     * Path to settings development environment
     */
    const CONFIG_FILE_PATH_DEV         = '../Settings_dev.php';

    /**
     * Path to settings test environment
     */
    const CONFIG_FILE_PATH_TEST    = '../Settings_test.php';

    /**
     * Path to settings staging environment
     */
    const CONFIG_FILE_PATH_STAGING = '../Settings_staging.php';

    /**
     * Path to settings production environment
     */
    const CONFIG_FILE_PATH_PROD    = '../Settings.php';

    /**
     * Global SDK settings instance.
     * @var \Weew\Config\Config
     * @access protected
     */
    protected $settingsObj;

    /**
     * Settings array
     * @var Settings
     * @access static protected
     */
    protected static $settings;

    /**
     * Settings constructor.
     * @access private
     */
    private function __construct(){
        $this->initSettingsObj();
    }

    /**
     * Initialize settings object
     * @access private
     */
    private function initSettingsObj(){

        // Load all environment paths
        $ConfigEnvironments = $this->getConfigEnvironments();
        $ConfigLoader = new ConfigLoader(null,$ConfigEnvironments);

        // Set the environment to use
        $ConfigLoader->setEnvironment($this->getEnvironment());

        // load the config file path of the environment for using
        $EnvironmentsPath = $ConfigLoader->getPaths();
        $ConfigLoader->setPaths(array($EnvironmentsPath[$this->getEnvironment()]));
        $this->settingsObj = $ConfigLoader->load();
    }

    /**
     * Get the current environment
     * @return string
     * @access private
     */
    private function getEnvironment() {
        return (defined('GlobalE_ENV')) ? GlobalE_ENV : 'prod';
    }

    /**
     * Get key
     * @param string $key
     * @param null $default
     * @return mixed
     * @access public
     */
    public static function get($key, $default = null){
        if (!isset(self::$settings)) {
            self::$settings = new static();
        }
        return self::$settings->settingsObj->get($key,$default);
    }

    /**
     * Set key
     * @param string $key
     * @param mixed $value
     * @return mixed
     * @access static public
     */
    public static function set($key, $value){
        if (!isset(self::$settings)) {
            self::$settings = new static();
        }
        $result = self::$settings->settingsObj->set($key,$value);
		return $result;
    }


    /**
     * Add bulk of keys into settings by provided key name
     * @param mixed $Keys
     * @param string $MasterKey
     * @throws \Exception
     * @access static public
     */
    public static function setBulk($Keys, $MasterKey){
        if (!isset(self::$settings)) {
            self::$settings = new static();
        }
        if(gettype($Keys) == 'object') {
            $Keys = Models\Json::decode(Models\Json::encode($Keys), true);
        }
        foreach ($Keys as $KeyName => $Key) {
            // insert bulk of keys as Arrays into configurations
            if($MasterKey){
                self::$settings->settingsObj->merge(array($MasterKey =>array($KeyName => $Key)));
            }
            else{
                self::$settings->settingsObj->merge(array($KeyName => $Key));
            }
        }
    }



    /**
     * Get environments collection
     * @return array
     * @access private
     */
    private function getConfigEnvironments(){

        return $ConfigEnvironments = array(
                    'dev'     => __DIR__ . DIRECTORY_SEPARATOR.self::CONFIG_FILE_PATH_DEV,
                    'test'    => __DIR__ . DIRECTORY_SEPARATOR.self::CONFIG_FILE_PATH_TEST,
                    'staging' => __DIR__ . DIRECTORY_SEPARATOR.self::CONFIG_FILE_PATH_STAGING,
                    'prod'    => __DIR__ . DIRECTORY_SEPARATOR.self::CONFIG_FILE_PATH_PROD
                );
    }
}