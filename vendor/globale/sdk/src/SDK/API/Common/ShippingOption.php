<?php
namespace GlobalE\SDK\API\Common;
use GlobalE\SDK\Models\Common;

/**
 * Class ShippingOption
 * @method getCarrier()
 * @method getCarrierTitle()
 * @method getCarrierName()
 * @method getCode()
 * @method getMethod()
 * @method getMethodTitle()
 * @method getMethodDescription()
 * @method getPrice()
 * @method setCarrier($Carrier)
 * @method setCarrierTitle($CarrierTitle)
 * @method setCarrierName($CarrierName)
 * @method setCode($Code)
 * @method setMethod($Method)
 * @method setMethodTitle($MethodTitle)
 * @method setMethodDescription($MethodDescription)
 * @method setPrice($Price)
 * @package GlobalE\SDK\API\Common
 */
class ShippingOption extends Common {

    /**
     * Shipping carrier
     * @var string
     * @access public
     */
    public $Carrier;

    /**
     * Shipping carrier title
     * @var string
     * @access public
     */
    public $CarrierTitle;
    /**
     * Shipping carrier name
     * @var string
     * @access public
     */
    public $CarrierName;
    /**
     * Shipping code
     * @var string
     * @access public
     */
    public $Code;
    /**
     * Shipping method
     * @var string
     * @access public
     */
    public $Method;
    /**
     * Shipping method title
     * @var string
     * @access public
     */
    public $MethodTitle;
    /**
     * Shipping method description
     * @var string
     * @access public
     */
    public $MethodDescription;
    /**
     * Shipping price
     * @var float
     * @access public
     */
    public $Price;
}