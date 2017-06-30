<?php

/**
 * Created by JetBrains PhpStorm.
 * User: arne
 * Date: 2012-08-02
 * Time: 12.54
 * To change this template use File | Settings | File Templates.
 */

class Icommerce_JsonProductInfo_Model_Attribute_Renderer_Media_ImageAllSizes extends Icommerce_JsonProductInfo_Model_Attribute_Renderer_Abstract {

    protected function getConfig( $param ){
        return Icommerce_JsonProductInfo_Helper_Data::getConfig( $param );
    }

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
        static $st_fake_prod;
        static $st_image_helper;
        if( !$st_fake_prod ){
            $st_fake_prod = Mage::getModel( "catalog/product" );
            $st_image_helper = Mage::helper( 'catalog/image' );
        }

        // Set these as "side effects"
        $vals[$eid][$acode . '_thumb'] = $st_image_helper->init($st_fake_prod,'image',$val)->keepFrame($this->getConfig('keep_frame_thumbnail'))->resize($this->getConfig('thumbnail_image_width'))->__toString();
        $vals[$eid][$acode . '_main'] = $st_image_helper->init($st_fake_prod,'image',$val)->keepFrame($this->getConfig('keep_frame_main'))->resize($this->getConfig('main_image_width'))->__toString();
        $vals[$eid][$acode . '_full'] = $st_image_helper->init($st_fake_prod,'image',$val)->keepFrame($this->getConfig('keep_frame_full'))->resize($this->getConfig('full_image_width'))->__toString();

        // The return the raw value (base image name)
        return $val;
    }

}
