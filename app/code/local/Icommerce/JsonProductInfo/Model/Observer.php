<?php
/**
 * Created by PhpStorm.
 * User: arne
 * Date: 2014-02-04
 * Time: 14.00
 */

class Icommerce_JsonProductInfo_Model_Observer {

    public function onBindProductImage( $o ){
        $pid = $o->getData("product_id");
        if( $pid ){
            $tags = array( "CATALOG_PRODUCT_$pid" );
            $typeId = Icommerce_Db::getValue( "SELECT type_id FROM catalog_product_entity WHERE entity_id=?", array($pid) );
            if( $typeId=="simple" ){
                $cPid = Icommerce_Db::getValue( "SELECT parent_id FROM catalog_product_super_link WHERE product_id=?", array($pid) );
                if( $cPid ) $tags[] = "CATALOG_PRODUCT_$cPid";
            }
            Mage::app()->cleanCache( $tags );
        }
    }

}
