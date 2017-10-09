<?php
namespace GlobalE\SDK\API\Processors;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\API\Processor;

/**
 * Global-e API method
 * @package GlobalE\SDK\API\Processors
 */
class SendCartV1 extends Processor
{

    /**
     * API path Checkout/SendCart
     * @var string
     * @access protected
     */
    protected $Path = 'Checkout/SendCart';

    /**
     * Name of the common class API should return.
     * @var string
     */
    protected $ObjectResponse = "SendCart";

    /**
     * SendCart constructor.
     * @param Common\ApiParams $Params
     * @access public
     */
    public function __construct(Common\ApiParams $Params) {

        $this->setParams($Params);
    }
}