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

require_once Mage::getModuleDir('controllers', 'Icommerce_EmailAttachments') . DS . 'Adminhtml/EmailAttachments/CreditmemoController.php';

class Icommerce_PdfCustomiser_Adminhtml_PdfCustomiser_CreditmemoController extends Icommerce_EmailAttachments_Adminhtml_EmailAttachments_CreditmemoController
{
    public function pdfcreditmemosAction()
    {
        $creditmemosIds = $this->getRequest()->getPost('creditmemo_ids');
        $orderIds = array();

        foreach ($creditmemosIds as $creditmemoId) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
            $order_id = $creditmemo->getData('order_id');
            array_push($orderIds, $order_id);
        }
        $pdf = Mage::getModel('pdfcustomiser/creditmemo')->getPdf(null, $orderIds, null, false);
        $this->_redirect('*/*/');
    }
}