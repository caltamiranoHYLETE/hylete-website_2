<?php
namespace GlobalE\SDK\Models\Common\Request;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\API\Common\OrderStatusReason;
use GlobalE\SDK\API\Common\OrderStatus;

/**
 * Class OrderStatusDetails
 * @method getOrderId()
 * @method getOrderStatus()
 * @method getOrderStatusReason()
 * @method getOrderComments()
 * @method getConfirmationNumber()
 * @method geTrackingServiceName()
 * @method getTrackingNumber()
 * @method getTrackingURL()
 * @method getDeliveryReferenceNumber()
 * @method getTrackingServiceSite()
 * @method setOrderId($OrderId)
 * @method setOrderStatus($OrderStatus)
 * @method setOrderStatusReason($OrderStatusReason)
 * @method setOrderComments($OrderComments)
 * @method setConfirmationNumber($ConfirmationNumber)
 * @method seTrackingServiceName($TrackingServiceName)
 * @method setTrackingNumber($TrackingNumber)
 * @method setTrackingURL($TrackingURL)
 * @method setDeliveryReferenceNumber($DeliveryReferenceNumber)
 * @method setTrackingServiceSite($TrackingServiceSite)
 * @package GlobalE\SDK\Models\Common
 */
class OrderStatusDetails extends Common {

    /**
     * Global-e order unique identifier (previously submitted to the merchant’s SendOrderToMerchant method defined
     * below in this document when an order had been created with Global-e checkout)
     * @var string
     * @access public
     */
    public $OrderId;

    /**
     * Order status code
     * @var OrderStatus
     * @access public
     */
    public $OrderStatus;

    /**
     * Order status reason
     * @var OrderStatusReason
     * @access public
     */
    public $OrderStatusReason;

    /**
     * Merchant's comments for the order
     * @var string
     * @access public
     */
    public $OrderComments;
    /**
     * Merchant's order confirmation number
     * @var string
     * @access public
     */
    public $ConfirmationNumber;
    /**
     * Name of the tracking service used by the merchant for this order
     * @var string
     * @access public
     */
    public $TrackingServiceName;
    /**
     * Reference number valid for the tracking service used by the merchant for this order
     * @var string
     * @access public
     */
    public $TrackingNumber;
    /**
     * Full tracking URL on the tracking service site used by the merchant (if specified overrides all other “Tracking…” properties)
     * @var string
     * @access public
     */
    public $TrackingURL;
    /**
     * Merchant’s internal delivery Reference Number for this order
     * @var string
     * @access public
     */
    public $DeliveryReferenceNumber;
    /**
     * URL of the tracking service site used by the Merchant for this order
     * @var string
     * @access public
     */
    public $TrackingServiceSite;
}