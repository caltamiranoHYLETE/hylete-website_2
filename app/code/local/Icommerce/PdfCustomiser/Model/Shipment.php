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
 * @package     Icommerce_PdfCustomiser
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Vaimo AB Team
 */

class Icommerce_PdfCustomiser_Model_Shipment extends Icommerce_PdfCustomiser_Model_Abstract
{
    /**
     * Creates PDF using the tcpdf library from array of shipments or orderIds
     * @param array $shipmentsGiven , $orderIds
     * @param array $orderIds
     * @param null $pdf
     * @param bool $suppressOutput
     * @return bool|\Icommerce_PdfCustomiser_MYPDF|null|\Zend_Pdf
     * @access public
     */
    public function getPdf($shipmentsGiven = array(), $orderIds = array(), $pdf = null, $suppressOutput = false)
    {
        if (empty($pdf) && empty($shipmentsGiven) && empty($orderIds)) {
            Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('adminhtml')
                    ->__('There are no printable documents related to selected orders'));
            return false;
        }

        $this->order_ids_before = $orderIds;
        $shipmentIds = array();
        if (!empty($shipmentsGiven)) {
            foreach ($shipmentsGiven as $shipmentGiven) {
                $currentOrderId = $shipmentGiven->getOrder()
                        ->getId();
                $this->order_ids_before[] = $currentOrderId;
                if (!isset($shipmentIds[$currentOrderId])) {
                    $shipmentIds[$currentOrderId] = array();
                }
                $shipmentIds[$currentOrderId][] = $shipmentGiven->getId();
            }
        }
        $this->order_ids_before = array_unique($this->order_ids_before);
        //need to get the store id from the first order to initialise pdf
        $this->_current_order_store_id = $order = Mage::getModel('sales/order')
                ->load($this->order_ids_before[0])
                ->getStoreId();

        $this->_beforeGetPdf();

        //work with a new pdf or add to existing one
        if (empty($pdf)) {
            $pdf = $this->getPdfObject($this->_current_order_store_id);
        }

        foreach ($this->order_ids_before as $orderId) {
            if (!empty($shipmentIds[$orderId])) {
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                        ->addAttributeToSelect('*')
                        ->setOrderFilter($orderId)
                        ->addAttributeToFilter('entity_id', array('in' => $shipmentIds[$orderId]))
                        ->load();
            } else {
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                        ->addAttributeToSelect('*')
                        ->setOrderFilter($orderId)
                        ->load();
            }
            if ($shipments->getSize() == 0) {
                continue;
            }
            foreach ($shipments as $shipment) {
                $this->_processShipmentPdf($pdf, $shipment);
            }
        }

        // reset pointer to the last page
        if ($pdf->getNumPages()) {
            $pdf->lastPage();
        }

        //output PDF document
        if (!$suppressOutput) {
            if ($pdf->getPdfAnyOutput()) {
                $pdf->Output('packingslip_' . Mage::getSingleton('core/date')
                        ->date('Y-m-d_H-i-s') . '.pdf', 'I');
                exit;
            } else {
                Mage::getSingleton('adminhtml/session')
                        ->addError(Mage::helper('adminhtml')
                        ->__('There are no printable documents related to selected orders'));
            }
        }

        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * @param Icommerce_PdfCustomiser_MYPDF $pdf
     * @param Icommerce_EmailAttachments_Model_Order_Shipment|Mage_Sales_Model_Order_Shipment $shipment
     */
    protected function _processShipmentPdf($pdf, $shipment)
    {
        /** @var Icommerce_PdfCustomiser_Helper_Shipment $shipmentHelper */
        $shipmentHelper = Mage::helper('pdfcustomiser/shipment');
        $shipment->load($shipment->getId());
        $storeId = $shipment->getStoreId();
        if ($shipment->getStoreId()) {
            Mage::app()
                    ->getLocale()
                    ->emulate($shipment->getStoreId());
        }

        $shipmentHelper->setStoreId($storeId);
        // set standard pdf info
        $pdf->SetStandard($shipmentHelper);

        // add a new page
        $pdf->AddPage();
        // Output heading for Items
        switch (Mage::getStoreConfig('sales_pdf/all/allpagesize', $storeId)) {
            case 'A4':
                $units = (595 - 2.83 * 2 * $shipmentHelper->getPdfMargins('sides')) / 10;
                break;
            case 'LETTER':
                $units = (612.00 - 2.83 * 2 * (float)$shipmentHelper->getPdfMargins('sides')) / 10;
                break;
        }
        $this->_processShipmentPdfHeader($pdf, $shipment, $shipmentHelper, $units, Mage::getStoreConfig(self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID, $storeId));

        $salesHelper = Mage::helper('sales');

        // Output heading for Items
        $tbl = '<table border="0" cellpadding="2" cellspacing="0">';
        $tbl .= '<thead>';
        $tbl .= '<tr>';
        $tbl .= '<th width="' . (6.9 * $units) . '"><strong>' . $salesHelper->__('Name') . '</strong></th>';
        $tbl .= '<th width="' . (2 * $units) . '"><strong>' . $salesHelper->__('SKU') . '</strong></th>';
        $tbl .= '<th width="' . (1.1 * $units) . '" align="center"><strong>' . $salesHelper->__('QTY') . '</strong></th>';
        $tbl .= '</tr>';
        $tbl .= '<tr><td width="' . (10 * $units) . '" colspan="6"><hr style="width:10px;"/></td></tr>';
        $tbl .= '</thead>';

        // Prepare Line Items
        $pdfItems = array();
        $pdfBundleItems = array();
        $pdf->prepareLineItems($shipmentHelper, $shipment->getAllItems(), $pdfItems, $pdfBundleItems);

        //Output Line Items
        $pdf->SetFont($shipmentHelper->getPdfFont(), '', $shipmentHelper->getPdfFontsize('small'));
        $shipmentDisplay = Mage::getStoreConfig('sales_pdf/shipment/shipmentdisplay', $storeId);
        foreach ($pdfItems as $pdfItem) {
            $tbl .= $this->_processShipmentPdfItem($pdf, $pdfItem, $pdfBundleItems, $shipmentHelper, $shipmentDisplay, $units);
            $tbl .= '<tcpdf method="Line2" params=""/>';
        }
        $tbl .= '</table>';
        $pdf->writeHTML($tbl, true, false, false, false, '');
        $pdf->SetFont($shipmentHelper->getPdfFont(), '', $shipmentHelper->getPdfFontsize());

        //reset Margins in case there was a page break
        $pdf->setMargins($shipmentHelper->getPdfMargins('sides'), $shipmentHelper->getPdfMargins('top'));

        // Output Order Gift Message
        $pdf->OutputGiftMessage($shipmentHelper, $shipment->getOrder());

        // Output Comments
        $pdf->OutputComment($shipmentHelper, $shipment);

        //Custom Blurb underneath
        $pdf->Ln(2);
        $pdf->writeHTMLCell(0, 0, null, null, $shipmentHelper->getPdfShipmentCustom(), null, 1);
        if ($shipment->getStoreId()) {
            Mage::app()
                    ->getLocale()
                    ->revert();
        }
        $pdf->setPdfAnyOutput(true);
    }

    /**
     * @param Icommerce_PdfCustomiser_MYPDF $pdf
     * @param Icommerce_EmailAttachments_Model_Order_Shipment|Mage_Sales_Model_Order_Shipment $shipment
     * @param Icommerce_PdfCustomiser_Helper_Shipment $shipmentHelper
     * @param bool $printOrderNumber
     */
    protected function _processShipmentPdfHeader($pdf, $shipment, $shipmentHelper, $units = null, $printOrderNumber = false)
    {
        $pdf->printHeader($shipmentHelper, $shipmentHelper->getPdfShipmentTitle());

        $shipmentNumbersEtc = Mage::helper('sales')
                        ->__('Packingslip # ') . $shipment->getIncrementId() . "\n";
        if ($printOrderNumber) {
            $shipmentNumbersEtc .= Mage::helper('sales')
                            ->__('Order # ') . $shipment->getOrder()
                            ->getIncrementId() . "\n";
        }

        $shipmentNumbersEtc .= Mage::helper('catalog')
                        ->__('Date') . ': ' . Mage::helper('core')
                        ->formatDate($shipment->getCreatedAt(), 'medium', false) . "\n";
        $pdf->MultiCell($pdf->getPageWidth() / 2 - $shipmentHelper->getPdfMargins('sides'), 0, $shipmentNumbersEtc, 0, 'L', 0, 0);
        $pdf->MultiCell($pdf->getPageWidth() / 2 - $shipmentHelper->getPdfMargins('sides'), $pdf->getLastH(), $shipmentHelper->getPdfOwnerAddresss(), 0, 'L', 0, 1);
        $pdf->Ln(5);

        //add billing and shipping addresses
        $pdf->OutputCustomerAddresses($shipmentHelper, $shipment->getOrder(), $shipmentHelper->getPdfShipmentAddresses());

        // Output Shipping and Payment
        $pdf->OutputPaymentAndShipping($shipmentHelper, $shipment->getOrder(), $shipment);
    }

    /**
     * @param Icommerce_PdfCustomiser_MYPDF $pdf
     * @param array $pdfItem
     * @param array $pdfBundleItems
     * @param Icommerce_PdfCustomiser_Helper_Shipment $shipmentHelper
     * @param string $shipmentDisplay
     * @param float $units
     *
     * @return string
     */
    protected function _processShipmentPdfItem($pdf, $pdfItem, $pdfBundleItems, $shipmentHelper, $shipmentDisplay, $units)
    {
        // we generallly don't want to display subitems of configurable products etc
        if ($pdfItem['parentItemId']) {
            return '';
        }

        $tbl = '';

        //Output line items
        if ($pdfItem['parentType'] != 'bundle' && $pdfItem['type'] != 'bundle') {
            // Output 1 line item
            $tbl .= '<tr>';
            $shipmentHelper->outputShippingLineItem($tbl, $shipmentHelper, $shipmentDisplay, $pdf, $pdfItem, $units);
            $tbl .= '<td width="' . (2 * $units) . '">' . $pdfItem['productDetails']['Sku'] . '</td>';
            $tbl .= '<td width="' . (1.1 * $units) . '"align="center">' . $pdfItem['qty'] . '</td>';
            $tbl .= '</tr>';
            return $tbl;
        }

        // Deal with Bundles
        //check if the subitems of the bundle have separate prices
        $currentParentId = $pdfItem['itemId'];
        $subItemsSum = 0;
        foreach ($pdfBundleItems[$currentParentId] as $bundleItem) {
            $subItemsSum += $bundleItem['price'];
        }
        //don't display bundle price if subitems have prices
        if ($subItemsSum > 0) {
            // Output 1 bundle with subitems separately
            $tbl .= '<tr>';
            $shipmentHelper->outputShippingLineItem($tbl, $shipmentHelper, $shipmentDisplay, $pdf, $pdfItem, $units);
            $tbl .= '<td colspan="2" width="' . (2.75 * $units) . '">' . $pdfItem['productDetails']['Sku'] . '</td>';
            $tbl .= '</tr>';
            //Display subitems
            foreach ($pdfBundleItems[$currentParentId] as $bundleItem) {
                $tbl .= '<tr>';
                // Output 1 subitem
                $bundleItem['productDetails']['Name'] = '&nbsp;&nbsp;&nbsp;&nbsp;' . $bundleItem['productDetails']['Name'];
                $shipmentHelper->outputShippingLineItem($tbl, $shipmentHelper, $shipmentDisplay, $pdf, $bundleItem, $units, false);
                $tbl .= '<td width="' . (2 * $units) . '">' . $bundleItem['productDetails']['Sku'] . '</td>';
                $tbl .= '<td width="' . (1.1 * $units) . '" align="center">' . $pdfItem['qty'] . '</td>';
                $tbl .= '</tr>';
            }
            return $tbl;
        }

        foreach ($pdfBundleItems[$currentParentId] as $bundleItem) {
            $pdfItem['productDetails']['Name'] .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;" . $bundleItem['qty'] . " x " . $bundleItem['productDetails']['Name'];
        }

        // Output bundle with items as decription only
        $tbl .= '<tr>';
        $shipmentHelper->outputShippingLineItem($tbl, $shipmentHelper, $shipmentDisplay, $pdf, $pdfItem, $units);
        $tbl .= '<td width="' . (2 * $units) . '">' . $pdfItem['productDetails']['Sku'] . '</td>';
        $tbl .= '<td width="' . (1.1 * $units) . '" align="center">' . $pdfItem['qty'] . '</td>';
        $tbl .= '</tr>';
        return $tbl;
    }
}

/**
 * Class Icommerce_PdfCustomiser_Shipment
 * @deprecated since version 1.2.32, please use instead Mage::helper('pdfcustomiser/shipment');
 */
class Icommerce_PdfCustomiser_Shipment extends Icommerce_PdfCustomiser_Helper_Shipment
{
}
