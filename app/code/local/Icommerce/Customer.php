<?php

class Icommerce_Customer {

    static public function isInEu( $cty ){
        if( strlen($cty)!==2 ){
            // Translate to 2 letter ISO
            // $cty = ...
        }
        $ctrys_eu = "AU,BE,BG,CY,CZ,EE,FI,FR,DE,GR,HU,IE,IT,LV,LT,LU,MT,NL,PL,PT,RO,SK,SI,ES,SE,GB";
        if( strpos($ctrys_eu,$cty)!==FALSE ){
            return TRUE;
        } else {
            return false;
        }
    }

    static function getCustomerGroupId( $grp_name, $exact_match=true ){
        if( !$exact_match ){
            $OP = " LIKE ";
            if( strpos($grp_name,'%')==FALSE ){
                $grp_name = "%".$grp_name."%";
            }
        } else {
            $OP = " = ";
        }
        return Icommerce_Db::getValue( "SELECT customer_group_id FROM customer_group WHERE customer_group_code $OP '$grp_name';" );
    }

}
