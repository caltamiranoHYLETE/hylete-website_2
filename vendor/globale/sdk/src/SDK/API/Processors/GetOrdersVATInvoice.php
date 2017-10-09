<?php
namespace GlobalE\SDK\API\Processors;

use GlobalE\SDK\API;
use GlobalE\SDK\Models\Common;

/**
 * Global-e API method
 * @package GlobalE\SDK\API\Processors
 */
class GetOrdersVATInvoice extends API\Processor {

    /**
     * API path Order/GetOrdersVATInvoice
     * @var string
     * @access protected
     */
    protected $Path = 'Order/GetOrdersVATInvoice';

    /**
     * GetOrdersVATInvoice constructor.
     * @param Common\ApiParams $Params
     * @access public
     */
    public function __construct(Common\ApiParams $Params) {

        $this->setParams($Params);
    }

    /**
     * Create HTTP query URI from parameters in unconventional way, using the same GET parameter name
     * @param array $Uri
     * @return string
     * @access protected
     */
    protected function formatParameters(array $Uri)	{

        $OrderIdsList = $this->formatUrlArray($Uri, 'orderId');
        $urlString = parent::formatParameters($Uri);
        $urlString .= $OrderIdsList;

        return $urlString;
    }

    /**
     * No need to write the response to log from Order/GetOrdersVATInvoice
     * @param $response
     * @param $ExtraLogInfo
     * @access protected
     */
    protected function writeResponseToLog($response, $ExtraLogInfo){}

    /**
     * No need to decode response from Order/GetOrdersVATInvoice
     * @param $response
     * @return mixed
     * @access protected
     */
    protected function decodeResponseData($response){
        return $response;
    }

}