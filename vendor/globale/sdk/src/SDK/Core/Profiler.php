<?php
namespace GlobalE\SDK\Core;

use \Profiler\Profiler as ProfilerLoicSharma;

/**
 * Class Profiler
 * @package GlobalE\SDK\Core
 */
class Profiler {
    

    /**
     * Profiler instance object
     * @var ProfilerLoicSharma
     * @access protected
     */
    protected $profilerObj;

    /**
     * Profiler library object
     * @var Profiler
     * @access protected
     */
    protected static $profiler;

    /**
     * Profiler constructor.
     * @access private
     */
    private function __construct(){
        $this->initProfilerObj();
    }
    
    private function initProfilerObj(){

        if(Settings::get('Profiler.Enable') === true){
            $this->profilerObj = new ProfilerLoicSharma(Log::getLogObject());
        }
        else{
            $this->profilerObj = new Profiler\Null;
        }
    }

    /**
     * Start new timer
     * @param string $timer
     * @param float $startTime
     * @return mixed
     * @access static public
     */
    public static function startTimer($timer, $startTime = null){
        if (!isset(self::$profiler)) {
            self::$profiler = new static();
        }
        return self::$profiler->profilerObj->startTimer($timer,$startTime);
    }

    /**
     * Close the timer
     * @param string $timer
     * @param float $endTime
     * @return mixed
     * @access static public
     */
    public static function endTimer($timer, $endTime = null){
        if (!isset(self::$profiler)) {
            self::$profiler = new static();
        }
        return self::$profiler->profilerObj->endTimer($timer,$endTime);
    }

    /**
     * Render profiler
     * @param bool $echo
     * @return mixed
     * @access static public
     */
    public static function render($echo = false){
        if (!isset(self::$profiler)) {
            self::$profiler = new static();
        }

        if($echo){
            echo self::$profiler->profilerObj->render();
        }
        return self::$profiler->profilerObj->render();
    }
}