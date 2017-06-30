<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
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
 * @package     Vaimo_IntegrationBaseStandard
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Kjell Holmqvist <kjell.holmqvist@vaimo.com>
 */


class Vaimo_IntegrationBaseStandard_Model_Import_Shipments extends Vaimo_IntegrationBaseStandard_Model_Import_Abstract
{
    protected $_logFile = 'standard_import_shipments.log';

    /**
     * @param array $tracks
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    protected function _addTracks(array $tracks, $shipment)
    {
        foreach ($tracks as $row) {
            /** @var Mage_Sales_Model_Order_Shipment_Track $track */
            $track = Mage::getModel('sales/order_shipment_track');

            if (isset($row['track_number']) && ($row['track_number'])) {
                $track->setTrackNumber($row['track_number']);
            }

            if (isset($row['carrier_code']) && $row['carrier_code']) {
                $track->setCarrierCode($row['carrier_code']);
            }

            if (isset($row['description']) && $row['description']) {
                $track->setDescription($row['description']);
            }

            if (isset($row['title']) && $row['title']) {
                $track->setTitle($row['description']);
            }

            if (isset($row['qty']) && $row['qty']) {
                $track->setQty($row['qty']);
            }

            if (isset($row['weight']) && $row['weight']) {
                $track->setWeight($row['weight']);
            }

            $shipment->addTrack($track);
        }

        return true;
    }

    /**
     * @param array $items
     * @param array $tracks
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function _createShipment($items, $tracks, $order)
    {
        if (!$order->canShip()) {
            Mage::throwException('Cannot do shipment');
        }

        $qtyData = array();

        foreach ($items as $item) {
            if (!isset($item['sku'])) {
                Mage::throwException('Product sku missed');
            }

            $sku = $item['sku'];
            $qty = $item['qty'];

            $matchFound = false;

            /** @var Mage_Sales_Model_Order_Item $item */
            foreach ($order->getAllItems() as $orderItem) {
                if ($orderItem->getSku() == $sku) {
                    $qtyData[$orderItem->getItemId()] += $qty;
                    $matchFound = true;
                    break;
                }
            }

            if (!$matchFound) {
                Mage::throwException('Product not found on order: ' . $sku);
            }
        }
        /** @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($qtyData);

        if (!$shipment) {
            Mage::throwException('Failed to create shipment');
        }

        $this->_addTracks($tracks, $shipment);

        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);

        Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        //Send shipment email to customer
        $shipment->sendEmail()->setEmailSent(true);
        $shipment->save();

        return true;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _createInvoice($order)
    {
        $result = array();

        $qtyData = array();
        $totalQty = 0;

        /** Mage_Sales_Model_Order_Item $item */
        foreach ($order->getAllItems() as $item) {
            $qty = $item->getQtyShipped() - $item->getQtyInvoiced();
            if ($qty < 0) {
                $qty = 0;
            }
            $qtyData[$item->getItemId()] = $qty;
            $totalQty += $qty;
        }

        if (!$totalQty) {
            Mage::throwException('Invoice cannot be created, nothing is shipped');
        }

        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($qtyData);

        if (!$invoice) {
            Mage::throwException('Failed to create invoice');
        }

        if (!$invoice->getTotalQty()) {
            Mage::throwException('Cannot create an invoice without products');
        }

        $invoice->register();
        $invoice->setEmailSent(true);
        $invoice->getOrder()->setCustomerNoteNotify(true);
        $invoice->getOrder()->setIsInProcess(true);

        Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

        try {
            $invoice->sendEmail();
        } catch (Exception $e) {
            $result[] = 'Unable to send the invoice email';
        }

        try {
            if ($invoice->canCapture()) {
                $invoice->capture();
                $invoice->getOrder()->setIsInProcess(true);

                Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
            }
        } catch (Exception $e) {
            $result[] = 'Error capturing invoice: ' . $e->getMessage();
        }

        if ($order->canCancel()) {
            $order->cancel();
            $order->save();
        }

        return $result;
    }

    /**
     * @param array $shipmentData
     */
    protected function _importRows($shipmentData)
    {
        $result = array();

        if (isset($shipmentData['integrationbase']['shipment'])) {
            $shipmentData['integrationbase'] = array($shipmentData['integrationbase']['shipment']);
        }

        foreach ($shipmentData['integrationbase'] as $shipment) {
            $isInvoiced       = (isset($shipment['invoiced']) && $shipment['invoiced'] == 'yes');
            $orderIncrementId = (isset($shipment['order_increment_id'])) ? $shipment['order_increment_id'] : null;

            $this->_log('Order Increment Id:' . $orderIncrementId);

            if ($isInvoiced) {
                $this->_log('Invoiced: Yes');
            }

            if (isset($shipment['shipment_items']['shipment_item'])) {
                $items = array($shipment['shipment_items']['shipment_item']);
            } else {
                $items = isset($shipment['shipment_items']) ? $shipment['shipment_items'] : array();
            }

            if (isset($shipment['shipment_tracks']['shipment_track'])) {
                $tracks = array($shipment['shipment_tracks']['shipment_track']);
            } else {
                $tracks = isset($shipment['shipment_tracks']) ? $shipment['shipment_tracks'] : array();
            }

            /** @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($orderIncrementId);

            if (!$order->getId()) {
                Mage::throwException('Order not found');
            }

            $this->_createShipment($items, $tracks, $order);

            if ($isInvoiced) {
                $result = array_merge($result, $this->_createInvoice($order));
            }

            $this->_successCount++;
        }

        return $result;
    }

    public function import($filename)
    {
        $this->_log('Reading file: ' . $filename);
        $this->_log('');

        $shipmentData = Mage::getSingleton('integrationbasestandard/xml_parser')->parseToArray($filename);
        $result = $this->_importRows($shipmentData);

        $this->_log(implode("\n", $result));

        $this->_log('');
    }
}