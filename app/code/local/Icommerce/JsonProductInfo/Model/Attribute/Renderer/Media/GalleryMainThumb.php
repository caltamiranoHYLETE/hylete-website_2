<?php

/**
 * Created by JetBrains PhpStorm.
 * User: arne
 * Date: 2012-08-02
 * Time: 12.54
 * To change this template use File | Settings | File Templates.
 */

class Icommerce_JsonProductInfo_Model_Attribute_Renderer_Media_GalleryMainThumb extends Icommerce_JsonProductInfo_Model_Attribute_Renderer_Abstract {

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

        // Generate a final full info gallery array with format:
        // array( 0=>array("full"=>"full1.jpg", "thumb"=>"small.jpg", "dbfile"=>"file.jpg"),
        //        1=>array("full"=>"full_side.jpg", "thumb"=>"small_side.jpg", "dbfile"=>"file_side.jpg"),
        //        ... );

        $val = $media_gallery = Icommerce_Db::getColumn( "SELECT value FROM catalog_product_entity_media_gallery as g
                                                          INNER JOIN catalog_product_entity_media_gallery_value as gv on g.value_id=gv.value_id
                                                          WHERE g.entity_id=? AND gv.disabled=0 ORDER BY position ASC", array($eid) );

        // Do rebuild the gallery with js when clicking on buttons we need resized images...
        $media_gallery_thumbs = array();
        foreach($media_gallery as $image_file){
            $media_gallery_thumbs[] = $st_image_helper->init($st_fake_prod,'thumbnail',$image_file)->keepFrame($this->getConfig('keep_frame_thumbnail'))->resize($this->getConfig('thumbnail_image_width'))->__toString();
        }

        //$vals[$eid]["media_gallery_thumbs"] = $media_gallery_thumbs;

        //... as well as large version for each media gallery image when viewing in popup
        $media_gallery_full = array();
        foreach($media_gallery as $image_file){
            $media_gallery_full[] = $st_image_helper->init($st_fake_prod,'thumbnail',$image_file)->keepFrame($this->getConfig('keep_frame_full'))->resize($this->getConfig('full_image_width'))->__toString();
        }

        //$vals[$eid]["media_gallery_full"] = $media_gallery_full;

        // Put together a "final" array to give to JS
        $final_vals = array();
        foreach( $media_gallery as $ix => $v ){
            $final_vals[] = array( "full" => $media_gallery_full[$ix], "thumb" => $media_gallery_thumbs[$ix], "dbfile" => $media_gallery[$ix] );
        }

        return $final_vals;
    }

}

