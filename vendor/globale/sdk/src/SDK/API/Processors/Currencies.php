<?php
namespace GlobalE\SDK\API\Processors;

use GlobalE\SDK\API;
use GlobalE\SDK\Models\Common;

/**
 * Global-e API method
 * @package GlobalE\SDK\API\Processors
 */
class Currencies extends API\Processor {

    /**
     * API path Browsing/Currencies
     * @var string
     * @access protected
     */
    protected $Path = 'Browsing/Currencies';

    /**
     * Name of the common class API should return.
     * @var string
     */
    protected $ObjectResponse = "Currency";

    /**
     * Currencies constructor.
     * @param Common\ApiParams $Params
     * @access public
     */
    public function __construct(Common\ApiParams $Params) {

        $this->setParams($Params);
    }
}
