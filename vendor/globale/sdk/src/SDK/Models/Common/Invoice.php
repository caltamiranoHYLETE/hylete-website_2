<?php
namespace GlobalE\SDK\Models\Common;

use GlobalE\SDK\Models\Common;

/**
 * Class Invoice
 * @method getHeaders()
 * @method getBody()
 * @method $this setHeaders($Headers)
 * @method $this setBody($Body)
 * @package GlobalE\SDK\Models\Common
 */
class Invoice extends Common
{
    /**
     * Headers of the invoice page
     * @var array
     * @access public
     */
    public $Headers;

    /**
     * Body of the invoice page
     * @var string
     * @access public
     */
    public $Body;
}