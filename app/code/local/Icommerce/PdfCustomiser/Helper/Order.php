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
 * Class Icommerce_PdfCustomiser_Helper_Order
 */
class Icommerce_PdfCustomiser_Helper_Order extends Icommerce_PdfCustomiser_Helper_Pdf
{
    /**
     * get main heading for order title
     *
     * @return  string
     * @access public
     */
    public function getPdfOrderTitle()
    {
        return Mage::getStoreConfig('sales_pdf/order/ordertitle', $this->getStoreId());
    }

    /**
     * return which addresses to display
     *
     * @return  string billing/shipping/both
     * @access public
     */
    public function getPdfOrderAddresses()
    {
        return Mage::getStoreConfig('sales_pdf/order/orderaddresses', $this->getStoreId());
    }

    /**
     * custom text for underneath order
     *
     * @return  string
     * @access public
     */
    public function getPdfOrderCustom()
    {
        return Mage::getStoreConfig('sales_pdf/order/ordercustom', $this->getStoreId());
    }
}
