<?php

class Icommerce_Currency {

    static $rates = array();
    static function adminToStoreRate( $store_id ){
        $store_id = Icommerce_Default::getStoreId( $store_id );
        if( $store_id===null ) return null;
        if( isset(self::$rates[$store_id]) ){
            return self::$rates[$store_id];
        }

        // Translate store ID to website ID
        $ws_id = Icommerce_Db::getValue( "SELECT website_id FROM core_store WHERE store_id=$store_id" );
        $cur_from = Icommerce_Db::getValue( "SELECT value FROM core_config_data WHERE path='currency/options/base' AND scope_id=0" );
        $cur_to = Icommerce_Db::getValue( "SELECT value FROM core_config_data WHERE path='currency/options/base' AND scope_id=$ws_id" );

        if( !$cur_from || !$cur_to ) return null;

        // Look up conversion rate
        $rate = Icommerce_Db::getValue( "SELECT rate FROM directory_currency_rate WHERE currency_from=? AND currency_to=?", array($cur_from, $cur_to) );
        if( !$rate ){
            // Try other way around
            $rate = Icommerce_Db::getValue( "SELECT rate FROM directory_currency_rate WHERE currency_to=? AND currency_from=?", array($cur_from, $cur_to) );
            if( $rate ){
                $rate = 1.0 / (float)$rate;
            }
        }

        self::$rates[$store_id] = $rate;
        return $rate;
    }

}
