<?php

/**
 * Created by JetBrains PhpStorm.
 * User: arne
 * Date: 2012-08-02
 * Time: 12.54
 * To change this template use File | Settings | File Templates.
 */

class Icommerce_JsonProductInfo_Model_Attribute_Renderer_PriceHtml extends Icommerce_JsonProductInfo_Model_Attribute_Renderer_Abstract {

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
        static $st_product_block;
        if( !$st_product_block ) $st_product_block = Mage::getSingleton('core/layout')->createBlock( 'catalog/product' );
        // Magento has (had?) issues with reloading models, so we kep creating a new one in each call
        $simple_prod = Mage::getModel('catalog/product')->load($eid);
        $price_html = $st_product_block->getPriceHtml($simple_prod);
        //$vals[$eid]["price_html"] = $price_html;
        return $price_html;
    }

}
