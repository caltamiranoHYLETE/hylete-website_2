<?php

/**
 * Created by JetBrains PhpStorm.
 * User: arne
 * Date: 2012-08-02
 * Time: 12.54
 * To change this template use File | Settings | File Templates.
 */

class Icommerce_JsonProductInfo_Model_Attribute_Renderer_Media_Gallery extends Icommerce_JsonProductInfo_Model_Attribute_Renderer_Abstract {

    /**
      * Returns an array of simple attribute values
      *
      * @param   $eid Entity ID
      * @param   $acode Attribute code
      * @param   $val Simple value
      * @param   $vals Array to store "side effects into"
      * @return  The actually rendered value
    */
    public function render( $eid, $acode, $val, &$vals, $prod ){
        $val = $media_gallery = Icommerce_Db::getColumn( "SELECT value FROM catalog_product_entity_media_gallery as g
                                                          INNER JOIN catalog_product_entity_media_gallery_value as gv on g.value_id=gv.value_id
                                                          WHERE g.entity_id=? AND gv.disabled=0 ORDER BY position ASC", array($eid) );
        return $val;
    }

}

