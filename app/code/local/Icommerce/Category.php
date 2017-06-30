<?php
/**
 * Class that manage categories.
 *
 * @copyright (C) Icommerce 2012
 * @package Icommerce_Category
 * @version
 */
class Icommerce_Category {

    // During loading a category path we can track down where it failed
    static $_last_base, $_last_path;

    // Load a Magento category
    static function load( $base_cat, $path=null ){
        if( $base_cat && !($base_cat instanceof Mage_Catalog_Model_Category) ){
            if( $base_cat && strcmp((int)$base_cat,$base_cat) ){
                if( is_string($base_cat) ){
                    // Make array
                    $base_path = explode( "/", $base_cat );
                    // And load base path from root
                    $base_cat = self::load( null, $base_path );
                    if( !$base_cat ){
                        return null;
                    }
                }
            }
            else {
                $base_cat = Mage::getModel( "catalog/category" )->load( $base_cat );
                if( !$base_cat->getId() ) {
                    return null;
                }
            }
        }
        if( is_null($path) ) return $base_cat;

        // Now $base_cat is a Magento category
        // and $path is either a string or an array of identifiers
        if( !is_array($path) ){
            if( is_string($path) ){
                $path = explode( "/", $path );
            } else {
                $path = array($path);
            }
        }

        // Need root cat?
        if( !$base_cat ){
            $base_cat = Mage::getModel("catalog/category")->load(1);
        }

        // Iterate the path
        self::$_last_base = $base_cat;
        self::$_last_path = $path;
        $aid_name = Icommerce_Db::getAttributeId( "name", "catalog_category" );
        $id_now = $base_cat->getId();
        foreach( $path as $k => $cat ){
            // Integer category ID?
            /*if( !strcmp((int)$cat,$cat) ){
                $id_nxt = $cat;
            }
            else*/ {
                // We have admin name of category
                if( strpos($cat,"Cee")!==FALSE ){
                    $x = 1;
                }
                $cat = str_replace( "'", "\'", $cat );
                $sql = "SELECT catalog_category_entity_varchar.entity_id FROM catalog_category_entity, catalog_category_entity_varchar WHERE
                        catalog_category_entity_varchar.attribute_id=$aid_name AND
                        catalog_category_entity_varchar.value='$cat' AND
                        catalog_category_entity.entity_id=catalog_category_entity_varchar.entity_id AND
                        parent_id=$id_now;";
                $id_nxt = Icommerce_Db::getSingleton( $sql );
            }

            if( !$id_nxt ){
                return null;
            }

            unset( $path[$k] );
            $id_now = $id_nxt;
            self::$_last_base = $id_now;
            self::$_last_path = $path;
        }

        // Have it
        $cat = Mage::getModel( "catalog/category" )->load( $id_nxt );
        return $cat;
    }

    // Increment position for repeated category creation
    static $_position_last = 1;

    // Create one or several nested categories
    static function create( $base_cat, $path, $new_cat_special_vals=array() ){
        if( ! ($base_cat instanceof Mage_Catalog_Model_Category) ){
            $base_cat = self::load($base_cat);
            if( ! ($base_cat instanceof Mage_Catalog_Model_Category) ){
                return null;
            }
        }

        // Create all categories in path
        if( !is_array($path) ){
            if( is_string($path) ){
                $path = explode( "/", $path );
            } else {
                $path = array($path);
            }
        }

        // Create all
        foreach( $path as $name ){
            $url = strtolower( str_replace("/","-",$name) );
            $url = strtolower( str_replace(" ","_",$name) );
            $url_path = str_replace( ".html", "", $base_cat->getData("url_path") ) . "/" . $url . ".html";
            $position = max( $base_cat->getData("position"), self::$_position_last+1 );
            self::$_position_last = $position;
            $special_vals = array_merge(
                array(  "parent_id" => $base_cat->getId(),
                        "level" => $base_cat->getData("level")+1,
                        "name" => $name,
                        "url_key" => $url,
                        "url_path" => $url_path,
                        "position" => $position ),
                $new_cat_special_vals );
            $eid = Icommerce_Eav::createEavObject(
                        "catalog_category", $base_cat,
                        $special_vals );
            if( !$eid ){
                return null;
            }
            // Set the path
            $cat = Mage::getModel("catalog/category")->load( $eid );
            Icommerce_Eav::writeEavValue( $cat, "path",
                                          $base_cat->getData("path")."/".$eid,
                                          "catalog_category" );

            $base_cat = $cat;
        }
        return $base_cat;
    }

    static function loadCreate( $path, $base_cat=null, $new_cat_special_vals=array() ){
        $new_cat = self::load( $base_cat, $path );
        if( !$new_cat ){
            // Have to create categories
            $cat_last = self::getCatLoadFailBase();
            $cats = self::getCatLoadFailPath();
            if( !$cat_last || !$cats ){
                throw new Exception( "Icommerce_Category::loadCreate: Failed creating required import categories [getCatLoadFailBase or getCatLoadFailPath]" );
            }
            if( !($new_cat=self::create($cat_last, $cats, $new_cat_special_vals)) ){
                throw new Exception("loadCreate: Failed creating required import categories");
            }
        }
        return $new_cat;
    }


    // Return last category that loaded successully (before failed
    // loading a child category).
    static function getCatLoadFailBase( $as_id=false ){
        $last_base = self::$_last_base;
        if( $as_id ){
            if( self::$_last_base instanceof Varien_Object ){
                return self::$_last_base->getId();
            } else {
                return self::$_last_base;
            }
        } else {
            if( self::$_last_base ){
                if( self::$_last_base instanceof Varien_Object ){
                    return self::$_last_base;
                } else {
                    $cat = Mage::getModel("catalog/category")->load( self::$_last_base );
                    return $cat->getId()==self::$_last_base ? $cat : null;
                }
            } else {
                return null;
            }
        }
    }

    // Return remaining path components where category load failed
    static function getCatLoadFailPath( ){
        return self::$_last_path;
    }

    // Remove products from a category
    // $pids: array or list of products or ids. if null, remove all products.
    static function clearProducts( $cat, $pids=null ){
        // We must clear both references from categeory => product
        // and vice versa
        $cat_id = Icommerce_Db::toId( $cat );

        if( $pids===null ){
            // Get all products currently associated with category
            $pids = Icommerce_Db::getColumn( "SELECT product_id FROM catalog_category_product WHERE category_id=$cat_id" );
            if( is_null($pids) ){
                throw new Exception( "Icommerce_Category::clearProducts - Product ids not found" );
            }
            $pids2 = Icommerce_Db::getColumn( "SELECT product_id FROM catalog_category_product_index WHERE category_id=$cat_id" );
            if( $pids2 ){
                $pids = array_merge( $pids, $pids2 );
            }
        } else {
            $pids = Icommerce_Db::toIdList( $pids );
        }
        $pids = array_unique( $pids );

        // And delete them from category
        $ids = implode( ",",$pids );
        if( !$ids ){
            return true;
        }
        $wr = Icommerce_Db::getWrite();
        $wr->query( "DELETE FROM catalog_category_product WHERE category_id=$cat_id AND product_id IN ($ids)" );
        $wr->query( "DELETE FROM catalog_category_product_index WHERE category_id=$cat_id AND product_id IN ($ids)" );

        // Delete the category reference from each product
        // ## Note: The column category_ids is no more in M1.4.
        if( Icommerce_Default::getMagentoVersion()<1400 ){
            foreach( $pids as $pid ){
                $cat_ids = Icommerce_Eav::readValue( $pid, "category_ids", "catalog_product" );
                if( $cat_ids ){
                    $cat_ids = explode( ",",$cat_ids );
                    $k = array_search( $cat_id, $cat_ids );
                    if( $k!==false ){
                        unset( $cat_ids[$k] );
                        Icommerce_Eav::writeValue( $pid, "category_ids", implode(",",$cat_ids), "catalog_product" );
                    }
                }
            }
        }

        return true;
    }

    static $_pos = 1;
    // Add products to a certain category
    //   $cat:        Category or category ID where to add products
    //   $prods:      Array or list of products or ids.
    //   $visibility: Visibility of added products (search, catalog)
    //   $do_clear:   True to remove previous products from category
    static function addProducts( $cat, $prods, $visibility=4, $do_clear=false, $store_ids=0 ){
        $cat_id = Icommerce_Db::toId( $cat );
        if( $do_clear ){
            self::clearProducts( $cat );
        }
        $pids = Icommerce_Db::toIdList( $prods );
        if( !$pids ) return true;
        $pids = array_unique( $pids );

        // Prepare store ids
        $sids = array();
        if( is_string($store_ids) ){
            $store_ids = explode( ",", $store_ids );
        }
        else if( !is_array($store_ids) ){
            $store_ids = array($store_ids);
        }
        foreach( $store_ids as $sid ){
            if( is_null($sid2 = Icommerce_Default::prepareStoreId( $sid )) ){
                throw new Exception( "Icommerce_Category::addProducts: no such store: $sid" );
            }
            $sids[] = $sid2;
        }
        if( !$sids ){
             $sids = array( 0 );    // Admin only
        }

        // Prepare values string
        $values = $values_delete = "";
        $values_index = "";
        foreach( $pids as $pid ){
            if( $values ){
                 $values .= ",";
                 $values_delete .= " OR";
                 //$values_index_delete .= " OR";
            }
            $pos = self::$_pos++;
            $values .= "($cat_id,$pid,$pos)";
            foreach( $sids as $sid ){
                if( $values_index ){
                    $values_index .= ",";
                }
                $values_index .= "($cat_id,$pid,$visibility,$pos,$sid,1)";
            }
            $values_delete .= " product_id=$pid";
        }

        // This is to keep product counts and layered nav in order
        /*$is_anchor = self::getTreeAttr( $cat, "is_anchor", 0 );
        $cat_ids = array( $cat_id );
        if( $is_anchor ){
            $cat_anch_id = self::getTreeAttrMatchCat();
            $pid = $cat_id;
            while( ($pid=Icommerce_Eav::getValue($pid,"parent_id","catalog_category")) ){
                $cat_ids[] = $pid;
                if( $pid==$cat_anch_id ) {
                    break;
                }
            }
        }*/

        // And add them
        $wr = Icommerce_Db::getWrite();
        $sql_sid = "store_id IN (" . implode( ",", $sids ) . ")";
        $wr->query( "DELETE FROM catalog_category_product WHERE category_id=$cat_id AND ($values_delete)" );
        $wr->query( "INSERT INTO catalog_category_product (category_id,product_id,position) VALUES $values" );
        /*foreach( $cat_ids as $cid ){
            $values_index1 = str_replace( "CAT_ID", $cid, $values_index );
            $values_index2 = str_replace( "IS_P", $cid==$cat_id?1:0, $values_index1 );
            $wr->query( "DELETE FROM catalog_category_product_index WHERE category_id=$cid AND $sql_sid AND ($values_delete)" );
            $wr->query( "INSERT INTO catalog_category_product_index (category_id,product_id,visibility,position,store_id,is_parent) VALUES $values_index2" );
        }*/
        $wr->query( "DELETE FROM catalog_category_product_index WHERE category_id=$cat_id AND $sql_sid AND ($values_delete)" );
        $wr->query( "INSERT INTO catalog_category_product_index (category_id,product_id,visibility,position,store_id,is_parent) VALUES $values_index" );

        // Change category references from each product
        // ## Note: The column category_ids is no more in M1.4.
        if( Icommerce_Default::getMagentoVersion()<1400 ){
            foreach( $pids as $pid ){
                $cat_ids = trim( Icommerce_Eav::readValue( $pid, "category_ids", "catalog_product" ) );
                $cat_ids = $cat_ids ? explode( ",",$cat_ids ) : array();
                $k = array_search( $cat_id, $cat_ids );
                if( $k===false ){
                    $cat_ids[] = $cat_id;
                    Icommerce_Eav::writeValue( $pid, "category_ids", implode(",",$cat_ids), "catalog_product" );
                }
            }
        }

        return true;
    }

    /**
     * Remove selected products from category id in selected stores
     * @static
     * @param integer $cat Category ID
     * @param array|string $prods Product IDs to remove from the category. Can be an array or a string
     * @param array|string|int $store_ids Remove on default store 0 or give string/array with store ids. example string:2,4,5
     * @return bool OK/FAIL
     * @throws Exception
     */
		static function removeProducts( $cat, $prods, $store_ids=0 ){
        $cat_id = Icommerce_Db::toId( $cat ); // Ensure that we have cat_id. Cat can be an id or a loaded category object
        $pids = Icommerce_Db::toIdList( $prods ); // Ensure that we have an array with product ids. Prod can be an array of objects, integers or a comma separated string
        if( !$pids ) return true; // If we have no array with product ids, then return true and exit. All (0) products were removed from the category.
        $pids = array_unique( $pids );  // Removes duplicate values from an array

        // Prepare store ids, make sure we have an array of store_ids
        $sids = array();
        if( is_string($store_ids) ){
            $store_ids = explode( ",", $store_ids );
        }
        else if( !is_array($store_ids) ){
            $store_ids = array($store_ids);
        }

        foreach( $store_ids as $sid ){
            if( is_null($sid2 = Icommerce_Default::prepareStoreId( $sid )) ){
                throw new Exception( "Icommerce_Category::addProducts: no such store: $sid" );
            }
            $sids[] = $sid2;
        }
        if( !$sids ){
             $sids = array( 0 );  // Admin only
        }

        // Prepare values string
        $values_delete = "";
        $values_index = "";
        foreach( $pids as $pid ){
            if( $values_delete ){
                 $values_delete .= " OR";
            }
            $values_delete .= " product_id=$pid";
        }


        // And add them
        $wr = Icommerce_Db::getWrite();
        $sql_sid = "store_id IN (" . implode( ",", $sids ) . ")";
        $wr->query( "DELETE FROM catalog_category_product WHERE category_id=$cat_id AND ($values_delete)" );

        $wr->query( "DELETE FROM catalog_category_product_index WHERE category_id=$cat_id AND $sql_sid AND ($values_delete)" );

        // Change category references from each product
        // ## Note: The column category_ids is no more in M1.4.
        if( Icommerce_Default::getMagentoVersion()<1400 ){
            foreach( $pids as $pid ){
                $cat_ids = trim( Icommerce_Eav::readValue( $pid, "category_ids", "catalog_product" ) );
                $cat_ids = $cat_ids ? explode( ",",$cat_ids ) : array();
                $k = array_search( $cat_id, $cat_ids );
                if( $k!==false ){
                    unset( $cat_ids[$k] );
                    Icommerce_Eav::writeValue( $pid, "category_ids", implode(",",$cat_ids), "catalog_product" );
                }
            }
        }

        return true;
    }

    static $_match_cat;
    /**
     * Look for an attribute on give category, or parent one, until found
     * @static
     * @param $cat Category ID or object
     * @param $attr
     * @param null $val_ignore
     * @return mixed|null
     */
    static function getTreeAttr( $cat, $attr, $val_ignore=null ){

        // Does the attribute exist on this category?
        $cat = self::load( $cat );

        // Get category tree
        $path = Icommerce_Default::getLoadModelData( $cat, "path" );  // Get the path-value from loaded category
        if( !$path ) return null; // If path had no contents, return null and exit

        $ids = explode( "/",$path); // Create array with all parent category ids to the loaded category
        $vals = Icommerce_Eav::readEavValues( $ids, $attr, "catalog_category" );
        while( count($ids) ){ // While we have any rows left in the array...
            $id = array_pop($ids);  // Put last id in $id and remove that id from the array
            if( isset($vals[$id]) && ($v=$vals[$id]) && $v!=$val_ignore){
                self::$_match_cat = $id;
                return $v;  // Return the value
            }
        }
        return null;
    }
    /**
     * Return the category (ID) where we found the matching attribute.
     * See function getTreeAttr above
     * @static
     * @return int|null
     */
		static function getTreeAttrMatchCat( ){
        return self::$_match_cat;
    }

    /**
     * Function to delete all child categories
     * Give mother category id, and optionally a filter
     * @static
     * @param $cat_id Mother category
     * @param null $filter @todo explain the filter
     * @return bool OK/FAIL
     * @throws dispatchEvent 'catalog_controller_category_delete'
     */
		static function deleteChildren( $cat_id, $filter=null ){

				// Find the path for category cat_id
				if( $cat_id ){  // If we have a positive cat_id
            $path = Icommerce_Eav::readValue( $cat_id, "path", "catalog_category" );
            if( !$path ) return false; // If no path found, then return false and exit.
            $path .= "/";
        } else {  // We have no positive cat_id
            $path = ""; // @todo Q:can we delete categories without a cat_id
        }

				// Find the categories to delete
        if( $filter ){  // If filter is anything else than null
            $aid = Icommerce_Eav::getAttributeId("name","catalog_category");
            $ids = Icommerce_Db::getColumn(
                "SELECT cce.entity_id FROM catalog_category_entity as cce, catalog_category_entity_varchar as ccevc WHERE
                 cce.path RLIKE '^".$path."[^/]+$' AND ccevc.entity_id=cce.entity_id AND ccevc.attribute_id=$aid AND
                 ccevc.value RLIKE '$filter'" );  // @todo explain the sql
            $names = Icommerce_Db::getColumn(
                "SELECT ccevc.value FROM catalog_category_entity as cce, catalog_category_entity_varchar as ccevc WHERE
                 cce.path RLIKE '^".$path."[^/]+$' AND ccevc.entity_id=cce.entity_id AND ccevc.attribute_id=$aid AND
                 ccevc.value REGEXP '$filter'" ); // @todo explain the sql
        } else {  // If filter is null
            $ids = Icommerce_Db::getColumn(
                "SELECT entity_id FROM catalog_category_entity WHERE
                 path REGEXP '^".$path."[^/]+$' " ); // @todo explain the sql
        }

        // Delete categories found
        foreach( $ids as $id ){
            $cat = Mage::getModel("catalog/category")->load($id); // Load category
            if( $cat->getId()==$id ){ // Check that we got the right category
                Mage::dispatchEvent( 'catalog_controller_category_delete', array('category'=>$cat) ); // Warn everyone that we are going to delete.
                $cat->delete(); // Delete the category
            }
        }

        return true;
    }

    /**
     * Load category by url_path
     * Return nothing or a category
     * @static
     * @param string $url_path
     * @return Mage_Core_Model_Abstract
     */
		static function loadByUrlPath( $url_path ){
        $aid = Icommerce_Eav::getAttributeId( "url_path", "catalog_category" );
        $eid = Icommerce_Db::getValue( "SELECT entity_id FROM catalog_category_entity_varchar WHERE value='$url_path' AND attribute_id=$aid" );
        if( $eid ){
            return Mage::getModel( "catalog/category" )->load($eid);
        }
    }

		/**
		 * Check if a url_path exist among the categories.
		 * Return null = no does not exist, integer = yes exists
		 * @static
		 * @param string $url_path
		 * @return null|string
		 */
    static function urlPathExists( $url_path ){
        $aid = Icommerce_Eav::getAttributeId( "url_path", "catalog_category" );
        $eid = Icommerce_Db::getValue( "SELECT entity_id FROM catalog_category_entity_varchar WHERE value='$url_path' AND attribute_id=$aid" );
        return $eid;
    }

    /**
     * Load category object by its unique url_key
     * @static
     * @param $url_key
     * @return Mage_Core_Model_Abstract
     */
		static function loadByUrlKey( $url_key ){
        $aid = Icommerce_Eav::getAttributeId( "url_key", "catalog_category" );
        $eid = Icommerce_Db::getValue( "SELECT entity_id FROM catalog_category_entity_varchar WHERE value='$url_key' AND attribute_id=$aid" );
        if( $eid ){
            return Mage::getModel( "catalog/category" )->load($eid);
        }
    }

    /**
     * Check if a url_key exist among the categories.
     * Give string url_key, get null or the entity_id of that string.
     * Null = no the string was not found. Integer = yes the url_key was found.
     * Useful before trying to add a category with an existing url_key.
     * @static
     * @param string $url_key
     * @return null|string
     */
		static function urlKeyExists( $url_key ){
        $aid = Icommerce_Eav::getAttributeId( "url_key", "catalog_category" );
        $eid = Icommerce_Db::getValue( "SELECT entity_id FROM catalog_category_entity_varchar WHERE value='$url_key' AND attribute_id=$aid" );
        return $eid;
    }

    static function recalculateChildrenCount()
    {
        $wr = Icommerce_Db::getWrite();
        $wr->query( "CREATE TABLE catalog_category_entity_tmp LIKE catalog_category_entity;" );
        $wr->query( "INSERT INTO catalog_category_entity_tmp SELECT * FROM catalog_category_entity;" );
        $wr->query( "UPDATE catalog_category_entity cce SET children_count =
                    (
                    SELECT (count(cce2.entity_id)-1) as children_county
                    FROM catalog_category_entity_tmp cce2
                    WHERE PATH LIKE CONCAT(cce.path,'%')
                    );
                    " );
        $wr->query( "DROP TABLE catalog_category_entity_tmp;" );
    }
}
