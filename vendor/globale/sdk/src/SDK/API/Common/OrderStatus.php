<?php
namespace GlobalE\SDK\API\Common;
use GlobalE\SDK\Models\Common;

/**
 * Class OrderStatus
 * @method getOrderStatusCode()
 * @method getName()
 * @method setOrderStatusCode($OrderStatusCode)
 * @method setName($Name)
 * @package GlobalE\SDK\API\Common
 */
class OrderStatus extends Common {

    /**
     * Order status code on the Merchant’s site (to be mapped on Global-e side)
     * @var string
     * @access public
     */
    public $OrderStatusCode;

    /**
     * Order status name
     * @var string
     * @access public
     */
    public $Name;
}