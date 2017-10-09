<?php
namespace GlobalE\SDK\Models\Common;

use GlobalE\SDK\Models\Common\Response;

/**
 * Interface iHandleAction
 * @package GlobalE\SDK\Models\Common
 */
interface iHandleAction {

    /**
     * Callback action method to execute
     * @param $Request
     * @return Response\Order
     * @access public
     */
    public function handleAction($Request);

}