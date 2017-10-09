<?php
namespace GlobalE\SDK\Controllers;

use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common\Response;

/**
 * Class BaseController
 * Interface Methods
 * @package GlobalE\SDK\Controllers
 */
abstract class BaseController {

    /**
     * Get public method enabled/disabled by settings
     * @return boolean
     * @throws \Exception
     * @access public
     */
    public function IsEnabled(){

        $class_array = explode('\\', get_class($this));
        $class = end($class_array);

        switch ($class){
            case 'SDK' :
                return Core\Settings::get('EnableGlobalESDK');
                break;
            case 'Browsing' :
                return Core\Settings::get('Controllers.Browsing');
                break;
            case 'Checkout' :
                return Core\Settings::get('Controllers.Checkout');
                break;
            case 'Merchant' :
                return Core\Settings::get('Controllers.Merchant');
                break;
            case 'Admin' :
                return Core\Settings::get('Controllers.Admin');
                break;
            default :
                throw new \Exception("There is no Settings declaring class {$class} is Enabled or not.");
                break;
        }
    }

    /**
     * This method will run profiler timer before and after action,
     * and validate with the Validator the input arguments.
     * @param $Method
     * @param $Arguments
     * @return \GlobalE\SDK\Models\Common\Response
     */
    public function __call($Method,$Arguments) {

        $Interface = get_class($this);
        $Interface = str_replace(__NAMESPACE__.'\\', '', $Interface); // Remove namespace name from class name
        if(!method_exists($this, $Method)) {
            $Response = new Response(false, "There is no action $Method in interface ". $Interface);
        }

        if(empty($Response) && Core\Settings::get('Validator.Enable') !== false){
            Core\Profiler::startTimer("$Interface->$Method Validator");
            try{
                Core\Validator::Validate($Interface, $Method, $Arguments);
            }
            catch (\Exception $e){
                $Response = new Response(false, $e->getMessage());
            }
            Core\Profiler::endTimer("$Interface->$Method Validator");
        }

        if(empty($Response)){
            Core\Profiler::startTimer("$Interface->$Method Run");
            try{
                $Response = call_user_func_array(array($this,$Method),$Arguments);
            }
            catch(\Exception $e) {
                $Response = new Response(false, $e->getMessage());
            }
            Core\Profiler::endTimer("$Interface->$Method Run");
        }

        return $Response;
    }

    /**
     * Get class methods
     * @return array
     * @access public
     */
    public function Describe(){
        return get_class_methods(get_class($this));
    }

}