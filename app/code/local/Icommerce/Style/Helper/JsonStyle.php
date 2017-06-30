<?php
class Icommerce_Style_Helper_JsonStyle extends Icommerce_JsonProductInfo_Helper_Data {
	
	/**
	 * Returns an array containing all used attribute IDs, attribute options, and option IDs
	 *
	 * @return  array
	 */
	public function getAttributeIdLookupPerProduct( $include_sort = false, $productId){
		if( !$this->simple_datas[$productId."-"] ) return array();
		$this->acode_id_map = array();
		$sid = Mage::app()->getStore()->getId();
		// This could also be cached, since it is a bit of iteration
		// Cache key is keys of simple_datas
		$cache_key = "aid_lut:" . implode( "-", array_keys($this->simple_datas[$productId."-"]) )."_".$sid;
		$sval = (!$this->no_cache ? $this->cache->load( $cache_key ) : "");
		if( $sval ){
			$val = Zend_Json::decode( $sval );
			return $val;
		}
	
		if( count($this->simple_datas[$productId."-"]) ){
			// Have to loop through all loaded simple datas to extract out used attribs and option IDs
			$is_select = array();
			foreach( $this->simple_datas[$productId."-"] as $key => $val ){
				foreach( $val as $acode => $nval ){
					//foreach( $vals as $acode => $val ){
					if( !isset($is_select[$acode]) ){
						$ainfo = Icommerce_Eav::getAttributeInfo($acode);
						$is_select[$acode] = ($ainfo && ($ainfo["frontend_input"]=="select" || $ainfo["frontend_input"]=="multiselect"));
					}
					if( $is_select[$acode] ){
						foreach( explode(",",$nval) as $opt_code ){
							if( !isset($this->acode_id_map[$acode][$opt_code]) ){
								//$oid = Icommerce_Default::getOptionValueId( $acode, $opt_code );
								$optionKey = '_oid:'.$acode;
								$oid = false;
								if (isset($val[$optionKey])) { $oid = $val[$optionKey]; }
								if (!$oid) { $oid = Icommerce_Default::getOptionValueId( $acode, $opt_code ); }

								if (!(bool)$oid || $oid < 0) { unset($is_select[$acode]); }
								$this->acode_id_map[$acode][$opt_code] = $oid ? $oid : -1;
							}
						}
					}
					//}
				}
			}
		}
	
		foreach( $this->acode_id_map as $acode => $map ){
			// Make sure we also can lookup the attribute ID
			if( !isset($this->acode_id_map[$acode]["attribute_id"]) ){
				$this->acode_id_map[$acode]["attribute_id"] = Icommerce_Eav::getAttributeId( $acode );
			}
		}
		if ($include_sort) {
			foreach( $this->acode_id_map as $acode => &$map ){
				// sortByOptionPosition expetcs option ID as keys, we have to flip twce to use it
				$this->acode_id_map[$acode] = array_flip( $this->sortByOptionPosition( array_flip($map) ) );
			}
		}
	
		$sval = Zend_Json::encode( $this->acode_id_map );
		$this->cache->save( $sval, $cache_key, array("jpinfo"), $this->getConfig("lifetime") );
	
		return $this->acode_id_map;
	}
	
}