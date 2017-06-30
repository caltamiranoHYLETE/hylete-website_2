<?php 

    // Class to update database item if members were changed
    class Icommerce_MageProductHolder extends Icommerce_MageDbItemHolder {
        
        function __construct( ){
            // Set to catalog/product model by default
            parent::__construct( Mage::getModel("catalog/product") );	
        }
        
        public function loadBySku( $sku ){
            $id = $this->_item->getIdBySku($sku);
            if( $id===null || $id===false )
            	return null;
            return $this->load($id);
        }
        
        public function loadCreateBySku( $sku ){
            $id = $this->_item->getIdBySku($sku);
            $this->loadCreate($id);
            return $this->set( "sku", $sku )->set( "created_at", now() );
        }
        
        public function skuToId( $sku ) {
            return $this->_item->getIdBySku($sku);
        }
        
        public function getWebsiteIds()
        {
            return $this->_item->getWebsiteIds();
        }
    }
    