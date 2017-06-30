<?php
/**
 * Created by PhpStorm.
 * User: arne
 * Date: 2014-02-14
 * Time: 11.37
 */

class Icommerce_JsonProductInfo_Model_Source_StockInfo
{
    public function toOptionArray()
    {
        // Get columns by means of SELECT
        $columns = Icommerce_Db::getRow( "SELECT * FROM cataloginventory_stock_item LIMIT 1" );
        foreach ($columns as $col=>$val) {
            if( $col!="qty" ){
                $arr[] = array('value'=>$col, 'label'=>$col);
            }
        }
        return $arr;
    }
}
