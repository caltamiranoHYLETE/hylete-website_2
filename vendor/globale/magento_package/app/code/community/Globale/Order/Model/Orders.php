<?php

use GlobalE\SDK\API\Common;
use GlobalE\SDK\Models\Common\Request;

/**
 * Class Globale_Order_Model_Orders
 */
class Globale_Order_Model_Orders extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('globale_order/orders'); // this is location of the resource file.
    }

    /**
     * Save Order in DB, in globale_orders
     * @param $IncrementId
     * @param object $Request
     */
    public function saveOrder($IncrementId, $Request) {

        $this->setOrderId($IncrementId);
        $this->setGlobaleOrderId($Request->OrderId);
        $this->setOrderStatus(0);
        $this->setCreatedTime(strtotime('now'));
        $this->setUpdateTime(strtotime('now'));
        $this->save();
    }

	/**
	 * update Global-e order data to status=1 (success) so the order will be locked.
	 */
    public function saveSuccessOrder(){

        $this->setOrderStatus(1);
        $this->setUpdateTime(strtotime('now'));
        $this->save();
    }
    
    public function reArrangeRequest($Request){

        $arr = array();
        $BaseSubTotalIncl = 0;
        $SubTotalIncl = 0;
        foreach ($Request->Products as $Product) {
            $arr[$Product->CartItemId] = $Product;
            $BaseSubTotalIncl += $Product->Price * $Product->Quantity;
            $SubTotalIncl += $Product->InternationalPrice * $Product->Quantity;
        }
        $Request->Products = $arr;
        $Request->BaseSubTotalIncl = $BaseSubTotalIncl;
        $Request->SubTotalIncl = $SubTotalIncl;

        return $Request;

    }

    /**
     * @param $TotalAmount
     * @param $TotalDiscountAmount
     * @param $Price
     * @param $Qty
     * @return mixed
     */
    public function getProductDiscount($TotalAmount,$TotalDiscountAmount,$Price, $Qty){

        // Percent of product price from total price
        $PercentPrice = $Price * $Qty / $TotalAmount;
        $DiscountAmount = $TotalDiscountAmount * $PercentPrice;
        return $DiscountAmount;
    }

    /**
     * Send an UpdateStatus call to Global-e API.
     * @param $Order
	 * @param string $Status
     */
    public function sendUpdateStatusRequest($Order, $Status = null) {

        $GlobaleOrder = Mage::getModel('globale_order/orders')->load($Order->getIncrementId(),'order_id');

        if(empty($Status)){
			$Status = $Order->getStatus();
		}

        if($GlobaleOrder->hasData()){
            $OrderStatusDetails = new Request\OrderStatusDetails();
            $OrderStatusDetails->setOrderId($GlobaleOrder->getGlobaleOrderId());

            $OrderStatus = new Common\OrderStatus();
            $OrderStatus->setName($Status);
            $OrderStatus->setOrderStatusCode($Status);

            $OrderStatusDetails->setOrderStatus($OrderStatus);

            /** @var GlobalE\SDK\SDK $GlobaleSDK */
            $GlobaleSDK = Mage::registry('globale_sdk');
            $GlobaleSDK->Admin()->UpdateOrderStatus($OrderStatusDetails);
        }
    }

}