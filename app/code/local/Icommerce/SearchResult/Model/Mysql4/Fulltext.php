<?php

class Icommerce_SearchResult_Model_Mysql4_Fulltext extends Mage_CatalogSearch_Model_Mysql4_Fulltext
{
// Code from Mage_CatalogSearch_Model_Mysql4_Fulltext
    public function cronResetSearchResults()
    {
        $this->beginTransaction();
        try {
            $this->_getWriteAdapter()->update($this->getTable('catalogsearch/search_query'), array('is_processed' => 0));
            $this->_getWriteAdapter()->query('TRUNCATE TABLE ' . $this->getTable('catalogsearch/result'));

            Mage::helper("searchresult")->setConfigData("reload",0);

            $this->commit();
        }
        catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }

        Mage::dispatchEvent('catalogsearch_reset_search_result');

        return $this;
    }

    public function resetSearchResults()
    {
        if (Mage::helper("searchresult")->getConfigData("activate")==1) {
            Mage::helper("searchresult")->setConfigData("reload",1);
        } else {
            parent::resetSearchResults();
        }
        return $this;
    }

}
