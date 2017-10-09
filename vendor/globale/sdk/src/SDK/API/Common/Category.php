<?php
namespace GlobalE\SDK\API\Common;
use GlobalE\SDK\Models\Common;

/**
 * Class Category
 * @method getCategoryCode()
 * @method getName()
 * @method setCategoryCode($CategoryCode)
 * @method setName($Name)
 * @package GlobalE\SDK\API\Common
 */
class Category extends Common {

    /**
     * Category code
     * @var string $CategoryCode
     * @access public
     */
    public $CategoryCode;

    /**
     * Category name
     * @var string $Name
     * @access public
     */
    public $Name;

}