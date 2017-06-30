<?php

class Icommerce_Attributebinder_Helper_Data extends Mage_Core_Helper_Abstract {

    protected function lookupBoundValueByBindId( $bind_id, $val, $default_val ){
        // Lookup the bound value
        if( is_array($val) ){
            $r = array();
            foreach( $val as $v ){
                $r[] = $this->lookupBoundValueByBindId( $bind_id, $v, $default_val );
            }
            return $r;
        }
        else if( strpos($val,",") ){
            $r = $this->lookupBoundValueByBindId( $bind_id, explode(",",$val), $default_val );
            return implode(",",$r);
        }
        $val_rows = Icommerce_Db::getRows( "SELECT main_attribute_value FROM icommerce_attributebinder_bindings WHERE attributebinder_id=? AND bind_attribute_value=?", array($bind_id,$val) );
        if(count($val_rows)==1) {
            $val_bound = $val_rows[0]['main_attribute_value'];
            return $val_bound ? $val_bound : $default_val;
        } else {
            $val_bound = array();
            foreach($val_rows as $key => $value) {
                $val_bound[] = $value['main_attribute_value'];
            }
            return !empty($val_bound) ? $val_bound : $default_val;
        }
    }

    function lookupBoundValueByBind( $acode, $val, $default_val=null ){
        // Lookup using attr binder
        static $st_bind_ids = array();
        if( !isset($st_bind_ids[$acode]) ){
            $st_bind_ids[$acode] = Icommerce_Db::getValue(
                "SELECT id FROM icommerce_attributebinder WHERE bind_attribute_code=? ",
                array($acode) );
        }
        if( !($bind_id=$st_bind_ids[$acode]) ){
            return $default_val;
        }

        return $this->lookupBoundValueByBindId( $bind_id, $val, $default_val );
   }

    function lookupBoundValueByMain( $acode, $val, $default_val=null ){
        // Lookup using attr binder
        static $st_bind_ids = array();
        if( !isset($st_bind_ids[$acode]) ){
            $st_bind_ids[$acode] = Icommerce_Db::getValue(
                "SELECT id FROM icommerce_attributebinder WHERE main_attribute_code=? ",
                array($acode) );
        }
        if( !($bind_id=$st_bind_ids[$acode]) ){
            return $default_val;
        }

        return $this->lookupBoundValueByBindId( $bind_id, $val, $default_val );
   }

}

