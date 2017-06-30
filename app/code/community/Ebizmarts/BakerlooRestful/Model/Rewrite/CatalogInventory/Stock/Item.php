<?php

class Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{

    const BACKORDERS_YES = 'Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item';

    /**
     * Retrieve Stock Availability
     *
     * @return bool|int
     */
    public function getIsInStock()
    {
        if(Mage::registry(Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES))
            return true;

        return parent::getIsInStock();
    }

    /**
     * Retrieve backorders status
     *
     * @return int
     */
    public function getBackorders()
    {

        if(Mage::registry(Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES))
            return Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY;


        return parent::getBackorders();

   }

   public function updateModifiedDate() {

       if(!$this->getId())
           return;

       $tableName = $this->_getResource()->getTable('cataloginventory/stock_item');

       $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
       $sql = 'UPDATE '.$tableName.' SET `updated_at` = ? WHERE `item_id` = ?';
       $connection->query($sql, array(Varien_Date::now(), $this->getId()));
   }
}