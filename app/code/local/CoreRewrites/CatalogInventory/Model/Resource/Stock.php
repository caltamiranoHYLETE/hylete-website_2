<?php
/**
* Original patch applied to app/code/core/Mage/CatalogInventory/Model/Resource/Stock by Vaimo
* Based on GIST
* Rewritted to overwrite and extend the core class to encapsulate the modification instead of patching core
* https://github.com/OpenMage/magento-lts/pull/103
*
* @author   Joel    @Mediotype
**/

class CoreRewrites_CatalogInventory_Model_Resource_Stock extends Mage_CatalogInventory_Resource_Stock {

    /**
    * Get stock items data for requested products
    *
    * @param Mage_CatalogInventory_Model_Stock $stock
    * @param array $productIds
    * @param bool $lockRows
    * @return array
    */
    public function getProductsStock($stock, $productIds, $lockRows = false)
    {
        if (empty($productIds)) {
        return array();
        }
        $itemTable = $this->getTable('cataloginventory/stock_item');
        $productTable = $this->getTable('catalog/product');
        $select = $this->_getWriteAdapter()->select()
        ->from(array('si' => $itemTable))
        ->where('stock_id=?', $stock->getId())
        ->where('product_id IN(?)', $productIds)
        ->forUpdate($lockRows);
        $rows = $this->_getWriteAdapter()->fetchAll($select);

        // https://github.com/OpenMage/magento-lts/pull/103
        // Add type_id to result using separate select without FOR UPDATE instead
        // of a join which causes only an S lock on catalog_product_entity rather
        // than an X lock. An X lock on a table causes an S lock on all foreign keys
        // so using a separate query here significantly reduces the number of
        // unnecessarily locked rows in other tables, thereby avoiding deadlocks.
        $select = $this->_getWriteAdapter()->select()
        ->from($productTable, ['entity_id', 'type_id'])
        ->where('entity_id IN(?)', $productIds);
        $typeIds = $this->_getWriteAdapter()->fetchPairs($select);
        foreach ($rows as &$row) {
        $row['type_id'] = $typeIds[$row['product_id']];
        }
        return $rows;
    }

}