<?php

require_once BP.'/app/code/core/Mage/Adminhtml/controllers/Sales/ShipmentController.php';

class Icommerce_EmailAttachments_Adminhtml_EmailAttachments_ShipmentController extends Mage_Adminhtml_Sales_ShipmentController
{
    public function pdfshipmentsAction()
    {
        $shipmentIds = $this->getRequest()->getPost('shipment_ids');
        $orderIds = array();

        foreach($shipmentIds as $shipmentId)
        {
            $shipment     = Mage::getModel('sales/order_shipment')->load($shipmentId);
            $order_id     = $shipment->getData('order_id');
            array_push($orderIds, $order_id);
        }
        $pdf = Mage::getModel('pdfcustomiser/shipment')->getPdf(null,$orderIds,null, false);
        $this->_redirect('*/*/');
    }

    public function printAction()
    {
        $shipmentId   = $this->getRequest()->getParam('invoice_id');
        $shipment     = Mage::getModel('sales/order_shipment')->load($shipmentId);
        $orderIds[0]  = $shipment->getData('order_id');
        $pdf = Mage::getModel('pdfcustomiser/shipment')->getPdf(null,$orderIds,null, false);
        $this->_redirect('*/*/');
    }


}