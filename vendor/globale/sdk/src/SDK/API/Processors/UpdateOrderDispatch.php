<?php
namespace GlobalE\SDK\API\Processors;

use GlobalE\SDK\API;
use GlobalE\SDK\Models\Common;

/**
 * Global-e API method
 * @package GlobalE\SDK\API\Processors
 */
class UpdateOrderDispatch extends API\Processor {

    protected $Path = 'Order/UpdateOrderDispatch';

    /**
     * Name of the common class API should return.
     * @var string
     */
    protected $ObjectResponse = "ResponseInfo";

    /**
     * UpdateOrderDispatch constructor.
     * @param Common\ApiParams $Params
     * @access public
     */
    public function __construct(Common\ApiParams $Params) {

        $this->setParams($Params);
        $this->setUseCache(false);
    }

    /**
     * Format parameters to string URL in order to send to the API service
     * Sending array of Parcels Global-E way: &parcelsList={"ParcelCode":"1"}&parcelsList={"ParcelCode":"2"}...
     * @param array $Uri
     * @return string
     * @access protected
     */
    protected function formatParameters(array $Uri)	{

        $ParcelsList = $this->formatUrlArray($Uri, 'parcelsList', true);
        $urlString = parent::formatParameters($Uri);
        $urlString .= $ParcelsList;

        return $urlString;
    }

}