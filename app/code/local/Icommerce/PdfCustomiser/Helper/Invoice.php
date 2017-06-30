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
 * Class Icommerce_PdfCustomiser_Helper_Invoice
 */
class Icommerce_PdfCustomiser_Helper_Invoice extends Icommerce_PdfCustomiser_Helper_Pdf
{

    /**
     * get main heading for invoice title ie TAX INVOICE
     *
     * @return  string
     * @access public
     */
    public function getPdfInvoiceTitle()
    {
        return Mage::getStoreConfig('sales_pdf/invoice/invoicetitle', $this->getStoreId());
    }

    /**
     * get tax number
     *
     * @return  string
     * @access public
     */
    public function getPdfInvoiceTaxNumber()
    {
        return Mage::getStoreConfig('sales_pdf/invoice/invoicetaxnumber', $this->getStoreId());
    }

    /**
     * return which addresses to display
     *
     * @return  string billing/shipping/both
     * @access public
     */
    public function getPdfInvoiceAddresses()
    {
        return Mage::getStoreConfig('sales_pdf/invoice/invoiceaddresses', $this->getStoreId());
    }

    /**
     * custom text for underneath invoice
     *
     * @return  string
     * @access protected
     */
    public function getPdfInvoiceCustom()
    {
        return Mage::getStoreConfig('sales_pdf/invoice/invoicecustom', $this->getStoreId());
    }
}
