<?php
namespace GlobalE\SDK\Core\Log;

use Monolog\Logger;
use Monolog\Handler;

/**
 * Class MagentoHandler
 * Handler for magento logger, will use \Mage::log to write logs.
 * @package GlobalE\SDK\Core\Log
 */
class MagentoHandler extends Handler\AbstractHandler
{

    const FILE_LOG_PATH = "globale\\globale_[date].log";
    
    /**
     * @param int $level The minimum logging level at which this handler will be triggered
     */
    public function __construct($level = Logger::DEBUG)
    {
        parent::__construct($level, false);
    }

    /**
     * Handles log records.
     * This method is called each time somebody tries to write to log and has magento as his log type in settings.
     * @param array $record record that we need to write to log
     * @return bool
     */
    public function handle(array $record)
    {
        if ($record['level'] < $this->level) {
            return false;
        }

        $msg = $record['message'].json_encode($record['context']);
        \Mage::log($msg,$this->convertLogLevelForMagento($record['level']),$this->getFileLogPath());

        return true;
    }

    /**
     * Will return file log path for today
     * @return string
     */
    private function getFileLogPath(){
        return str_replace('[date]', date('Y-m-d'), self::FILE_LOG_PATH);
    }

    /**
     * Converts sdk log level to magento log level:
     *
     * Level Name | SDK | Magento
     * DEBUG      | 100 | 7
     * INFO       | 200 | 6
     * NOTICE     | 250 | 5
     * WARNING    | 300 | 4
     * ERROR      | 400 | 3
     * CRITICAL   | 500 | 2
     * ALERT      | 550 | 1
     * EMERGENCY  | 600 | 0
     *
     * @param $level
     * @return int
     */
    private function convertLogLevelForMagento($level){
       $map_levels = array(600 => 0, 550 => 1, 500 => 2, 400 => 3,
                            300 => 4, 250 => 5, 200 => 6, 100 => 7);
       return($map_levels[$level]);
    }

}