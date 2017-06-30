<?php

class Icommerce_JsonProductInfo_Model_Setup extends Mage_Core_Model_Resource_Setup {

    public function applyUpdates(){

        // Need to add tab code to catalog_eav_attribute ?
        if( !Icommerce_Db::columnExists("catalog_eav_attribute", "jsonproductinfo_cache") ){
            Icommerce_Db::addColumn( "catalog_eav_attribute", "jsonproductinfo_cache", "int", null, 0 );
        }

        // jsonproductinfo_suppress_lookup
        if( !Icommerce_Db::columnExists("catalog_eav_attribute", "jsonproductinfo_suppress_lookup") ){
            Icommerce_Db::addColumn( "catalog_eav_attribute", "jsonproductinfo_suppress_lookup", "int", null, 0 );
        }
    }

}
