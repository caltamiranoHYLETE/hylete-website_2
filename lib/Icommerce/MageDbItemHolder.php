<?php 

    class Icommerce_MageDbItemHolder {
        protected $_item;
        protected $_n_change = 0;
        protected $_id = null;
        protected $_n_changed = 0;
        protected $_n_same = 0;
        protected $_n_new = 0;
        
        function __construct( $item ){
            $this->_item = $item;
        }
        
        function __destruct(){
        	if( $this->_item )
            	$this->flush();
        }
        
        function resetModel( ){
        	$mdl = $this->_item;
        	$this->_item = null;
        	return $mdl;
        }
        
        function flush( ){
            if( $this->_item && $this->_n_change>0 ){
                $this->_item->save();
				$this->_n_change = 0;
                $this->_n_changed++;
            }
            else $this->_n_same++;
            return $this;
        }
        
        public function getCollection(){
            return $this->_item->getCollection();
        }
        
        function load( $id ){
        	// # The load can fail, how to detect?
        	$storeid = $this->_item->getData('store_id');
            $this->_item->unsetData( );
            if ($storeid>1)
                $this->_item->setData('store_id',$storeid);
            $this->_item->load( $id );
            $this->_n_change = 0;
            return $this;
        }
        
        function loadCreate( $id=null ){
        	$this->_item->unsetData();
        	if( $id!==null )
            	$this->_item->load( $id );
            $this->_n_change = 1;
            $this->_item->setId(null);
            $this->_n_new++;
            return $this;
        }
        
        function updateValues( $db_keys, $vals ){
            foreach( $db_keys as $ktgt => $ksrc ){
                // No column mapping?
                if( !$ksrc ) $ksrc=$ktgt;
                // Does this value exist? 
                if( array_key_exists($ksrc,$vals) ){
                    $old_val = $this->_item->getData( $ktgt );
                    $new_val = $vals[$ksrc]; 
                    if( $old_val!==$new_val && (!empty($new_val) || !empty($old_val)) ){
                        $this->_item->setData( $ktgt, $new_val );
                        $this->_n_change++;
                        //echo "CHG(".$this->_item->getSku()."):".$ktgt." - ".$old_val."=>".$new_val."<br>\n";
                    }
                }
            }
            return $this;
        }

        function set( $key, $val ){
            $old_val = $this->_item->getData($key);
            if( $val!=$old_val && (!empty($val) || !empty($old_val) || $val=="0") ){ // 0 counts as empty..
                $this->_item->setData( $key, $val );
                $this->_n_change++;
                //if( $key!="updated_at" )
                //    echo "CHG2(".$this->_item->getSku()."):".$key." - ".$old_val."=>".$val."<br>\n";
            }
            return $this;
        }
        
        function __set( $key, $val ){ $this->set($key,$val); }
         
        function __get( $key ){ 
            return $this->_item->getData($key); 
        } 
        
        function get( $key ){ 
            return $this->_item->getData($key); 
        }
         
        function dirty(){
            return $this->_item && $this->_n_change>0;
        }
        
        function getItem(){
            return $this->_item;
        }

        public function setStoreId( $id ) {
            return $this->_item->setStoreId($id); // Some call a function, some just set it
        }
    }
    
