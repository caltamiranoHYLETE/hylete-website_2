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

class Vaimo_IntegrationBase_Model_Import_Creditmemo extends Vaimo_IntegrationBase_Model_Import_Abstract
{
    protected $_eventPrefix = 'creditmemo';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/creditmemo', 'Credit Memo import');
        $this->_successMessage = '%d creditmemo(s) imported';
        $this->_failureMessage = '%d creditmemo(s) failed to import';
    }

    protected function _importRecord($baseCreditmemo)
    {
        $this->_log($baseCreditmemo->getIncrementId());

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($baseCreditmemo->getIncrementId());

        if (!$order->getId()) {
            Mage::throwException(Mage::helper('integrationbase')->__('Order not found'));
        }

        if (!$order->canCreditmemo()) {
            Mage::throwException(Mage::helper('integrationbase')->__('Cannot create credit memo for the order'));
        }

        $qtyData = array();

        foreach ($baseCreditmemo->getAllItems() as $baseItem) {
            $matchFound = false;

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

        /** @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = Mage::getModel('sales/service_order', $order)->prepareCreditmemo($qtyData);

        if (!$creditmemo) {
            Mage::throwException(Mage::helper('integrationbase')->__('Failed to create credit memo'));
        }

        $creditmemo->register();
        $creditmemo->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($creditmemo)
            ->addObject($creditmemo->getOrder())
            ->save();

        //Send creditmemo email to customer
        $creditmemo->sendEmail()->setEmailSent(true);
        $creditmemo->save();

        return $this;
    }

    protected function _deleteRecord($item)
    {
        Mage::throwException('Credit Memo import does not support delete');
    }
}