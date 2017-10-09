<?php
namespace GlobalE\SDK\Models\Common\Request;

use GlobalE\SDK\Models\Common;

/**
 * Class Parcel
 * @method getParcelCode()
 * @method setParcelCode($ParcelCode)
 * @method getProducts()
 * @method setProducts($Products)
 * @package GlobalE\SDK\Models\Common
 */
class Parcel extends Common {

    /**
     * Package parcel code
     * @var string
     */
    public $ParcelCode;

    /**
     * @var array
     */
    public $Products;

}