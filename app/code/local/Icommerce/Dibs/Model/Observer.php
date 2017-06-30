<?php
/**
 * Copyright (c) 2009-2011 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
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
 * @category    Icommerce
 * @package     Icommerce_Dibs
 * @copyright   Copyright (c) 2009-2011 Icommerce Nordic AB
 * @author      Wilko Nienhaus
 */

class Icommerce_Dibs_Model_Observer
{
    /**
     * When the frontend shows different payment methods which are all actually DIBS,
     * this function fixes the 'method' that Magento will see/use and we instead put the
     * method selected on front-end into a special variable (that will eventually end up
     * in the additionalData array of the payment method)
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function fixPaymentMethod(Varien_Event_Observer $observer)
    {
        /** @var Varien_Object $data */
        $data = $observer->getEvent()->getInput();

        if (substr($data->getMethod(),0,5)=='dibs_') {
            $data->setFrontendMethod(substr($data->getMethod(),5));
            $data->setMethod('dibs');
        }
        if ($data->getMethod()=='dibs') {
            if ($data->getFees()) {
                $payment = $observer->getEvent()->getPayment();
                $additional_data = unserialize($payment->getAdditionalData());
                $additional_data['fees'] = $data->getFees();
                $payment->setAdditionalData(serialize($additional_data));
            }
        }
    }

/* Moved to Order module
    public function salesOrderCancel(Varien_Event_Observer $observer)
    {
        $payment = $observer->getEvent()->getPayment();
        if ($payment->getMethod()=='dibs') {
            $dibs = Mage::getModel('dibs/dibs');
            $ares = $dibs->tryCancel($payment);
            if ($ares[0]==0) {
                Mage::getSingleton('adminhtml/session')->addSuccess($ares[1]);
            } elseif ($ares[0]>1) {
//                Mage::getSingleton('adminhtml/session')->addWarning($ares[1]);
                throw new Mage_Core_Exception($ares[1]);
            } elseif ($ares[0]!=1) {
//                Mage::getSingleton('adminhtml/session')->addError($ares[1]);
                throw new Mage_Core_Exception($ares[1]);
            }
            $payment->getOrder()->addStatusToHistory($payment->getOrder()->getStatus(), $ares[1]);
        }
    }

    public function salesOrderRefund(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $payment = $creditmemo->getOrder()->getPayment();
        if ($payment->getMethod()=='dibs') {
            $dibs = Mage::getModel('dibs/dibs');
            $ares = $dibs->tryRefund($payment,$creditmemo->getGrandTotal());
            if ($ares[0]==0) {
                Mage::getSingleton('adminhtml/session')->addSuccess($ares[1]);
            } elseif ($ares[0]>1) {
//                Mage::getSingleton('adminhtml/session')->addWarning($ares[1]);
                throw new Mage_Core_Exception($ares[1]);
            } elseif ($ares[0]!=1) {
//                Mage::getSingleton('adminhtml/session')->addError($ares[1]);
                throw new Mage_Core_Exception($ares[1]);
            }
            $payment->getOrder()->addStatusToHistory($payment->getOrder()->getStatus(), $ares[1]);
        }
    }
    
    public function salesPaymentRefund(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $payment = $observer->getEvent()->getPayment();
        if ($payment->getMethod()=='dibs') {
            $dibs = Mage::getModel('dibs/dibs');
            $ares = $dibs->tryRefund($payment,$creditmemo->getGrandTotal());
            if ($ares[0]==0) {
                Mage::getSingleton('adminhtml/session')->addSuccess($ares[1]);
            } elseif ($ares[0]>1) {
//                Mage::getSingleton('adminhtml/session')->addWarning($ares[1]);
                throw new Mage_Core_Exception($ares[1]);
            } elseif ($ares[0]!=1) {
//                Mage::getSingleton('adminhtml/session')->addError($ares[1]);
                throw new Mage_Core_Exception($ares[1]);
            }
            $payment->getOrder()->addStatusToHistory($payment->getOrder()->getStatus(), $ares[1]);
        }
    }
*/
}