<?php
namespace GlobalE\SDK\Controllers;

use GlobalE\SDK\Core;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common\Response;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\Request;
use GlobalE\SDK\API\Processors;

/**
 * Class Admin
 * Interface Methods
 * @package GlobalE\SDK\Controllers
 */
class Admin extends BaseController {

    /**
     * Send collection of products to the API service
     * @param Request\Product[] $Products
     * @return Response
     */
    protected function SaveProductsList(array $Products){

        $ModelAdmin = new Models\Admin();
        $ApiParams = $ModelAdmin->buildApiParamsForSaveProductsList($Products);

        $SaveProductsList = new Processors\SaveProductsList($ApiParams);
        $ResponseData = $SaveProductsList->processRequest();
        $ResponseData = $ResponseData[0];

        Core\Log::log('SaveProductsList succeeded', Core\Log::LEVEL_INFO, (array)$ResponseData);
        return new Response\Data(true, $ResponseData);
    }

    /**
     * Update the order status in the Global-e side
     * @param Request\OrderStatusDetails $OrderStatus
     * @return Response
     */
    protected function UpdateOrderStatus(Request\OrderStatusDetails $OrderStatus){

        $ApiParams = new Common\ApiParams();
        $ApiParams->setUri(array('orderStatus' => $OrderStatus));

        $UpdateOrderStatus = new Processors\UpdateOrderStatus($ApiParams);
        $ResponseData = $UpdateOrderStatus->processRequest();
        $ResponseData = $ResponseData[0];

        return new Response\Data($ResponseData->Success, $ResponseData->Reason);
    }

    /**
     * Updates order status and Delivery Quantities for the products,
     * as well as Merchant’s internal Delivery Reference Number if applicable.
     * Optionally may include the list of Parcels for this order shipment to Global-e hub.
     * @param Request\Product[] $Products
     * @param Request\OrderStatusDetails $OrderStatus
     * @param Request\Parcel[] $Parcels
     * @return Response
     */
    protected function UpdateOrderDispatch(array $Products, Request\OrderStatusDetails $OrderStatus, $Parcels = array()){

        $ApiParams = new Common\ApiParams();
        $ApiParams->setUri(array('orderStatus' => $OrderStatus,
            'parcelsList' => $Parcels));
        $ApiParams->setBody($Products);

        $UpdateOrderDispatch = new Processors\UpdateOrderDispatch($ApiParams);
        $ResponseData = $UpdateOrderDispatch->processRequest();
        $ResponseData = $ResponseData[0];

        return new Response\Data($ResponseData->Success, $ResponseData->Reason);
    }

    /**
     * Marks the relevant parcel/s within a specific order as dispatched from merchant to Global-e hub
     * and updates Delivery Quantities for the products included in each parcel.
     * @param String $OrderId
     * @param Request\ParcelProduct[] $Parcels
     * @return Response
     */
    protected function UpdateParcelDispatch($OrderId, $Parcels = array()){

        $ApiParams = new Common\ApiParams();
        $ApiParams->setBody(array('OrderId' => $OrderId, 'Parcels' => $Parcels));

        $UpdateParcelDispatch = new Processors\UpdateParcelDispatch($ApiParams);
        $ResponseData = $UpdateParcelDispatch->processRequest();
        $ResponseData = $ResponseData[0];

        return new Response\Data($ResponseData->Success, $ResponseData->Reason);
    }

    /**
     * Flush the cache
     * @return Response
     */
    protected function ClearGECache(){

        $ClearGECache = Core\Cache::flush();
        return new Response($ClearGECache);
    }

    /**
     * Get barcode URL by order id
	 * @deprecated Please Use GetBarCodeUrl instead
     * @param string $OrderId
     * @return Response\Data
     */
    protected function GetBarCode($OrderId){

       return $this->GetBarCodeUrl($OrderId);
    }

	/**
	 * Get barcode URL by order id
	 * @param string $OrderId
	 * @return Response\Data
	 */
    protected function GetBarCodeUrl($OrderId){

		$AppModel = new Models\App();
		$BaseBarCodeUrl = $AppModel->getAppSetting('AppSettings.ServerSettings.BarcodeGeneratorURL.Value');

		$MerchantID = Core\Settings::get('MerchantID');

		$BarCodeUrl = str_replace(array('%MERCHANTID%','%CODE%'), array($MerchantID,$OrderId), $BaseBarCodeUrl);

		return new Response\Data(true, $BarCodeUrl);
	}

    /**
     * Get order invoice PDF by order id
     * @param array $OrderIds
     * @return Response
     */
    protected function GetOrderInvoice(array $OrderIds){

        $ApiParams = new Common\ApiParams();
        $ApiParams->setUri(array('orderId' => $OrderIds));

        $UpdateOrderStatus = new Processors\GetOrdersVatInvoice($ApiParams);
        $ResponseData = $UpdateOrderStatus->processRequest();
        $ResponseData = $ResponseData[0];

        $AdminModel = new Models\Admin();
        $Invoice = new Common\Invoice();
        $Invoice->setHeaders($AdminModel->getOrderInvoiceHeaders(strlen($ResponseData)));
        $Invoice->setBody($ResponseData);
        $response = new Response\Data(true, $Invoice);

        return $response;
    }
}