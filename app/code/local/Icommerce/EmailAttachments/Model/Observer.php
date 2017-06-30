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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Icommerce
 * @package     Icommerce_EmailAttachments
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */
class Icommerce_EmailAttachments_Model_Observer
{
    /**
     * Add 'Print Orders' action to massaction of Sales Order grid in Magento admin
     *
     * @param Varien_Event_Observer $observer
     *
     * @void
     */
    public function addbutton($observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction // Magento CE
            || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction // Magento EE
        ) {
            if ($block->getRequest()->getControllerName() == 'sales_order') {
                $store  = Mage::app()->getStore();
                $path   = 'adminhtml/emailAttachments_order/pdforders';
                $params = array('_secure' => $store->isAdminUrlSecure());
                $url    = $store->getUrl($path, $params);

                $block->addItem(
                    'pdforders_order', array('label' => Mage::helper('sales')->__('Print Orders'), 'url' => $url,)
                );
            }
        }
    }

    /**
     * Adding of order PDF and user agreements (txt/html)
     * as email attachments before sending of email
     *
     * @param Varien_Event_Observer $observer
     *
     * @void
     */
    public function sendNewOrderEmailBeforeSend(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $mailTemplate = $observer->getEmailTemplate();

        if (Mage::getStoreConfig('sales_email/order/attachpdf', $order->getStore()->getId())) {

            //Create Pdf and attach to email - play nicely with PDF Customiser
            if (file_exists(BP . '/app/code/local/Icommerce/PdfCustomiser/Model/Order.php')) {
                $pdf = Mage::getModel('pdfcustomiser/order')->getPdf(array($order), null, null, true);
            } else {
                $pdf = Mage::getModel('emailattachments/order_pdf_order')->getPdf($order);
            }
            $mailTemplate->addAttachment($pdf, Mage::helper('sales')->__('Order') . "_" . $order->getIncrementId());
        }

        if (Mage::getStoreConfig('sales_email/order/attachagreement', $order->getStore()->getId())) {
            $mailTemplate->addAgreements($order->getStore()->getId());
        }
    }

    /**
     * Trigger additional event before Magento Mailer send action
     * This was done to provide compatibility with previous versions of Icommerce_EmailAttachments
     *
     * @param Varien_Event_Observer $observer
     *
     * @void
     */
    public function mailerBeforeSend(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $mailer = $event->getMailer();
        $queue = $mailer->getQueue();

        if (!$queue) {
            return;
        }

        Mage::helper('emailattachments')->prepareTemporaryDirectory($queue);

        $customData = $queue->getCustomData();
        if ($customData && $queue->getEventType() == Mage_Sales_Model_Order::EMAIL_EVENT_NAME_NEW_ORDER) {
            Mage::dispatchEvent(
                'sales_order_send_new_order_email_before_send',
                array('order' => $customData->getInstance(), 'email_template' => $event->getEmailTemplate())
            );
        }
    }

    /**
     * Remove temporary files after Magento Mailer send action
     *
     * @param Varien_Event_Observer $observer
     *
     * @void
     */
    public function mailerAfterSend(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $mailer = $event->getMailer();
        $queue = $mailer->getQueue();

        if ($queue) {
            Mage::helper('emailattachments')->removeTemporaryDirectory($queue);
        }
    }
}
