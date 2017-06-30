<?php

class Icommerce_CustOpts {

    static $_prod_options = array();

    static function lookupOption( $product, $admin_label, $exact_match=true ){

        // Cached?
        if( array_key_exists($product->getId(),self::$_prod_options) &&
            array_key_exists($admin_label,self::$_prod_options[$product->getId()]) ){
            return self::$_prod_options[$product->getId()][$admin_label];
        }

        $options = $product->getOptions();
        if( !$options ){
            $options_coll = $product->getProductOptionsCollection();
            if( $options_coll ){
                $options = $options_coll->getItems();
            }
        }
        if( empty($options) ) return null;

        foreach( $options as $opt ){
            if( ($exact_match && $opt->getData("default_title")==$admin_label) ||
                (!$exact_match && strstr($opt->getData("default_title"),$admin_label)!==FALSE) ){
                if( $exact_match ){
                    self::$_prod_options[$product->getId()][$admin_label] = $opt;
                }
                return $opt;
            }
        }

        return null;
    }

    static function getOptionId( $product, $admin_label ){
        $option = self::lookupOption($product,$admin_label);
        return $option ? $option->getData("option_id") : null;
    }

    static function getOptionValue( $product, $admin_label, $option_by_sku=false ){
        $cust_opts = $product->getCustomOptions();
        if( !$cust_opts ) return null;
        if( is_string($admin_label) ){
            $option = self::lookupOption($product,$admin_label);
        } else {
            // it is (should be) already an option
            $option = $admin_label;
        }
        if( !$option ) return null;

        $id = $option->getData("option_id");
        if( array_key_exists("option_".$id,$cust_opts) ){
            $val = $cust_opts["option_".$id]->getData("value");
            if( !$option_by_sku || !($values=$option->getValues()) ){
                return $val;
            }
            // Lookup admin title of option
            if( array_key_exists($val,$values) ){
                return $values[$val]->getData("sku");
            }
            Mage::throwException("Icommerce_CustOpts: No option value with ID".$val );
        }
        return null;
    }

    // Read option_id from custom option database tables
    static function lookupOptionId( $prod, $admin_label, $exact_match=true ){
        if( $prod instanceof Mage_Catalog_Model_Product ){
            $pid = $prod->getId();
        } else {
            $pid = $prod;
        }

        // Get all matching option ID:s for product
        $rd = Icommerce_Db::getDbRead();
        $r = $rd->query( "SELECT option_id FROM catalog_product_option WHERE product_id=$pid" );
        $opt_ids = array();
        foreach( $r as $rr ){
            $opt_ids[] = $rr['option_id'];
        }
        $opt_ids = implode( ",", $opt_ids );
        if( !$opt_ids ){
            return null;
        }

        // Get the one option ID that matches the title
        $op = $exact_match ? "=" : "LIKE";
        return Icommerce_Db::getDbSingleton( "SELECT option_id FROM catalog_product_option_title WHERE store_id=0 AND option_id IN ($opt_ids) AND title $op '$admin_label'" );
    }

    // Read option_value_id from custom option database tables
    static function lookupOptionValueIdByOptionId( $opt_id, $sku ){
        if( !$opt_id ){
            return null;
        }

        // Get the one option value ID that matches the sku
        return Icommerce_Db::getDbSingleton( "SELECT option_type_id FROM catalog_product_option_type_value WHERE option_id=$opt_id AND sku='$sku'" );
    }

    // Read option_value_id from custom option database tables
    static function lookupOptionValueId( $prod, $admin_label, $sku, $exact_match=true ){
        if( $prod instanceof Mage_Catalog_Model_Product ){
            $pid = $prod->getId();
        } else {
            $pid = $prod;
        }

        $opt_id = self::lookupOptionId( $prod, $admin_label, $exact_match );
        return self::lookupOptionValueIdByOptionId( $opt_id, $sku, $exact_match );
    }

    // Read option_value_id from custom option database tables
    static function lookupOptionPriceByOptionId( $opt_id, $sku ){
        $opt_type_id = self::lookupOptionValueIdByOptionId( $opt_id, $sku );
        if( !$opt_id ){
            return null;
        }

        $store_id = Mage::app()->getStore()->getData("store_id");

        // Get the one option value ID that matches the sku
        $price = Icommerce_Db::getDbSingleton( "SELECT price FROM catalog_product_option_type_price WHERE option_type_id=$opt_type_id AND store_id='$store_id'" );
        if( is_null($price) ){
            // Try admin store also (single store mode)
            $price = Icommerce_Db::getDbSingleton( "SELECT price FROM catalog_product_option_type_price WHERE option_type_id=$opt_type_id AND store_id=0" );
        }
        return $price;
    }

    // Read option_value_id from custom option database tables
    static function lookupOptionPrice( $prod, $admin_label, $sku, $exact_match=true ){
        $opt_id = self::lookupOptionId( $prod, $admin_label, $exact_match );
        return self::lookupOptionPriceByOptionId( $opt_id, $sku );
    }

    static function deleteOption( $prod, $admin_label, $exact_match=true ){
        $opt_id = self::lookupOptionId( $prod, $admin_label, $exact_match );
        if( !$opt_id ) return null;
        $options[] = array(
                'option_id' => $opt_id,
                'is_delete' => '1',
                'is_require' => '0' );

        try {
            $prod->setCanSaveCustomOptions(true);
            $prod->setProductOptions($options);
            $prod->save();
            return true;
        } catch( Exception $e ){
            return false;
        }
    }

}
