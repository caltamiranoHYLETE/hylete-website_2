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
 * @package     Vaimo_Blog
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Tobias Wiking
 */
require_once 'Mage/Adminhtml/controllers/Catalog/ProductController.php';

class Vaimo_Blog_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{

    /**
     * Save product action
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        $storeId = $request->getParam('store');
        $productId = $request->getParam('id');
        $redirectBack = $request->getParam('back', false);
        $isEdit = (int )($productId != null);

        $data = $request->getPost();

        if ($data) {
            if (isset($data['product']['blog_content']) && (bool)$data['product']['blog_content']) {
                $data['product']['stock_data']['manage_stock'] = 1;
                $data['product']['stock_data']['is_in_stock'] = 1;
                if (!isset($data['product']['stock_data']['use_config_manage_stock'])) {
                    $data['product']['stock_data']['use_config_manage_stock'] = 0;
                }
            }

            $request->setPost($data);
            $product = $this->_initProductSave();

            try {
                $product->save();
                $productId = $product->getId();

                /**
                 * Do copying data to stores
                 */
                if (isset($data['copy_to_stores'])) {
                    foreach ($data['copy_to_stores'] as $storeTo => $storeFrom) {
                        $newProduct = Mage::getModel('catalog/product')
                                ->setStoreId($storeFrom)
                                ->load($productId)
                                ->setStoreId($storeTo)
                                ->save();
                    }
                }
                $check_model = Mage::getModel('catalogrule/rule');
                if(method_exists($check_model,'applyAllRulesToProduct')) {
                    Mage::getModel('catalogrule/rule')->applyAllRulesToProduct($product);
                }

                $this->_getSession()->addSuccess($this->__('The product has been saved.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage())->setProductData($data);
                $redirectBack = true;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect('*/*/edit', array('id' => $productId, '_current' => true));
        } elseif ($request->getParam('popup')) {
            $this->_redirect('*/*/created', array('_current' => true, 'id' => $productId, 'edit' => $isEdit));
        } else {
            if (!$this->_specialRedirect($product, $storeId)) {
                $this->_redirect('*/*/', array('store' => $storeId));
            }
        }
    }

    private function _specialRedirect($product, $storeId)
    {
        if ($product->getTypeId() == 'blog') {
            $this->_redirect('adminhtml/blog_blog', array('store' => $storeId));
            return true;
        }
        return false;
    }
}
