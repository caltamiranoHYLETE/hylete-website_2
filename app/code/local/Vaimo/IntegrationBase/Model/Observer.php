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
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Observer
{
    /**
     * Adds entity to Queue
     *
     * @param string $entityTypeCode
     * @param Mage_Core_Model_Abstract $entity
     */
    protected function _addEntityToQueue($entityTypeCode, $entity)
    {
        if (Mage::helper('integrationbase')->canAddEntityToQueue($entityTypeCode, $entity)) {
            /** @var $queue Vaimo_IntegrationBase_Model_Queue */
            $queue = Mage::getModel('integrationbase/queue');
            $queue->setEntityTypeCode($entityTypeCode);
            $queue->setEntityId($entity->getId());
            $queue->save();
        }
    }

    /**
     * Orders get added to export queue of successful payment
     *
     * @param Varien_Event_Observer $observer
     */
    public function addOrderToQueue(Varien_Event_Observer $observer)
    {
        if ($order = $observer->getEvent()->getOrder()) {
            $this->_addEntityToQueue('order', $order);
        }
    }

    public function addM2EProOrderToQueue(Varien_Event_Observer $observer)
    {
        // the order in observer is not the magento but m2epro order
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof Ess_M2ePro_Model_Order) {
            $magentoOrderId = $order->getMagentoOrder()->getRealOrderId();
            $magentoOrder = Mage::getModel('sales/order')
                    ->getCollection()
                    ->addAttributeToFilter('increment_id', $magentoOrderId)
                    ->getFirstItem();

            $this->_addEntityToQueue('order', $magentoOrder);
        }else{
            $this->_addEntityToQueue('order', $order);
        }
    }

    /**
     * Customer added to export queue on save
     *
     * @param Varien_Event_Observer $observer
     */
    public function addCustomerToQueue(Varien_Event_Observer $observer)
    {
        if ($customer = $observer->getEvent()->getCustomer()) {
            $this->_addEntityToQueue('customer', $customer);
        }
    }

    /**
     * Invoice added to export queue on save
     *
     * @param Varien_Event_Observer $observer
     */
    public function addInvoiceToQueue(Varien_Event_Observer $observer)
    {
        if ($invoice = $observer->getEvent()->getInvoice()) {
            $this->_addEntityToQueue('invoice', $invoice);
        }
    }

    /**
     * Shipment added to export queue on save
     *
     * @param Varien_Event_Observer $observer
     */
    public function addShipmentToQueue(Varien_Event_Observer $observer)
    {
        if ($shipment = $observer->getEvent()->getShipment()) {
            $this->_addEntityToQueue('shipment', $shipment);
        }
    }

    /**
     * Credit Memo added to export queue on save
     *
     * @param Varien_Event_Observer $observer
     */
    public function addCreditmemoToQueue(Varien_Event_Observer $observer)
    {
        if ($creditmemo = $observer->getEvent()->getCreditmemo()) {
            $this->_addEntityToQueue('creditmemo', $creditmemo);
        }
    }

    /**
     * Order payment record added to export queue on save
     *
     * @param Varien_Event_Observer $observer
     */
    public function addPaymentToQueue(Varien_Event_Observer $observer)
    {
        if ($payment = $observer->getEvent()->getPayment()) {
            $this->_addEntityToQueue('payment', $payment);
        }
    }
}