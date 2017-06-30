<?php

class Icommerce_Default_Helper_Data extends Mage_Core_Helper_Abstract {

    // Get a data member from a model object, load from database if needed.
    static function getLoadModelData( Mage_Core_Model_Abstract $obj, $attr ){
        $v = $obj->getData($attr);
        if( $v ) return $v;

        $coll = $obj->getResourceCollection()
            ->addAttributeToSelect($attr)
            ->addAttributeToFilter( "entity_id", $obj->getId() )
            ->setPage(1,1);
        foreach( $coll as $item ){
            $v = $item->getData($attr);
            $obj->setData($attr,$v);
            return $v;
        }
        return null;
    }

    // Check if a module is loaded and active or not
    static function isModuleActive( $modname )
    {
        $node = Mage::getConfig()->getNode("modules/" . $modname);
        if ($node) {
            if ($node->active=='true'){
                return true;
            }
        }
        return false;
    }

    // getStoreConfig, but try with theme/store code first:
    // Ordinary use:
    //   <myvar>47</myvar>
    // Per store use: ("gb" store code)
    //   <myvar-gb>47</myvar>
    static function getStoreConfig( $val ) {
        $store = mage::app()->getStore();
        $code = $store->getData("code");
        if( $code ){
            $v = Mage::getStoreConfig( $val."-".$code );
            if( $v )
                return $v;
        }
        return Mage::getStoreConfig( $val );
        
    }
}
