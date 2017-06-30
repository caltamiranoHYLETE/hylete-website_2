<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Icommerce_PdfCustomiser
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Vaimo AB Team
 */

require_once Mage::getModuleDir('controllers', 'Icommerce_EmailAttachments') . DS . 'Adminhtml/EmailAttachments/OrderController.php';

class Icommerce_PdfCustomiser_Adminhtml_PdfCustomiser_OrderController extends Icommerce_EmailAttachments_Adminhtml_EmailAttachments_OrderController
{
    public function pdfinvoicesAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (sizeof($orderIds)) {
            $pdf = Mage::getModel('pdfcustomiser/invoice')->getPdf(null, $orderIds);
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    public function pdfshipmentsAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (sizeof($orderIds)) {
            $pdf = Mage::getModel('pdfcustomiser/shipment')->getPdf(null, $orderIds);
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    public function pdfcreditmemosAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (sizeof($orderIds)) {
            $pdf = Mage::getModel('pdfcustomiser/creditmemo')->getPdf(null, $orderIds);
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    public function pdfdocsAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (sizeof($orderIds)) {
            $pdf = Mage::getModel('pdfcustomiser/invoice')->getPdf(null, $orderIds, null, true);
            $pdf = Mage::getModel('pdfcustomiser/shipment')->getPdf(null, $orderIds, $pdf, true);
            $pdf = Mage::getModel('pdfcustomiser/order')->getPdf(null, $orderIds, $pdf, true);
            $pdf = Mage::getModel('pdfcustomiser/creditmemo')->getPdf(null, $orderIds, $pdf, false, 'orderDocs_');
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    public function pdfordersAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (sizeof($orderIds)) {
            $pdf = Mage::getModel('pdfcustomiser/order')->getPdf(null, $orderIds);
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    public function pdfpickingAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (sizeof($orderIds)) {
            $pdf = Mage::getModel('pdfcustomiser/order')->getPicking(null, $orderIds);
        } else {
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    public function printAction()
    {
        if ($orderId = $this->getRequest()->getParam('order_id')) {
            if ($order = Mage::getModel('sales/order')->load($orderId)) {
                $pdf = Mage::getModel('pdfcustomiser/order')->getPdf(null, array($orderId));
            }
        } else {
            $this->_forward('noRoute');
        }
    }
}