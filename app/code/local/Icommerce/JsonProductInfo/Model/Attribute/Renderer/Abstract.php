<?php

/**
 * Created by JetBrains PhpStorm.
 * User: arne
 * Date: 2012-08-02
 * Time: 12.54
 * To change this template use File | Settings | File Templates.
 */

class Icommerce_JsonProductInfo_Model_Attribute_Renderer_Abstract {

    // This is the helper object
    /**
     * @var Icommerce_JsonProductInfo_Helper_Data
     */
    public $_helper;


    /**
      * Returns an array of simple attribute values
      *
      * @param   $eid Entity ID
      * @param   $acode Attribute code
      * @param   $val Simple value
      * @param   $vals Array to store "side effects into"
      * @param   $product The centermost product
      * @return  The actually rendered value
    */
    public function render( $eid, $acode, $val, &$vals, $product ){
        return null;
    }

}
