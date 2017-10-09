<?php
namespace GlobalE\SDK\API\Common;
use GlobalE\SDK\Models\Common;

/**
 * Class Attribute
 * @method getAttributeCode()
 * @method getName()
 * @method getAttributeTypeCode()
 * @method $this setAttributeCode($AttributeCode)
 * @method $this setName($Name)
 * @method $this setAttributeTypeCode($AttributeTypeCode)
 * @package GlobalE\SDK\API\Common
 */
class Attribute extends Common {

    /**
     * Custom attribute code which include the attribute types such as Size, Color, etc
     * @var string $AttributeCode
     * @access public
     */
    public $AttributeCode;

    /**
     * Attribute name
     * @var string $Name
     * @access public
     */
    public $Name;

    /**
     * Code used to identify the attribute type,
     * such as “size” for Size, “color” for Color, etc
     * @var string $AttributeTypeCode
     * @access public
     */
    public $AttributeTypeCode;

}