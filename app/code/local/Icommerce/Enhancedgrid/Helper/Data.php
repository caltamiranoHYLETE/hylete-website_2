<?php
/**
 * Copyright © 2009-2011 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Icommerce
 * @package     Icommerce_Enhancedgrid
 * @copyright   Copyright © 2009-2012 Icommerce Nordic AB
 */

class Icommerce_Enhancedgrid_Helper_Data extends Mage_Core_Helper_Abstract {

    
    public function getImageUrl($image_file)
    {
        $url = false;
        $url = Mage::getBaseUrl('media').'catalog/product';
        if (substr($image_file, 0, 1) != '/') {
            $url .= '/';
        }
        $url .= $image_file;
        return $url;
    }
  
    
    public function getFileExists($image_file)
    {
        $file_exists = false;
        $file_exists = file_exists('media/catalog/product'. $image_file);
        return $file_exists;
    }
    
    
    public function getSearchCollection($queryString, $request) {
    	//@nelkaake -m 13/11/10: Added compatibility with Magento 1.4 and up
    	if(Mage::helper('enhancedgrid')->isMageVerAtLeast('1.4.0.0')) {
	        $res = Mage::helper('catalogsearch')->getQuery()
	        	->getSearchCollection()
	        	->setStoreId(Mage::app()->getStore()->getId())
	            ->addSearchFilter($queryString);
    	} else {
	        $request->setParam('q', $queryString);
	        $searchquery = Mage::helper('catalogSearch')->getQuery();
	        $searchquery->setStoreId(Mage::app()->getStore()->getId());
	        $searchquery->save();
	        $res = $searchquery->getResultCollection();
    	}
        
        return $res;
    }
    

	/**
	 * True if the Magento version currently being run is x.x.x.x or higher
	 *
	 * @usage isMageVersionAtLeast('1.4.0.0') returns true for 1.4.0.0 and >
	 *          
	 * @return boolean
	 */ 
    public function isMageVerAtLeast($version_str) {
       $version_str_sections = explode('.', $version_str);
       $mage_version_sections = explode('.', Mage::getVersion());
       foreach( $version_str_sections as $key => $value){ 
       		if(!isset($mage_version_sections[$key])) break;
       		
            if ($mage_version_sections[$key] > $value ){
              return true;
            }
            if ($mage_version_sections[$key] < $value ) {
              return false;
            }
       }
       return true;    
    }
    
    
}
?>