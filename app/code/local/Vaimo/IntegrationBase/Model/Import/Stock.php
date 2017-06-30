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
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Import_Stock extends Vaimo_IntegrationBase_Model_Import_Abstract
{
    protected $_eventPrefix = 'stock';

    protected $_productModel;
    protected $_stockItemModel;

    protected $_defaultStockData = array(
        'stock_id'                      => 1,
        'manage_stock'                  => 1,
        'use_config_manage_stock'       => 1,
        'qty'                           => 0,
        'min_qty'                       => 0,
        'use_config_min_qty'            => 1,
        'min_sale_qty'                  => 1,
        'use_config_min_sale_qty'       => 1,
        'max_sale_qty'                  => 10000,
        'use_config_max_sale_qty'       => 1,
        'is_qty_decimal'                => 0,
        'backorders'                    => 0,
        'use_config_backorders'         => 1,
        'notify_stock_qty'              => 1,
        'use_config_notify_stock_qty'   => 1,
        'enable_qty_increments'         => 0,
        'use_config_enable_qty_inc'     => 1,
        'qty_increments'                => 0,
        'use_config_qty_increments'     => 1,
        'is_in_stock'                   => 0,
        'low_stock_date'                => null,
        'stock_status_changed_auto'     => 0,
        'is_decimal_divided'            => 0
    );

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/stock', 'Stock import', Mage_CatalogInventory_Model_Stock_Item::ENTITY);
        $this->_successMessage = '%d stock level(s) imported';
        $this->_failureMessage = '%d stock level(s) failed to import';
        $this->_productModel = Mage::getModel('catalog/product');
        $this->_stockItemModel = Mage::getModel('cataloginventory/stock_item');
    }

    protected function _importRecord($item)
    {
        $qty = (float)$item->getQty();
        $this->_log($item->getSku() . ' - ' . $qty);

        /** @var $product Mage_Catalog_Model_Product */
        $productId = Mage::getSingleton('catalog/product')->getIdBySku($item->getSku());

        if (!$productId) {
            Mage::throwException('Product not found');
        }

        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = $this->_stockItemModel->reset()->loadByProduct($productId);

        if (!$stockItem->getId()) {
            $product = $this->_productModel->reset()->load($productId);

            if (!$product->getId()) {
                Mage::throwException('Product could not be loaded');
            }

            $stockItem->setProduct($product);
            $stockItem->addData($this->_defaultStockData);
        }

        $stockItem->setQty($qty);
        $stockItem->setIsInStock($item->getStockStatus());

        if ($data = $item->getStockData()) {
            $stockItem->addData($data);
        }

        Mage::dispatchEvent('integrationbase_import_stock', array(
            'stock_item' => $stockItem,
            'item' => $item
        ));

        $stockItem->save();

        return $this;
    }

    protected function _deleteRecord($item)
    {
        Mage::throwException('Stock import does not support delete');
    }
}