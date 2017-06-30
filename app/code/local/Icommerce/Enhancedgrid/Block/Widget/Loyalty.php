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
class Icommerce_Enhancedgrid_Block_Widget_Loyalty extends Mage_Adminhtml_Block_Template {
	public function _toHtml() {
	    return "";
	}
	
	protected function _getLoyaltyUrl() {
	    $url = $this->_getBaseLoyaltyUrl();
	    
	    $url_data = array();
	    $url_data["a"] = "enhancedgrid";
	    $url_data["v"] = (string) Mage::getConfig()->getNode('modules/Icommerce_Enhancedgrid/version');
	    $url_data["m"] =  Mage::getVersion();
	    $url_data["p"] =  urlencode($this->getBaseUrl());
	    $url_data["ap"] =  urlencode($this->getAction()->getFullActionName());
	    //$url_data["license"] =  Mage::helper('rewards/loyalty_checker')->getLicenseKey();
	    
	    $url_data_json = json_encode($url_data);
	    
        $salt = "welovewdca12345!!";
        
        $url_data_json_hex = bin2hex($url_data_json . $salt);
	    
	    $url = $url . "?data=" . $url_data_json_hex;
	    
	    return $url;
	}
	
	protected function _getBaseLoyaltyUrl() {
	
	    return "";
	}
}