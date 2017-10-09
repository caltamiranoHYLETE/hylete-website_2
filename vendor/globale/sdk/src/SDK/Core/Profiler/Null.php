<?php
namespace GlobalE\SDK\Core\Profiler;

/**
 * Class Null
 * Empty class methods in order to disable the profiler functionality
 * @package GlobalE\SDK\Core\Profiler
 */
class Null {

    /**
     * Do not needed to add new timer
     * @param string $timer
     * @param int $startTime
     * @return bool
     * @access static public
     */
    public static function startTimer($timer, $startTime = null){
        return false;
    }

    /**
     * Do not needed to close the timer
     * @param string $timer
     * @param int $endTime
     * @return bool
     * @access static public
     */
    public static function endTimer($timer, $endTime = null){
        return false;
    }

    /**
     * Do not needed to render the profiler
     * @param bool $echo
     * @return bool
     * @access static public
     */
    public static function render($echo = false){
        return false;
    }
}
