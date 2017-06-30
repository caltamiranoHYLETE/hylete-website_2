<?php

class Icommerce_Products
{

    /**
     * Convert product or SKU to ID
     *
     * @static
     * @param int|string|Varien_Object $product_or_id Object or SKU to convert to ID
     * @return boolean $force_sku  Set to true to force SKU interpretation
     */
    static function toId($product_or_id, $force_sku = false){
        if ($product_or_id instanceof Varien_Object) {
            return $product_or_id->getId();
        } else {
            if ($force_sku || !Icommerce_Utils::isInteger($product_or_id)) {
                return  self::getIdBySku($product_or_id);
            } else {
                return $product_or_id;
            }
        }
    }

    # Extracts list of associated product IDs
    static function getAssociatedProductIds($configurableProduct)
    {
        if (!($configurableProduct === null) && ($configurableProduct->getHasOptions() == '1')) {

            $configurableProductId = $configurableProduct->getId();

            $read = Icommerce_Db::getDbRead();

            $sql = "";
            $sql .= "SELECT product_id ";
            $sql .= "FROM `catalog_product_super_link` ";
            $sql .= "WHERE parent_id='$configurableProductId'";

            $r_prod = $read->query($sql);

            $simpleProductIdArray = array();

            foreach ($r_prod as $rr) {
                $simpleProductIdArray[] = $rr['product_id'];
            }

            return $simpleProductIdArray;
        }
    }

    //  This function takes a product as argument and if the product is configurable it
    //  returns an array with it's associated products //Simon
    static function getAssociatedProducts($configurableProduct)
    {

        $simpleProductArray = array();

        if (!($configurableProduct === null) && ($configurableProduct->getHasOptions() == '1')) {

            $simpleProductIdArray = self::getAssociatedProductIds($configurableProduct);

            foreach ($simpleProductIdArray as $simpleProdId) {
                $prodModel = Mage::getModel('catalog/product');
                $simpleProductArray[] = $prodModel->load($simpleProdId);
            }
        }

        return $simpleProductArray;
    }

    static $_link_types = array();

    static function getProductLinkTypeId($type)
    {
        if (!array_key_exists($type, self::$_link_types)) {
            $read = Icommerce_Default::getDbRead();
            $sql = "SELECT link_type_id FROM catalog_product_link_type WHERE code='$type'";
            $r = $read->query($sql);
            if (!$r) return null;
            foreach ($r as $rr) {
                $id = $rr['link_type_id'];
            }
            self::$_link_types[$type] = $id;
        }
        return self::$_link_types[$type];
    }

    // Function to extract linked products (relation, bundle, super, up_sell, cross_sell)
    static function getLinkedProductIds($product, $link_type = "relation")
    {
        if (!$product) return null;

        // Sort out link type
        $link_type_id = self::getProductLinkTypeId($link_type);
        if ($link_type_id === null) return null;

        // Get IDs
        $pid = $product->getId();
        $sql = "SELECT linked_product_id FROM catalog_product_link WHERE product_id='$pid' AND link_type_id='$link_type_id'";
        return Icommerce_Db::getColumn($sql);
    }

    static function getLinkedProducts($product, $link_type = "relation", $attribs = array("name", "sku", "image", "description", "price"))
    {

        // Load a collection
        $ids = self::getLinkedProductIds($product, $link_type);
        if ($ids === null) return null;

        $coll = Mage::getModel('catalog/product')->getCollection();
        $coll->addAttributeToSelect($attribs);
        $coll->addFieldToFilter("entity_id", array("in" => $ids));

        return $coll;
    }

    // Function to set linked products (relation, bundle, super, up_sell, cross_sell)
    static function setLinkedProductIds($product, $ids, $link_type)
    {
        if (!$product) return null;

        // Sort out link type
        $link_type_id = self::getProductLinkTypeId($link_type);
        if ($link_type_id === null) return null;

        $write = Icommerce_Default::getDbWrite();
        $pid = $product->getId();
        $sql = "DELETE FROM catalog_product_link WHERE product_id=$pid AND link_type_id=$link_type_id;";

        $allIds = "";


        foreach ($ids as $id) {
            #$allIds .= $id.",";
            $sql .= "INSERT INTO catalog_product_link SET product_id=$pid, linked_product_id=$id,link_type_id=$link_type_id;\n";
        }

        #$attributeId = Icommerce_Default::getAttributeId("child_products");

        #$allIds = rtrim($allIds,",");

        #$sql .= "DELETE FROM catalog_product_entity_varchar WHERE entity_id=$pid AND attribute_id=$attributeId AND entity_type_id=10;\n";
        #$sql .= "INSERT INTO catalog_product_entity_varchar SET entity_type_id=10, attribute_id=$attributeId, store_id=0, value='$allIds', entity_id=$pid";

        $r = $write->query($sql);

        return $r ? true : false;
    }

    static $_prod;

    public static function getIdBySku($sku)
    {
        if (!self::$_prod) {
            self::$_prod = Mage::getModel("catalog/product");
        }
        return self::$_prod->getIdBySku($sku);
    }

    /**
     * Lookup product SKU from its ID
     *
     * @static
     * @param int|array $ids Single product ID or an array of ids
     * @return null|string|array null, single SKU or an array of lookups ID => SKUs
     */
    public static function getSkuFromId($ids)
    {
        if (is_array($ids)) {
            $ids = implode(",", $ids);
        }
        $skus = Icommerce_Db::getAssociativeArray("SELECT entity_id,sku FROM catalog_product_entity WHERE entity_id in ($ids)");
        /*$rd = Icommerce_Db::getDbRead();
        $sql = "SELECT entity_id,sku FROM catalog_product_entity WHERE entity_id in ($ids)";
        $r = $rd->query( $sql );
        $skus = array();
        foreach( $r as $rr ){
            $skus[$rr["entity_id"]] = $rr["sku"];
        }*/
        if (strpos($ids, ",") === FALSE) {
            foreach ($skus as $id => $sku) {
                return $sku;
            }
            return null;
        }
        return $skus;
    }

    /**
     * Lookup product ID from its SKU
     *
     * @static
     * @param string|array $skus Single product SKU or an array of SKUs
     * @return null|string|array null, single ID or an array of lookups SKU => IDs
     */
    public static function getIdFromSku($skus)
    {
        if (is_array($skus)) {
            $ids = array();
            foreach ($skus as $sku) {
                $ids[] = self::getIdBySku($sku);
            }
            return $ids;
        }
        return self::getIdBySku($skus);

        //return Icommerce_Db::getValue( "SELECT entity_id FROM catalog_product_entity WHERE sku='$skus'" );

        /*
        // More optimal loop for multi SKU case
        $rd = Icommerce_Db::getDbRead();
        $sql = "SELECT entity_id FROM catalog_product_entity WHERE sku in ($skus)";
        $r = $rd->query( $sql );
        $ids = array();
        foreach( $r as $rr ){
            $ids[$rr["sku"]] = $rr["entity_id"];
        }
        if( strpos($skus,",")===FALSE ){
            foreach( $ids as $sku => $id ){
                return $id;
            }
            return null;
        }
        return $ids;
        */
    }


    /**
     * Load a product given its entity_id
     *
     * @static
     * @param int $id Entity_id of the product to load
     * @return Mage_Catalog_Model_Product
     */
    public static function loadById($id)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');
        $product->load($id);
        return $product->getId() ? $product : null;
    }

    /**
     * Load a product given its SKU
     *
     * @static
     * @param string $sku SKU of the product to load
     * @return null|Mage_Catalog_Model_Product Returns the product if found, null otherwise
     */
    public static function loadBySku($sku)
    {
        $id = self::getIdBySku($sku);
        return self::loadById($id);
    }

    // Get all products that are enabled and visible
    static function getAllIds($also_disabled = false)
    {
        if (!$also_disabled) {
            $aid_vis = Icommerce_Eav::getAttributeId("visibility", "catalog_product");
            $aid_enab = Icommerce_Eav::getAttributeId("status", "catalog_product");
            $vis_cat = Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG;
            $vis_srch = Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH;
            $vis_both = Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH;
            $ids = Icommerce_Db::getColumn("SELECT cpe.entity_id FROM catalog_product_entity as cpe, catalog_product_entity_int as cpei1, catalog_product_entity_int as cpei2
                                             WHERE cpei1.entity_id=cpe.entity_id AND cpei1.attribute_id=$aid_vis AND cpei1.value IN ($vis_cat,$vis_srch,$vis_both)
                                             AND cpei2.entity_id=cpe.entity_id AND cpei2.attribute_id=$aid_enab AND cpei2.value=1");
        } else {
            $ids = Icommerce_Db::getColumn("SELECT cpe.entity_id FROM catalog_product_entity as cpe");
        }
        return array_unique($ids);
    }


    static function createEmptyProduct($sku, $aset = null, $tax_class = null, $type_id = "simple")
    {

        if ($aset == null) {
            $aset = "Default";
        }
        if (!Icommerce_Utils::isInteger($aset)) {
            if (!($aset_id = Icommerce_Eav::getAttributeSetId($aset, "catalog_product"))) {
                if ($aset == "Default") {
                    $aset_id = Icommerce_Eav::getAttributeSetId("Standard", "catalog_product");
                }
            }
        } else {
            $aset_id = $aset;
        }
        if (!$aset_id) return null;

        if ($tax_class == null) {
            $tax_cls_id = Icommerce_Db::getValue("SELECT min(class_id) FROM tax_class WHERE class_type='PRODUCT'");
        }
        else if (!Icommerce_Utils::isInteger($tax_class)) {
            $tax_cls_id = Icommerce_Db::getValue("SELECT class_id FROM tax_class WHERE class_type='PRODUCT' AND class_name='$tax_class'");
        } else {
            $tax_cls_id = $tax_class;
        }
        if (!$tax_cls_id) return null;

        $stock_data = array(
            'is_in_stock' => true,
            'manage_stock' => false,
            'use_config_manage_stock' => false);

        $prod = Mage::getModel("catalog/product");
        $prod->setData("sku", $sku);


        // Set some predefined values on attributes
        // 100% of the price is on the custom option
        $prod->setData("price", 0);
        $prod->setData("type_id", $type_id);
        $prod->setData("attribute_set_id", $aset_id);
        $prod->setData("stock_data", $stock_data);
        $prod->setData("status", 1);
        $prod->setData("visibility", 1);
        $prod->setData("tax_class_id", $tax_cls_id);

        return $prod;
    }

    /**
     * Return array of stock item info
     *
     * @static
     * @param int|string|Mage_Catalog_Model_Product $product_or_id The product (or ID) to get info for
     * @param int|string $stock_id The stock (multi site stock) to get ID for
     */
    public static function getCurrentStockStatus($productId, $stock_id = 1)
    {
        // Function returns a stock item, not stock status
        if ($productId instanceof Varien_Object) {
            $productId = $productId->getId();
        }

        /*
         * this is the fastest solution, returns an array of qty, is_in_stock, stock_id, manage_stock, use_config_manage_stock /magnus
         * Added min_qty to array, need it for changeStockData
         */
        if (!isset($productId) || trim($productId) == '') $productId = 0;
        $row = Icommerce_Db::getRow("SELECT item_id, is_in_stock, stock_id, manage_stock, use_config_manage_stock, qty, min_qty, use_config_min_qty
                                     FROM cataloginventory_stock_item WHERE product_id = ? AND stock_id=?",
                                    array($productId, $stock_id));
        if ($row['min_qty']<=0 && $row['use_config_min_qty']==1) {
            $row['min_qty'] = Mage::getStoreConfig('cataloginventory/item_options/min_qty');
        }
        return $row;
    }

    /**
     * Change product inventory in fast or slow way.
     * Can be called after finishing adjusting stock status on an array of simple products.
     *
     * @static
     * @param int|string|Mage_Catalog_Model_Product $product_or_id The product (or ID) to modify
     * @param int|string|array $stock_data_or_qty Either raw quantity value, or array of stock data
     * @param bool $force_sku Force ID to be translated as a SKU
     * @param $locker Object that can be used to serialize inventory saves
     * @return null|int Returns null if failed, 1 if no change, 2 if fast SQL change, 3 if change by stock item + indexer
     */
    static function changeStockData($product_or_id, $stock_data_or_qty, $force_sku = false, $locker=null )
    {
        $pid = self::toId( $product_or_id, $force_sku );
        if (!$pid) return null;

        if (is_array($stock_data_or_qty)) {
            $stock_data = $stock_data_or_qty;
        } else if (Icommerce_Utils::isInteger($stock_data_or_qty)) {
            $stock_data = array("qty" => $stock_data_or_qty);
        } else {
            return null;
        }

        // Multi site stock - Fallback via argument, to product, to config to global
        $stock_id = null;
        if( isset($stock_data["stock_id"]) ) $stock_id = $stock_data["stock_id"];
        if( !$stock_id ){
            if( $product_or_id instanceof Mage_Catalog_Model_Product ){
                $stock_id = $product_or_id->getData("stock_id");
            }
            if( !$stock_id ){
                $stock_id = Mage::getStoreConfig( "storestock/settings/stock_id" );
                if( !$stock_id ) $stock_id = 1;
            }
        }

        $curstock = self::getCurrentStockStatus($pid,$stock_id);

        $min_qty = isset($curstock['min_qty']) ? $curstock['min_qty'] : 0;
        if (!isset($stock_data['is_in_stock'])) {
            $qty = $stock_data['qty'];
            if ($qty>$min_qty) {
                $is_in_stock = 1;
            } else {
                $is_in_stock = 0;
            }
            $stock_data['is_in_stock'] = $is_in_stock;
        } else {
            $is_in_stock = $stock_data['is_in_stock'];
        }


        $sql_update = true;
        if ($curstock) {
            // Detect do-nothing cases
            if ($stock_data['qty'] == $curstock['qty'] && $stock_data['is_in_stock'] == $curstock['is_in_stock']) return 1;
            if ($stock_data['qty'] == 0 && $curstock['qty'] == 0) return 1; // If zero, ignore is_in_stock parameter sent down

            // Have to go slow way ?
            if (($stock_data['is_in_stock'] != $curstock['is_in_stock']) ||
                ($stock_data['qty'] <= $min_qty && $curstock['qty'] > $min_qty) ||
                ($stock_data['qty'] > $min_qty && $curstock['qty'] <= $min_qty) ||
                ($stock_data['qty'] < $min_qty && $curstock['qty'] < $min_qty) ){
                $sql_update = false;
            }
        } else {
            $sql_update = false;
        }

        if ($sql_update) {
            $transaction = Icommerce_Db::getDbWrite();
            try {
                $transaction->beginTransaction();

                $bind = array($is_in_stock,$stock_data['qty'],$pid, $stock_id);
                $sql="UPDATE cataloginventory_stock_item SET is_in_stock=?, qty=? WHERE product_id = ? AND stock_id = ?";
                $transaction->query($sql,$bind);
                $sql="UPDATE cataloginventory_stock_status SET stock_status=?, qty=? WHERE product_id = ? AND stock_id = ?";
                $transaction->query($sql,$bind);

                $transaction->commit();
                return 2;
            } catch (Exception $e) {
                $transaction->rollback();
                return null;
            }
/* Removed because of BB-771, the above is the new code.
            try {
                Icommerce_Db::write( "UPDATE cataloginventory_stock_item s_i, cataloginventory_stock_status s_s
                   SET   s_i.qty = '" . $stock_data['qty'] . "', s_i.is_in_stock = ?,
                         s_s.qty = '" . $stock_data['qty'] . "', s_s.stock_status = ?
                   WHERE s_i.product_id = '" . $pid . "' AND s_i.product_id = s_s.product_id
                   AND s_i.stock_id=? AND s_s.stock_id=?", array($is_in_stock,$is_in_stock,$stock_id,$stock_id) );
                return 2;
            } catch (Mage_Core_Exception $e) {
                return null;
            }
*/
        }

        // Go by stock item
        $stock_item = Mage::getModel( "cataloginventory/stock_item" );

        if($curstock) {
            $stock_item->load($curstock['item_id']);
        } else {
            $stock_item->setStockId( $stock_id );
            $stock_item->loadByProduct( $pid );
            $stock_item->setData("product_id",$pid);
        }

        foreach( $stock_data as $k => $v ){
            $stock_item->setData( $k, $v );
        }
        // Not sure these variables are neccessary, but they won't hurt
        if (!isset($stock_data['manage_stock'])) {
            $stock_data['manage_stock'] = 1;
        }
        if (!isset($stock_data['use_config_manage_stock'])) {
            $stock_data['use_config_manage_stock'] = 0;
        }

        // If we got a locker, use it
        if( !is_object($locker) || !method_exists($locker,"getLock") || !method_exists($locker,"releaseLock") ) $locker = null;
        if( $locker ){
            $tries = 0;
            while( !$locker->getLock("changeStockData", 1) && $tries<20 ) {
                Icommerce_Default::logAppend( "changeStockData - waiting for lock - $tries", "var/log/stockItem_locker.log" );
                $tries++;
                usleep(rand(200, 1000) * 1000);
            }
            if( $tries>=20 ){
                Icommerce_Default::logAppend( "changeStockData - early quit - failed obtain lock - $tries", "var/log/stockItem_locker.log" );
                return null;
            }
        }

        $e = null;
        try {
            $stock_item->save( );
        } catch( Exception $e ){ }
        if( $locker ) $r = $locker->releaseLock("changeStockData");
        if( $e ) throw $e;

        return 3;
    }


    /**
     * Adjust configurable product stock status, depending on its simple products availability.
     * Can be called after finishing adjusting stock status on an array of simple products.
     *
     * @static
     * @param array $pids Product IDs that have been affected. Can be mix of simple and config Ids
     * @return null
     */
    public static function checkAdjustConfigurableStockStatus( $pids ){
        if( !$pids ) return;
        if( !is_array($pids) ){
            $pids = array($pids);
        }

        // Get lookup array, configurable ID to simple IDs
        $pid_str = implode(",",Icommerce_Db::wrapQueryValues($pids));
        $types = Icommerce_Db::getAssociative( "SELECT entity_id,type_id FROM catalog_product_entity WHERE entity_id IN ($pid_str)" );
        $configs = array();
        $simples = array();
        foreach( $types as $eid => $type ){
            if( $type=="configurable" ){
                $configs[$eid] = true;
            }
            else if( $type=="simple" ){
                $simples[] = $eid;
            }
        }

        if( $simples ){
            $pid_str = implode(",",Icommerce_Db::wrapQueryValues($simples));
            $cfgs2 = Icommerce_Db::getColumn( "SELECT DISTINCT parent_id FROM catalog_product_relation WHERE child_id IN ($pid_str)" );
            foreach( $cfgs2 as $cfg_id ){
                $configs[$cfg_id] = true;
            }
        }

        $stock_ids = Icommerce_Db::getColumn( "SELECT stock_id FROM cataloginventory_stock" );

        // Have array of configurable product IDs, process then
        $reindex_arr = array();
        foreach( $configs as $cfg_id=>$v ){
            // Get the count of simple products that are in stock
            foreach( $stock_ids as $stock_id ){
                $cnt = Icommerce_Db::getValue( "SELECT count(qty) FROM cataloginventory_stock_item WHERE stock_id=? AND qty>min_qty AND product_id IN (SELECT child_id
                                                FROM catalog_product_relation WHERE parent_id=?)", array($stock_id,$cfg_id) );
                $row = Icommerce_Db::getRow( "SELECT is_in_stock, manage_stock FROM cataloginventory_stock_item WHERE stock_id=? AND product_id=?", array($stock_id,$cfg_id) );
                $stock_status = Icommerce_Db::getValue( "SELECT stock_status FROM cataloginventory_stock_status WHERE stock_id=? AND product_id=?", array($stock_id,$cfg_id) );
                // Change is_in_stock on configurable ?
                if( (bool)$cnt != (bool)$row["is_in_stock"] || !$row["manage_stock"] ){
                    Icommerce_Db::write( "UPDATE cataloginventory_stock_item SET manage_stock=1, is_in_stock=? WHERE product_id=? AND stock_id=?",
                                          array($cnt?1:0,$cfg_id,$stock_id) );
                    $reindex_arr[$cfg_id] = true;
                } else if( $stock_status!=$row["is_in_stock"] ){
                    // If index table out of sync, reindex anyhow
                    $reindex_arr[$cfg_id] = true;
                }
            }
        }
        $reindex_arr = array_keys( $reindex_arr );

        while( count($reindex_arr)>0 ){
            // We do this one-by-one - had weird issues calling indexing functions with array of indeces (!?)
            $pid = array_pop( $reindex_arr );
            self::reindexStockPrice( $pid );
        }

        return count($reindex_arr);
    }

    /**
     * Adjust configurable product stock status, depending on its simple products availability.
     * Can be called after finishing adjusting stock status on an array of simple products.
     *
     * @static
     * @param array $pids Product IDs that have been affected. Can be mix of simple and config Ids
     * @return null
     */
    public static function reindexStockPrice( $pids, $known_type=null ){

        if( !$pids ) return;
        if( Icommerce_Utils::isInteger($pids) ) $pids = array($pids);

        $type_pids = array();
        if( $known_type ){
            static $st_known_types = array( "simple"=>true, "configurable"=>true, "grouped"=>true );
            if( !isset($st_known_types[$known_type]) ){
                Mage::throwException( "reindexStockPrice - unknown product type indexer" );
            }
            $type_pids[$known_type] = $pids;
        } else {
            $pid_str = implode( ",",Icommerce_Db::wrapQueryValues($pids) );
            $types = Icommerce_Db::getAssociative( "SELECT entity_id,type_id FROM catalog_product_entity WHERE entity_id IN ($pid_str)" );
            $type_pids["configurable"] = array();
            $type_pids["simple"] = array();
            foreach( $types as $eid => $type ){
                if( $type=="configurable" ){
                    $type_pids["configurable"][] = $eid;
                }
                else if( $type=="simple" ){
                    $type_pids["simple"][] = $eid;
                }
            }
        }

        // Have to reindex simple level BEFORE reindexing configurable level
        if( isset($type_pids["configurable"]) ){
            if( $pid_str = implode( ",", $type_pids["configurable"] ) ){
                $pids_simple = Icommerce_Db::getAssociative( "SELECT child_id FROM catalog_product_relation WHERE parent_id IN ($pid_str)" );
                $ids = isset($type_pids["simple"]) && $type_pids["simple"] ? array_combine(array_values($type_pids["simple"]),array_keys($type_pids["simple"])) : array();
                //$ids = array_merge( $pids_simple, $ids );
                foreach( $pids_simple as $eid => $val ){
                    $ids[$eid] = true;
                }
                $type_pids["simple"] = array_keys( $ids );
            }
        }


        static $st_indexer_stock;
        if( !$st_indexer_stock ){
            $st_indexer_stock = Mage::getResourceSingleton( "cataloginventory/indexer_stock" );
        }
        //$st_indexer_stock->reindexProducts( $pids );

        static $st_type_indexers;
        if( !$st_type_indexers ){
            $st_type_indexers["simple"] = Mage::getResourceSingleton( "catalog/product_indexer_price_default" );
            $st_type_indexers["configurable"] = Mage::getResourceSingleton( "catalog/product_indexer_price_configurable" );
            $st_type_indexers["grouped"] = Mage::getResourceSingleton( "catalog/product_indexer_price_grouped" );
        }

        // This may be a bit raw, but temp table may not be empty at this point
        $tbl = $st_type_indexers["simple"]->getIdxTable();
        Icommerce_Db::write( "truncate $tbl" );

        //foreach( $type_pids as $type => $pids ){
        foreach( array("simple","configurable","grouped") as $ix => $type ){
            $pids = isset($type_pids[$type]) ? $type_pids[$type] : null;
            if( $pids ){
                $st_indexer_stock->reindexProducts( $pids );
                $st_type_indexers[$type]->setTypeId($type)->reindexEntity( $pids );
                self::copyIndexDataToMainTable( $st_type_indexers[$type], $pids );
            }
        }


            // Below indexing is what product saves doesm but it will run everything "per product"
            /*$price_indexer = Mage::getResourceModel( "catalog/product_indexer_price" );
            $event = Mage::getModel( "index/event" );

            $ts = microtime( true );

            //$type_ids = Icommerce_Db::getAssociative( "SELECT entity_id, type_id FROM catalog_product_entity WHERE entity_id IN ()" );
            foreach( $reindex_arr as $id ){
                $event->setData( "entity_pk", $id );
                $new_data = array( "reindex_price" => 1, "product_type_id"=> $types[$id] );
                $event->setData( "new_data", $new_data );
                $price_indexer->catalogProductSave( $event );
            }*/
    }


    /**
     * Load a product given its SKU or its entity_id.
     * @param mixed $sku_or_id SKU or entity_id of the product to load
     * @param boolean $force_int_check Force checking for integers, in which case it is assumed that $sku_or_id is the entity_id
     * @return Mage_Catalog_Model_Product|Varien_Object Returns a product object with data loaded if product was found. Returns an unaltered Varien_Object if a Varien_Object was passed into $sku_or_id (assumes this was a Product object already)
     *
     * @deprecated since 2011/11/21
     * @see Icommerce_Products::loadById($id)
     * @see Icommerce_Products::loadBySku($sku)
     */
    public static function load($sku_or_id, $force_int_check = false)
    {
        // Initial checks
        if (!$sku_or_id) return null;
        if ($sku_or_id instanceof Varien_Object) {
            return $sku_or_id;
        }

        $prod = Mage::getModel("catalog/product");
        // Is it an integer?
        if ((!$force_int_check || !strcmp((int)$sku_or_id, $sku_or_id)) || is_int($sku_or_id)) {
            $prod->load($sku_or_id);
        }
        if (!$prod->getId()) {
            // Try SKU
            $id = self::getIdBySku($sku_or_id);
            if (!$id) {
                // Try URL path
                $aid = Icommerce_Eav::getAttributeId("url_path");
                if ($aid) {
                    $url_val = Icommerce_Db::wrapQueryValues($sku_or_id);
                    $id = Icommerce_Db::getSingleton("SELECT entity_id FROM catalog_product_entity_varchar WHERE value=$url_val AND attribute_id=$aid");
                    if (!$id) return null;
                }
            }
            $prod->load($id);
        }
        return $prod;
    }

    /**
     * Find out stock level for a product
     * @param mixed $prod_or_id The product object or ID
     * @param string $multi_algo How to process products of mixed type (configurable, grouped)
     * @return int|null Stock level, or null if product not found
     */
    public static function getStockLevel($prod_or_id, $multi_algo = "sum")
    {
        // ## This could be cached for better performance

        // We could do something more clever here, for configurable and grouped product types
        // Like loading the number of the one with most or lowest in stock, or the sum of them
        $pid = Icommerce_Db::toId($prod_or_id);
        $qty = Icommerce_Db::getValue("SELECT qty FROM cataloginventory_stock_status WHERE product_id=? AND
                                             website_id=?", array($pid, Icommerce_Default::getWebsiteId()));
        if (!strcmp($qty, (int)$qty)) return $qty;

        // Should use multi algo ?
        if (!$multi_algo) return null;
        if (strpos("sumavgminmax", $multi_algo) === false) return null;

        $type = ($prod_or_id instanceof Varien_Object ? $prod_or_id->getData("type_id") :
            Icommerce_Db::getValue("SELECT type_id FROM catalog_product_entity WHERE entity_id=?", array($prod_or_id)));
        $pids = null;
        switch ($type) {
            case "configurable":
                $pids = Icommerce_Db::getColumn("SELECT product_id FROM catalog_product_super_link WHERE parent_id=?", array($pid));
                break;

            case "grouped":
                $pids = Icommerce_Db::getColumn("SELECT linked_product_id FROM catalog_product_link WHERE product_id=? AND link_type_id=?",
                    array($pid, Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED));
                break;
        }
        if (!$pids) return null;

        // Have pids, see if they are enabled and apply
        $aid_status = Icommerce_Eav::getAttributeId("status", "catalog_product");
        $pid_str = implode(",", Icommerce_Db::wrapQueryValues($pids));
        return Icommerce_Db::getValue("SELECT $multi_algo(qty) FROM cataloginventory_stock_status as s
                                        INNER JOIN catalog_product_entity_int as cpei ON s.product_id=cpei.entity_id AND
                                                   cpei.attribute_id=? WHERE
                                        s.product_id IN ($pid_str) AND s.website_id=? AND
                                        cpei.value!=2",
            array($aid_status, Icommerce_Default::getWebsiteId()));
    }

    static $priceBlock;

    public static function getPriceHtml($product)
    {
        if (!self::$priceBlock) {
            self::$priceBlock = Mage::getSingleton('core/layout')->createBlock('catalog/product')->setTemplate('catalog/product/price.phtml');
        }
        self::$priceBlock->setProduct($product);
        return self::$priceBlock->toHtml();
    }


    /**
     * Calculate discount percentage for product
     * @param Mage_Catalog_Model_Product product The product to investigate
     * @return int|string Percentage discount or '' if no discount
     */
    public static function getDiscountPercent($product)
    {
        // special_price not always loaded...
        $price = $product->getData("price");
        $discount_price = Icommerce_Default::getLoadModelData($product, "special_price");
        if (!$discount_price || $price == $discount_price) return "";
        return round(100 * ($price - $discount_price) / $price);
    }

    /**
     * Get tax/VAT percentage for product
     * @param Mage_Catalog_Model_Product product The product to investigate
     * @return int Percentage tax
     */
    public static function getTaxPercent($product)
    {
        $taxClassId = $product->getTaxClassId();
        $taxClasses = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
        return $taxClasses["value_" . $taxClassId];
    }


    /**
     * Calculate discount percentage for product
     * @param Mage_Catalog_Model_Produc|int product The product to investigate
     * @return string|null Rating (0 to 100) or null if none
     */
    public static function getRating($prod)
    {
        $pid = $prod instanceof Varien_Object ? $prod->getId() : $prod;
        $sids = "0," . Icommerce_Default::getStoreId();
        $val = Icommerce_Db::getValue("SELECT rating_summary FROM review_entity_summary WHERE entity_type=1 AND entity_pk_value=? AND store_id IN (?)", array($pid, $sids));
        return $val;
    }


    /**
     * Load a product given its SKU or its entity_id (assumes entity_id when $sku_id_url is an integer or a string that represents an integer)
     * @param mixed $sku_id_url An SKU, entity_id or the URL of a product to load
     * @return Mage_Catalog_Model_Product|Varien_Object Returns a product object with data loaded if product was found. Returns an unaltered Varien_Object if a Varien_Object was passed into $sku_or_id (assumes this was a Product object already)
     *
     * @deprecated since 2011/11/21
     * @see Icommerce_Products::loadById($id)
     * @see Icommerce_Products::loadBySku($sku)
     */
    public static function loadSmart($sku_id_url)
    {
        return self::load($sku_id_url, true);
    }


    /**
     * Since _copyIndexDataToMainTable is protected in Mage_Catalog_Model_Resource_Product_Indexer_Price (and we do not want the full indexing)
     * we need a local version of it, to move reindexed data to target tables.
     */
    public static function copyIndexDataToMainTable($indexer, $processIds)
    {
        $write = Icommerce_Db::getWrite();
        $write->beginTransaction();
        try {
            // remove old index
            $where = $write->quoteInto('entity_id IN(?)', $processIds);
            $write->delete($indexer->getMainTable(), $where);

            // remove additional data from index
            $where = $write->quoteInto('entity_id NOT IN(?)', $processIds);
            $write->delete($indexer->getIdxTable(), $where);

            // insert new index
            $indexer->useDisableKeys(false);
            $indexer->insertFromTable($indexer->getIdxTable(), $indexer->getMainTable());
            $indexer->useDisableKeys(true);

            $indexer->commit();
        } catch (Exception $e) {
            $indexer->rollBack();
            return false;
        }

        return true;
    }

    public static function getStockId(){
        static $st_stock_id;
        if( $st_stock_id===null ){
            // Default to 1 - Magento default
            $st_stock_id = 1;

            // Icommerce_StoreStock
            if( $store_stock_id = Mage::getStoreConfig("storestock/settings/stock_id") ){
                $st_stock_id = $store_stock_id;
            } else {
                // Icommerce_Multistock
                $stock_name = Mage::getStoreConfig( "cataloginventory/options/stock_name");
                if( $stock_name && strlen($stock_name)>0 ){
                    $st_stock_id = Icommerce_Db::getValue( "SELECT stock_id FROM cataloginventory_stock WHERE stock_name=?", array($stock_name) );
                }
            }
        }

        return $st_stock_id;
    }

}

