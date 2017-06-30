<?php

/**
 * Created by JetBrains PhpStorm.
 * User: peter lembke
 * Date: 2012-09-27
 * Time: 14.30
 * To change this template use File | Settings | File Templates.
 */

class Icommerce_JsonProductInfo_Model_Attribute_Renderer_PlainPrice extends Icommerce_JsonProductInfo_Model_Attribute_Renderer_Abstract {

    /**
      * Returns the products final price.
      * Look at a configurable product and you will see super attributes.
      * With the super attributes you can modify the configurable product price.
      * Example: blue +5 kr, extra small -7 kr
      *
      * @param   $eid Entity ID
      * @param   $acode Attribute code
      * @param   $val The configurable product
      * @param   $vals Array to store "side effects into"
      * @param   $prod The product
      * @return  The actually rendered value
    */
    public function render( $eid, $acode, $val, &$vals, $_product ) {

        // load event configuration areas (One of these things, without it getFinalPrice will not apply the price rules)
        // http://stackoverflow.com/questions/8829391/cant-retrieve-discounted-product-price-in-custom-script
        Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_FRONTEND, Mage_Core_Model_App_Area::PART_EVENTS);

        static $config;
        if ( !$config ) {
            $config=Mage::getStoreConfig("configurablecommon/settings/configs_simple_price");
        }

        if ($config==="1") {
            // Just use the simple price instead of the configurable price + offsets
            $item = Mage::getModel('catalog/product')->load($eid); // Load the simple product
            $price=$item->getFinalPrice();  // Simple price without price rules.
            return $price; // Just return the simple price
        }

        $price=$_product->getFinalPrice();
        return $price;
    }
}
