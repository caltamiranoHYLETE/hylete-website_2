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

abstract class Icommerce_PdfCustomiser_Model_Abstract extends Mage_Sales_Model_Order_Pdf_Abstract
{
    protected $order_ids_before = array();
    protected $design_before = array();
    protected $_current_order_store_id = 0;

    protected function _beforeGetPdf()
    {
        $theme_translation = Mage::getStoreConfig('sales_pdf/all/allthemetranslation', $this->_current_order_store_id);
        if ($theme_translation) {
            Mage::getDesign()->setArea('frontend');

            $this->design_before = Mage::getDesign()->setAllGetOld(array(
                'package' => Mage::getStoreConfig('design/package/name', $this->_current_order_store_id),
                'store' => $this->_current_order_store_id,
            ));
        }
        parent::_beforeGetPdf();
    }

    protected function _afterGetPdf()
    {
        $theme_translation = Mage::getStoreConfig('sales_pdf/all/allthemetranslation', $this->_current_order_store_id);
        if ($theme_translation) {
            Mage::getDesign()->setAllGetOld($this->design_before);
        }
        parent::_afterGetPdf();
    }

    /**
     * @param $storeId
     *
     * @return Icommerce_PdfCustomiser_MYPDF
     */
    public function getPdfObject($storeId)
    {
        $pdfPageSize = Mage::getStoreConfig('sales_pdf/all/allpagesize', $storeId);
        $defaultTcpdfClassName = Mage::getStoreConfig('sales_pdf/tcpdf_class_name', $storeId);
        $pdf = new $defaultTcpdfClassName('P', 'mm', $pdfPageSize, true, 'UTF-8', false);
        return $pdf;
    }
}
