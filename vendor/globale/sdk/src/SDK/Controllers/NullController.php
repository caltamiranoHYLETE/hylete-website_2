<?php
namespace GlobalE\SDK\Controllers;

use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common\Response;

/**
 * Class NullController
 * Will be used when user tries to access disabled controller
 * @package GlobalE\SDK\Controllers
 */
class NullController {

    /**
     * Name of the controller this Null object is replacing
     * @var
     */
    private $controller_name;

    /**
     * Null constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->controller_name = $name;
    }

    /**
     * Magic method that will replace all actions in disabled controller.
     * @param $name
     * @param $arguments
     * @return Response
     */
    public function __call($name, $arguments)
    {
        $msg = $this->controller_name.' controller is NOT enabled in settings.';
        Core\Log::log($msg, Core\Log::LEVEL_WARNING);
        return new Response(false, $msg);
    }

    /**
     * Magic method that will replace all static actions in disabled controller.
     * @param $name
     * @param $arguments
     * @return Response
     */
    public static function __callStatic($name, $arguments)
    {
        $msg = 'Controller you called is NOT enabled in settings.';
        Core\Log::log($msg, Core\Log::LEVEL_WARNING);
        return new Response(false, $msg);
    }
}