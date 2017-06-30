<?php

class Icommerce_SearchResult_Model_Observer
{

// Cron that runs once every hour
    public function cronResetSearchResults()
    {
        try {
            if (Mage::helper("searchresult")->getConfigData("reload")==1) {
                $model = Mage::getResourceModel('catalogsearch/fulltext');
                $model->cronResetSearchResults();
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * If num_results > 0 then check that we have results in table catalogsearch_result
     * a truncate could have happened.
     * @param $observer
     */
    public function catalogsearch_query_load_after($observer) {

        // Do not trigger more events, read direct from DB
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Check if table catalogsearch_fulltext are truncated, then rebuild the table.
        $query = "select count(product_id) as num_results from catalogsearch_fulltext";
        $db_num_results = $db->fetchOne($query);
        if ($db_num_results==0) {
            // There are no data in the table, Magento should have done that, now we have to repopulate the table.
            // Mage_CatalogSearch_Model_Indexer_Fulltext
            Mage::getSingleton('catalogsearch/indexer_fulltext')->reindexAll();
            $observer->getEvent()->getDataObject()->setData('is_processed', '0');
        }

        // Occur before prepareResult, we still have a chance to change is_processed=0 and get a search done.
        $obj=$observer->getDataObject();
        if ($obj->getData("num_results")==0) { return; }

        // We can not really know if there are data in catalogsearch_result unless we check

        $query = "select count(query_id) as num_results from catalogsearch_result where query_id=:queryid";
        $binds=array('queryid'=>$obj->getData("query_id"));
        $db_num_results = $db->fetchOne($query,$binds);

        if ($db_num_results != $obj->getData("num_results")) {
            $observer->getEvent()->getDataObject()->setData('is_processed', '0');
            $observer->getEvent()->getDataObject()->setData('num_results', $db_num_results);
        }

    }

    /**
     * Check is_processed in table catalogsearch_query:
     * If origdata = 1 and db = 0, then change data to 0 because then a truncahe have happend
     * @param $observer
     * @return mixed
     */
    public function catalogsearch_query_save_before($observer) {
        // Occur after prepareResult
        $obj=$observer->getDataObject();

        // If we want to save a 0 then we could just leave and save it. This case will probably not happen
        if ($obj->getData("is_processed")=="0") { return; }
        // An inactive search word, then we don't care
        if ($obj->getData("is_active")=="0") { return; }
        // Empty search results, we can let them pass
        if ($obj->getData("num_results")==0) { return; }

        // Do not trigger more events, read direct from DB
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $query = "select is_processed from catalogsearch_query where query_id=:queryid";
        $binds=array('queryid'=>$obj->getData("query_id"));
        $db_is_processed = $db->fetchOne($query,$binds);
        $orig_is_processed = $obj->getOrigData('is_processed');

        if (is_null($orig_is_processed)==true) { return; }

        if ($orig_is_processed=="1" and $db_is_processed == "0") {
            $observer->getEvent()->getDataObject()->setData('is_processed', '0');
        }
    }
}
