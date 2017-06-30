<?php
/**
 * Copyright © 2009-2011 Icommerce Nordic AB
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
 * @package     Icommerce_Enhancedgrid
 * @copyright   Copyright © 2009-2012 Icommerce Nordic AB
 */
include_once "Mage".DS."Adminhtml".DS."controllers".DS."Catalog".DS."ProductController.php";
class Icommerce_Enhancedgrid_Catalog_Product_EnhancedController extends Mage_Adminhtml_Catalog_ProductController
{
    protected $massactionEventDispatchEnabled = true;
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Icommerce_Enhancedgrid');
    }

    /**
     * Product list page
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/enhancedgrid');

        $this->_addContent(
            $this->getLayout()->createBlock('enhancedgrid/catalog_product')
        );

        $this->renderLayout();
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('enhancedgrid/catalog_product_grid')->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/products');
    }



    /**
     * Export product grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'products.csv';
        $content    = $this->getLayout()->createBlock('enhancedgrid/catalog_product_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export product grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'products.xml';
        $content    = $this->getLayout()->createBlock('enhancedgrid/catalog_product_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }


    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');

        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);

        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    /**
     * This will relate all products selected to each other.
     *
     */
    public function massRefreshProductsAction()
    {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            $this->_getSession()->addError($this->__('Please select product(s)'));
        }
        else {
            try {
                foreach ($productIds as $productId) {
                    $product = Mage::getModel('catalog/product')->load($productId);
                    if ($this->massactionEventDispatchEnabled)
                        Mage::dispatchEvent('catalog_product_prepare_save',
                            array('product' => $product, 'request' => $this->getRequest()));
                    $product->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully refreshed.', count($productIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}