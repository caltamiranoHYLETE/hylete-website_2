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
 * @package     Vaimo_IntegrationBaseStandard
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Kjell Holmqvist <kjell.holmqvist@vaimo.com>
 */

class Vaimo_IntegrationBaseStandard_Model_Import_Stock extends Vaimo_IntegrationBaseStandard_Model_Import_Abstract
{
    protected $_logFile = 'standard_import_stock.log';

    protected function _getProductId($sku)
    {
        $select = $this->_getRead()->select()
            ->from($this->_getTableName('catalog/product'), 'entity_id')
            ->where('sku = :sku');

        $bind = array(':sku' => (string)$sku);

        return $this->_getRead()->fetchOne($select, $bind);
    }

    protected function _alwaysPositive($value)
    {
        if ($value > 0) {
            return $value;
        } else {
            return 0;
        }
    }

    protected function _addStock($productId, $sku, $qty)
    {
        $this->_log($sku . ' - ' . $qty);

        /** @var Vaimo_IntegrationBase_Model_Stock $stock */
        $stock = Mage::getModel('integrationbase/stock');
        $stock->load($sku, 'sku');
        $stock->setSku($sku);
        $stock->setQty($this->_alwaysPositive($qty));
        $minQty = 0;
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
        if ($stockItem && $stockItem->getId()) {
            $minQty = $stockItem->getMinQty();
            if ($minQty<=0 && $stockItem->getUseConfigMinQty()==1) {
                $minQty = Mage::getStoreConfig('cataloginventory/item_options/min_qty');
            }
        }
        $stock->setStockStatus($qty > $minQty ? Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK : Mage_CatalogInventory_Model_Stock::STOCK_OUT_OF_STOCK);
        $stock->setRowStatus(Vaimo_IntegrationBase_Helper_Data::ROW_STATUS_IMPORT);
        $stock->save();

        $this->_successCount++;
    }

    protected function _importRows($rows)
    {
        $headerPrinted = false;

        foreach ($rows as $row) {
            if (!$headerPrinted) {
                $this->_log('Importing stock levels');
                $this->_log('');
                $headerPrinted = true;
            }
            
            $productId = $this->_getProductId($row['sku']);
            if (!$productId) {
                $this->_log('Product not found: ' . $row['sku']);
                $this->_failureCount++;
                continue;
            }

            $sku = $row['sku'];
            $qty = (float)$row['qty'];
            $this->_addStock($productId, $sku, $qty);
        }

    }

    public function import($filename)
    {
        $this->_log('Reading file: ' . $filename);
        $this->_log('');

        $stockData = Mage::getSingleton('integrationbasestandard/xml_parser')->parse($filename, '/integrationbase/stock');
        $this->_importRows($stockData);

        $this->_log('');
    }

}