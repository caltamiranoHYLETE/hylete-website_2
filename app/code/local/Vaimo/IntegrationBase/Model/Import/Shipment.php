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
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Import_Shipment extends Vaimo_IntegrationBase_Model_Import_Abstract
{
    protected $_eventPrefix = 'shipment';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/shipment', 'Shipment import');
        $this->_successMessage = '%d shipment(s) imported';
        $this->_failureMessage = '%d shipment(s) failed to import';
    }

    /**
     * @param Vaimo_IntegrationBase_Model_Shipment $baseShipment
     * @return bool
     */
    protected function _importRecord($baseShipment)
    {
        $this->_log('Order: ' . $baseShipment->getIncrementId());

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($baseShipment->getIncrementId());

        if (!$order->getId()) {
            Mage::throwException(Mage::helper('integrationbase')->__('Order not found'));
        }

        if (!$order->canShip()) {
            Mage::throwException(Mage::helper('integrationbase')->__('Cannot do shipment'));
        }

        $qtyData = array();

        /** @var Vaimo_IntegrationBase_Model_Shipment_Item $baseItem */
        foreach ($baseShipment->getAllItems() as $baseItem) {
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

        /** @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($qtyData);

        if (!$shipment) {
            Mage::throwException(Mage::helper('integrationbase')->__('Failed to create shipment'));
        }

        foreach ($baseShipment->getAllTracks() as $baseTrack) {
            /** @var Mage_Sales_Model_Order_Shipment_Track $track */
            $track = Mage::getModel('sales/order_shipment_track');
            $track->addData($baseTrack->getTrackData());
            $shipment->addTrack($track);
        }

        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        $this->_log('Shipment: ' . $shipment->getIncrementId());

        //Send shipment email to customer
        $shipment->sendEmail()->setEmailSent(true);
        $shipment->save();

        Mage::dispatchEvent('integrationbase_import_shipment', array(
            'shipment' => $shipment,
            'base_shipment' => $baseShipment,
        ));

        $this->_log('');

        return $this;
    }

    protected function _deleteRecord($item)
    {
        Mage::throwException('Shipment import does not support delete');
    }
}