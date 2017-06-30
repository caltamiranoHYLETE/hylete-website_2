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

/**
 * Extend the TCPDF class to create custom Header
 *
 * Class Icommerce_PdfCustomiser_Helper_Shipment
 */
class Icommerce_PdfCustomiser_Helper_Shipment extends Icommerce_PdfCustomiser_Helper_Pdf
{
    const LINE_ITEM_DISPLAY_IMAGE = 'image';
    const LINE_ITEM_DISPLAY_BARCODE = 'barcode';
    const LINE_ITEM_DISPLAY_NONE = 'none';

    /**
     * get main heading for invoice title
     *
     * @return  string
     * @access public
     */
    public function getPdfShipmentTitle()
    {
        return Mage::getStoreConfig('sales_pdf/shipment/shipmenttitle', $this->getStoreId());
    }

    /**
     * return which addresses to display
     *
     * @return  string billing/shipping/both
     * @access public
     */
    public function getPdfShipmentAddresses()
    {
        return Mage::getStoreConfig('sales_pdf/shipment/shipmentaddresses', $this->getStoreId());
    }

    /**
     * custom text for underneath invoice
     *
     * @return  string
     * @access public
     */
    public function getPdfShipmentCustom()
    {
        return Mage::getStoreConfig('sales_pdf/shipment/shipmentcustom', $this->getStoreId());
    }

    /**
     * output display of product on packing slip - optional display of image or barcode
     *
     * @return  lineHeight
     * @access public
     */
    public function outputShippingLineItem(&$tbl, $helper, $display, &$pdf, $pdfItem, $units, $suppressBarcode = false)
    {
        if ($pdfItem['parentItemId']) {
            $pdfItem['productDetails']['Name'] = "    " . $pdfItem['productDetails']['Name'];
        }
        switch ($display) {
            case self::LINE_ITEM_DISPLAY_IMAGE:
                $productImage = Mage::getModel('catalog/product')
                    ->load($pdfItem['productId'])
                    ->getImage();

                if ($productImage != "no_selection") {
                    $tbl .= '<td width="' . (3.9 * $units) . '">' . $pdfItem['productDetails']['Name'] . '</td>';
                    $imagePath = 'media/catalog/product' . $productImage;
                    $tbl
                        .=
                        '<td align="center" width="' . (3 * $units) . '"><img src="' . $imagePath . '" width="' . (1.5
                            * $units) . '"/></td>';
                } else {
                    $tbl .= '<td width="' . (6.9 * $units) . '">' . $pdfItem['productDetails']['Name'] . '</td>';
                }

                break;
            case self::LINE_ITEM_DISPLAY_BARCODE:
                $tbl .= '<td width="' . (3.9 * $units) . '">' . $pdfItem['productDetails']['Name'] . '</td>';
                if (!$suppressBarcode) {
                    // CODE 39 EXTENDED + CHECKSUM
                    $tbl .= '<td height="' . (1 * $units) . '" width="' . (3 * $units)
                        . '"><tcpdf method="write1DBarcode" params="\'' . $pdfItem['productDetails']['Sku']
                        . '\',\'C39E+\',null,null,' . (0.8 * $units) . ',' . (0.35 * $units) . '"/></td>';
                } else {
                    $tbl .= '<td width="' . (3 * $units) . '">&nbsp;</td>';
                }

                break;
            case self::LINE_ITEM_DISPLAY_NONE:
            default:
                $tbl .= '<td width="' . (6.9 * $units) . '">' . $pdfItem['productDetails']['Name'] . '</td>';
        }
    }
}
