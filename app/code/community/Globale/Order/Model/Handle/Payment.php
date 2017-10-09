<?php

use GlobalE\SDK\Models\Common\Response;
use GlobalE\SDK\Models\Common\iHandleAction;

/**
 * Class Globale_Browsing_Model_Conversion
 */
class Globale_Order_Model_Handle_Payment extends Mage_Core_Model_Abstract implements iHandleAction
{

    /**
     * @param $Request
     * @return Response\Order
     */
    public function handleAction($Request){

        /** @var Mage_Sales_Model_Order $Order */
        $Order = Mage::getSingleton('sales/order')->loadByIncrementId($Request->MerchantOrderId);

        if($Order->getId() === null){
            return new Response\Order(false, 'Order not found.', $Request->MerchantOrderId);
        }

        if(!$Order->canInvoice()) {
            return new Response\Order(false, "Can't create invoice.", $Request->MerchantOrderId);
        }

        $Invoice = Mage::getModel('sales/service_order', $Order)->prepareInvoice();
        $Invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $Invoice->register();
        $TransactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($Invoice)
            ->addObject($Invoice->getOrder());
        $TransactionSave->save();
        $Invoice->save();

        $StateCode = Mage_Sales_Model_Order::STATE_PROCESSING;

        //Manually changing the order status
        //Get the payment status from Global-e settings configuration or by default from order model
        $Settings = Mage::getModel('globale_base/settings');
        $PaymentStatus = $Settings->getInternationalPaymentStatus();
        if(empty($PaymentStatus)){
            $PaymentStatus = true;
        }
        $Order->setState($StateCode, $PaymentStatus);
        $Order->save();

        /** @var Globale_Order_Model_Payment $Payment */
        $Payment = Mage::getModel('globale_order/payment')->load($Order->getIncrementId(), 'order_id');
        $Payment->savePayment($Request);

        return new Response\Order(true, null, $Request->MerchantOrderId, $Request->MerchantOrderId);
    }
}

