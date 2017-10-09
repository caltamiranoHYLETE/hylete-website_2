<?php
namespace GlobalE\SDK\API\Processors;

use GlobalE\SDK\API;
use GlobalE\SDK\Models\Common;

/**
 * Global-e API method
 * @package GlobalE\SDK\API\Processors
 */
class AppVersion extends API\Processor {

    /**
     * API path Browsing/AppVersion
     * @var string
     * @access protected
     */
    protected $Path = 'Browsing/AppVersion';

    /**
     * Name of the common class API should return.
     * @var string
     */
    protected $ObjectResponse = "AppVersion";

    /**
     * AppVersion constructor.
     * @param Common\ApiParams $Params
     * @access public
     */
    public function __construct(Common\ApiParams $Params) {

        $this->setParams($Params);
    }
}
