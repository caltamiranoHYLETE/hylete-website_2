<?php
namespace GlobalE\SDK\API\Common;
use GlobalE\SDK\Models\Common;

/**
 * Class OrderStatusReason
 * @method getOrderStatusReasonCode()
 * @method getName()
 * @method setOrderStatusReasonCode($OrderStatusReasonCode)
 * @method setName($Name)
 * @package GlobalE\SDK\API\Common
 */
class OrderStatusReason extends Common {

    /**
     * Order status reason code on the Merchant’s site (to be mapped on Global-e side)
     * @var string
     * @access public
     */
    public $OrderStatusReasonCode;

    /**
     * Order status reason name
     * @var string
     * @access public
     */
    public $Name;

}