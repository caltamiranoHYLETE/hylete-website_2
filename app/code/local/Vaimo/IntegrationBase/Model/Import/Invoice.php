<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Import_Invoice extends Vaimo_IntegrationBase_Model_Import_Abstract
{
    protected $_eventPrefix = 'invoice';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/invoice', 'Invoice import');
        $this->_successMessage = '%d invoice(s) imported';
        $this->_failureMessage = '%d invoice(s) failed to import';
    }

    /**
     * @param Vaimo_IntegrationBase_Model_Invoice $baseInvoice
     * @return bool
     */
    protected function _importRecord($baseInvoice)
    {
        $this->_log('Order: ' . $baseInvoice->getIncrementId());

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($baseInvoice->getIncrementId());

        if (!$order->getId()) {
            Mage::throwException(Mage::helper('integrationbase')->__('Order not found'));
        }

        if (!$order->canInvoice()) {
            Mage::throwException(Mage::helper('integrationbase')->__('Cannot do Invoice'));
        }

        $qtyData = array();

        /** @var Vaimo_IntegrationBase_Model_Invoice_Item $baseItem */
        foreach ($baseInvoice->getAllItems() as $baseItem) {
            $matchFound = false;

            /** @var Mage_Sales_Model_Order_Item $item */
            foreach ($order->getAllItems() as $item) {
                if ($item->getSku() == $baseItem->getSku()) {
                    $qtyData[$item->getItemId()] += $baseItem->getQty();
                    $matchFound = true;
                    break;
                }
            }

            if (!$matchFound) {
                Mage::throwException(Mage::helper('integrationbase')->__('Product %s not found on order', $baseItem->getSku()));
            }
        }

        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($qtyData);

        if (!$invoice->getTotalQty()) {
            Mage::throwException(Mage::helper('integrationbase')->__('Cannot create an invoice without products.'));
        }

        if ($baseInvoice->getCaptureCase()) {
            $invoice->setRequestedCaptureCase($baseInvoice->getCaptureCase());
        }

//        if (!empty($data['comment_text'])) {
//            $invoice->addComment(
//                $data['comment_text'],
//                isset($data['comment_customer_notify']),
//                isset($data['is_visible_on_front'])
//            );
//        }

        $invoice->register();

        if ($baseInvoice->getSendEmail()) {
            $invoice->setEmailSent(true);
        }

        $invoice->getOrder()->setCustomerNoteNotify($baseInvoice->getSendEmail());
        $invoice->getOrder()->setIsInProcess(true);

        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());

        if ($baseInvoice->getDoShipment() || (int) $invoice->getOrder()->getForcedDoShipmentWithInvoice()) {
            $shipment = Mage::getModel('sales/service_order', $invoice->getOrder())->prepareShipment($qtyData);
            if ($shipment->getTotalQty()) {
                $shipment->setEmailSent($invoice->getEmailSent());
                $transactionSave->addObject($shipment);
            }
        }

        $transactionSave->save();

        // send invoice/shipment emails
//        $comment = '';
//        if (isset($data['comment_customer_notify'])) {
//            $comment = $data['comment_text'];
//        }
//        try {
//            $invoice->sendEmail(!empty($data['send_email']), $comment);
//        } catch (Exception $e) {
//            Mage::logException($e);
//            $this->_getSession()->addError($this->__('Unable to send the invoice email.'));
//        }
//        if ($shipment) {
//            try {
//                $shipment->sendEmail(!empty($data['send_email']));
//            } catch (Exception $e) {
//                Mage::logException($e);
//                $this->_getSession()->addError($this->__('Unable to send the shipment email.'));
//            }
//        }


        Mage::dispatchEvent('integrationbase_import_invoice', array(
            'invoice' => $invoice,
            'base_invoice' => $baseInvoice,
        ));

        $this->_log('');

        return $this;
    }

    protected function _deleteRecord($item)
    {
        Mage::throwException('Invoice import does not support delete');
    }
}