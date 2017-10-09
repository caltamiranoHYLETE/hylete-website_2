<?php
namespace GlobalE\SDK\Core\Log;

use \Monolog\Logger;
use \Profiler\Logger\ProfilerLoggerInterface;
use \Psr\Log\LogLevel;

/**
 * Class ProfilerLogger
 * @package GlobalE\SDK\Core\Log
 */
class ProfilerLogger extends Logger implements ProfilerLoggerInterface {

    /**
     * The array of logs
     * @var array
     * @access protected
     */
    protected $logs = array();

    /**
     * The array of queries
     * @var array
     * @access protected
     */
    protected $queries = array();

	/**
	 * Log a query statement
	 * @param string $query
	 * @param int $time
	 * @return array
	 */
    public function query($query, $time = 0)
    {
        $result = compact('query', 'time');
    	$this->queries[] = $result;
		return $result;
    }

	/**
	 * Detailed debug information.
	 * @param string $value
	 * @param array $context
	 * @return bool
	 */
    public function debug($value, array $context = array())
    {
        $result = parent::debug($value,$context);
        // If we were given anything other than a string,
        // we'll get readable format of the value.
        if( ! is_string($value))
        {
            $value = print_r($value, true);
        }

        $this->log(LogLevel::DEBUG, $value, $context);
		return $result;
    }

	/**
	 * Logs with an arbitrary level.
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 * @return bool
	 */
    public function log($level, $message, array $context = array())
    {
        $result = parent::log($level, $message, $context);
        $this->logs[] = compact('level', 'message', 'context');
		return $result;
    }

    /**
     * Retrieve the queries.
     * @return array
     * @access public
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * Retrieve logs for the matching level.
     * @param  string $level
     * @return array
     * @access public
     */
    public function getLogs($level = null)
    {
        // If no level was given, return all logs.
        if(is_null($level))
        {
            return $this->logs;
        }

        else
        {
            if(isset($this->logs[$level]))
            {
                return $this->logs[$level];
            }
        }
        return array();
    }
}