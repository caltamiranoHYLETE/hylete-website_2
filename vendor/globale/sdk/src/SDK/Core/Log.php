<?php
namespace GlobalE\SDK\Core;

use \Monolog\Logger;
use \Monolog\Handler\RotatingFileHandler;
use \Monolog\Handler\NullHandler;
use \GlobalE\SDK\Core\Log\ProfilerLogger;

/**
 * Class Log
 * @package GlobalE\SDK\Core
 */
class Log
{
    /**
     * Logger name
     */
    const DEFAULT_LOGGER_NAME = 'GlobaleSDK';
    /**
     * Logger file name
     */
    const LOG_FILE_NAME = 'globale.log';

    //Logger Levels

    /**
     * EMERGENCY level
     */
    const LEVEL_EMERGENCY = Logger::EMERGENCY;
    /**
     * ALERT level
     */
    const LEVEL_ALERT = Logger::ALERT;
    /**
     * CRITICAL level
     */
    const LEVEL_CRITICAL = Logger::CRITICAL;
    /**
     * ERROR level
     */
    const LEVEL_ERROR = Logger::ERROR;
    /**
     * WARNING level
     */
    const LEVEL_WARNING = Logger::WARNING;
    /**
     * NOTICE level
     */
    const LEVEL_NOTICE = Logger::NOTICE;
    /**
     * INFO level
     */
    const LEVEL_INFO = Logger::INFO;
    /**
     * DEBUG level
     */
    const LEVEL_DEBUG = Logger::DEBUG;


    /**
     * Log object instance object
     * @var ProfilerLogger
     * @access protected
     */
    protected $logObject = null;

    /**
     * Logger library object
     * @var Log
     * @access protected
     */
    protected static $logger;

    /**
     * Log constructor.
     * @access public
     */
    public function __construct()
    {
        $this->initLogObject();
    }

    /**
     * Initialize LogObject by SDK settings
     * @access protected
     */
    protected function initLogObject()
    {
        $this->logObject = new ProfilerLogger(self::DEFAULT_LOGGER_NAME);

        $handler = Settings::get('Log.Type');

        // If log is disabled, change the handler to null.
        if(Settings::get('Log.Enable') !== true){
            $handler = 'Null';
        }

        switch($handler){
            case 'Magento':
                $this->logObject->pushHandler(
                    new Log\MagentoHandler(Settings::get('Log.Level'))
                );
                break;
            case 'Null':
                $this->logObject->pushHandler(
                    new NullHandler()
                );
                break;
            case 'File':
            default:
                $this->logObject->pushHandler(
                    new RotatingFileHandler(
                        Settings::get('Log.Path').DIRECTORY_SEPARATOR.self::LOG_FILE_NAME,
                        Settings::get('Log.MaxFilesAmount'),
                        Settings::get('Log.Level')
                    )
                );
                break;
        }
    }

	/**
	 * Adds a log record at an arbitrary level.
	 * @param string $message
	 * @param string $level
	 * @param array $context
	 * @access static public
	 * @access static public
	 */
	public static function log($message,$level,array $context = array())
	{
		$message = self::maskGUID($message);

		if (!isset(self::$logger)) {
			self::$logger = new static();
		}
		try {
		    self::$logger->logObject->log($level,$message,$context);
	    }catch (\Exception $e) {
            // if log not writable, we do not write anything and do not generate error
        }
    }

    /**
     * Log record for EMERGENCY level.
     * @param array $message
     * @param array $context
     * @access static public
     */
    public static function emergency($message,array $context = array())
    {
        if (!isset(self::$logger)) {
            self::$logger = new static();
        }
        self::$logger->logObject->emergency($message,$context);
    }

    /**
     * Log record for ALERT level.
     * @param array $message
     * @param array $context
     * @access static public
     */
    public static function alert($message,array $context = array())
    {
        if (!isset(self::$logger)) {
            self::$logger = new static();
        }
        self::$logger->logObject->alert($message,$context);
    }

    /**
     * Log record for CRITICAL  level.
     * @param string $message
     * @param array $context
     * @access static public
     */
    public static function critical($message,array $context = array())
    {
        if (!isset(self::$logger)) {
            self::$logger = new static();
        }
        self::$logger->logObject->critical($message,$context);
    }

    /**
     * Log record for ERROR level.
     * @param string $message
     * @param array $context
     * @access static public
     */
    public static function error($message,array $context = array())
    {
        if (!isset(self::$logger)) {
            self::$logger = new static();
        }
        self::$logger->logObject->error($message,$context);
    }

    /**
     * Log record for Warning level.
     * @param string $message
     * @param array $context
     * @access static public
     */
    public static function warning($message,array $context = array())
    {
        if (!isset(self::$logger)) {
            self::$logger = new static();
        }
        self::$logger->logObject->warning($message,$context);
    }

    /**
     * Log record for NOTICE level.
     * @param string $message
     * @param array $context
     * @access static public
     */
    public static function notice($message,array $context = array())
    {
        if (!isset(self::$logger)) {
            self::$logger = new static();
        }
        self::$logger->logObject->notice($message,$context);
    }

    /**
     * Log record for INFO level.
     * @param string $message
     * @param array $context
     * @access static public
     */
    public static function info($message,array $context = array())
    {
        if (!isset(self::$logger)) {
            self::$logger = new static();
        }
        self::$logger->logObject->info($message,$context);
    }

    /**
     * Log record for DEBUG level.
     * @param string $message
     * @param array $context
     * @access static public
     */
    public static function debug($message,array $context = array())
    {
        if (!isset(self::$logger)) {
            self::$logger = new static();
        }
        self::$logger->logObject->debug($message,$context);
    }

    /**
     * Get Logger object for external usage
     * @return ProfilerLogger
     * @access static public
     */
    public static function getLogObject(){
        if (!isset(self::$logger)) {
            self::$logger = new static();
        }
        return self::$logger->logObject;
    }

    static protected function maskGUID($message){
        $guid = Settings::get('MerchantGUID');
        return str_replace( substr($guid, 0, -12), 'xxxxxxxx-xxxx-xxxx-xxxx-', $message );
    }
}