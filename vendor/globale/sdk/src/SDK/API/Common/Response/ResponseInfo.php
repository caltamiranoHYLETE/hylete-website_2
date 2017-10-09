<?php
namespace GlobalE\SDK\API\Common\Response;

use GlobalE\SDK\Models\Common;

/**
 * Class UpdateOrderDispatch
 * @method getSuccess()
 * @method getReason()
 * @method setSuccess($Success)
 * @method setReason($Reason)
 * @package GlobalE\SDK\API\Common\Response
 */
class ResponseInfo extends Common {


    /**
     * @var array $Success
     * @access public
     */
    public $Success;

    /**
     * @var array $Reason
     * @access public
     */
    public $Reason;
}