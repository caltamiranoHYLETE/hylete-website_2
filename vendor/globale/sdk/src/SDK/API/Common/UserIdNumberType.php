<?php
namespace GlobalE\SDK\API\Common;
use GlobalE\SDK\Models\Common;

/**
 * Class UserIdNumberType
 * @method getUserIdNumberTypeCode()
 * @method getName()
 * @method setUserIdNumberTypeCode($UserIdNumberTypeCode)
 * @method setName($Name)
 * @package GlobalE\SDK\API\Common
 */
class UserIdNumberType extends Common {

    /**
     * Identification document type (e.g. Passport, ID card, etc.)
     * @var string $UserIdNumberTypeCode
     * @access public
     */
    public $UserIdNumberTypeCode;

    /**
     * Identification document type name
     * @var string $Name
     * @access public
     */
    public $Name;
}

