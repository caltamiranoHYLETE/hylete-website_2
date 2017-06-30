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
 * @package     Vaimo_HyleteIntegration
 * @file        Stock.php
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Vitali Rassolov <vitali.rassolov@vaimo.com>
 */

class Vaimo_HyleteIntegration_Model_Integration_Base_Standard_Import_Stock extends Vaimo_IntegrationBaseStandard_Model_Import_Stock
{
    protected $_resourceName = 'hyleteintegration/stock';

    protected $_stockNameToIdLookup = array();

    /**
     * @param string $stockName
     *
     * @return int
     *
     * @throws Mage_Core_Exception
     */
    protected function _getStockIdByName($stockName)
    {
        if (!$this->_stockNameToIdLookup) {
            /** @var Vaimo_HyleteIntegration_Model_Resource_Stock $resource */
            $resource = $this->getResource();
            $this->_stockNameToIdLookup = $resource->getStockNameToIdLookup();
        }

        if (!isset($this->_stockNameToIdLookup[$stockName])) {
            Mage::throwException('Unknown stock name: ' . $stockName);
        }

        return $this->_stockNameToIdLookup[$stockName];
    }

    protected function _addStock($productId, $stockName, $sku, $qty)
    {
        $stockId = $this->_getStockIdByName($stockName);

        $this->_log($sku . ' - ' . $qty . ' | ' . $stockName);

        /** @var Vaimo_IntegrationBase_Model_Stock $stock */
        $stock = Mage::getModel('integrationbase/stock');
        $stock->getResource()->loadByKeys($stock, array(
            'stock_id' => $stockId,
            'sku' => $sku,
        ));

        $stock->setStockId($stockId);
        $stock->setSku($sku);
        $stock->setQty($this->_alwaysPositive($qty));
        $minQty = 0;
        $stockItem = Mage::getModel('cataloginventory/stock_item')
            ->setStockId($stockId)
            ->loadByProduct($productId);
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

            if (!isset($row['stock_name'])) {
                $stockName = Vaimo_HyleteIntegration_Helper_Data::DEFAULT_STOCK_NAME;
            } else {
                $stockName = $row['stock_name'];
            }

            $sku = $row['sku'];
            $qty = (float)$row['qty'];
            $this->_addStock($productId, $stockName, $sku, $qty);
        }
    }
}