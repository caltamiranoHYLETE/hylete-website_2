<?php

class Icommerce_EmailAttachments_Adminhtml_EmailAttachments_InvoiceController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Additional initialization
     *
     */
    protected function _construct()
    {
        $this->setUsedModuleName('Mage_Sales');
    }

    public function pdfinvoicesAction()
    {
        $invoiceIds = $this->getRequest()->getPost('invoice_ids');
        $orderIds = array();

        foreach($invoiceIds as $invoiceId)
        {

            $invoice     = Mage::getModel('sales/order_invoice')->load($invoiceId);
            $order_id     = $invoice->getData('order_id');
            array_push($orderIds, $order_id);
        }
        $pdf = Mage::getModel('pdfcustomiser/invoice')->getPdf(null,$orderIds,null, false);
        $this->_redirect('*/*/');
    }

    public function printAction()
    {
        $invoiceId   = $this->getRequest()->getParam('invoice_id');
        $invoice     = Mage::getModel('sales/order_invoice')->load($invoiceId);
        $orderIds[0]  = $invoice->getData('order_id');
        $pdf = Mage::getModel('pdfcustomiser/invoice')->getPdf(null,$orderIds,null, false);
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/invoice');
    }
}