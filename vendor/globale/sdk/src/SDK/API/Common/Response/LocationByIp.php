<?php
namespace GlobalE\SDK\API\Common\Response;

use GlobalE\SDK\Models\Common;

/**
 * Class LocationByIp
 * @method getCountry()
 * @method getRegion()
 * @method getCity()
 * @method getIPRange()
 * @method setCountry($Country)
 * @method setRegion($Region)
 * @method setCity($City)
 * @method setIPRange($IPRange)
 * @package GlobalE\SDK\API\Common\Response
 */
class LocationByIp extends Common {


    /**
     * @var \stdClass $Country
     * @access public
     */
    public $Country;

    /**
     * @var array $Region
     * @access public
     */
    public $Region;

    /**
     * @var \stdClass $City
     * @access public
     */
    public $City;

    /**
     * @var \stdClass $IPRange
     * @access public
     */
    public $IPRange;
}