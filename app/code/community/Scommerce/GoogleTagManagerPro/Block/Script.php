<?php
/**
 * Scommerce Remarketing block for normal remarketing and dynamic remarketing
 *
 * @package 	Scommerce_GoogleTagManagerPro
 * @category 	Scommerce
 * @author	 	Scommerce Mage <core@scommerce-mage.co.uk>
 */
Class Scommerce_GoogleTagManagerPro_Block_Script extends Mage_Core_Block_Template
{
	/**
	 * Google Remarketing Allowed Page Types
	 * @see https://support.google.com/adwords/answer/3103357?hl=en
	 */
	private $_allowedPageTypes 	= array('home','searchresults','category','product','cart','purchase','other');
	
	/**
	 * Default template to use for Google Remarketing Script HTML
	 */
	private $_useTemplate		= 'scommerce/googletagmanagerpro/script.phtml';
	
	/**
	 * Default product attribute to use for 
	 */
	private $_productAttribute 	= 'sku';
	
	/**
	 * Default cart and sales attribute to use 
	 */
	private $_saleAttribute 	= 'product_id';
	
	/**
	 * Default pagetype
	 */
	private $_pagetype			= 'other';
	
	/**
	 * Cart Items
	 */
	private $_cartItems			= array();
	
	/**
	 * Remarketing class constructor
	 */
	public function _construct(){
		parent::_construct();
		$this->_productAttribute 	= Mage::helper('scommerce_googletagmanagerpro')->getProductAtributeKey();
	}
	
	/**
	 * Set the needed template
	 */
	public function _prepareLayout(){
		$this->setTemplate($this->_useTemplate);
	}
	
	/**
	 * Set current pagetype 
	 * @param string
	 */
	public function setPageType($pagetype){
		if(in_array(strtolower($pagetype),$this->_allowedPageTypes)){
			$this->_pagetype = strtolower($pagetype);
		}
	}
	
	/**
	 * get current pagetype 
	 * @param string
	 */
	public function getPageType(){
		return $this->_pagetype;
	}
	
	/**
	 * Set product attribute to use for Google Product Key
	 * @param string
	 */
	public function setProductAttributeName($attributename){
		$this->_productAttribute = strtolower($attributename);
	}
	
	/**
	 * 
	 */
	public function getJsConfigParams(){
		
		/**
		 * Default parameters
		 */
		$_params = array(
			'ecomm_pagetype' => $this->_pagetype,
			'ecomm_prodid' => '',
			'ecomm_totalvalue' => ''
		);
		
		switch($this->_pagetype){
			default:
			break;
			
			case 'category':
				$_params = array_merge($_params,$this->collectCurrentCategoryData());
			break;
				
			case 'product':
				$_params = array_merge($_params,$this->collectCurrentProductData());
			break;
				
			case 'cart':
				$_params = array_merge($_params,$this->collectCurrentCartData());
			break;
				
			case 'purchase':
				$_params = array_merge($_params,$this->collectCurrentOrderData());
			break;
		}
		
		$param = preg_replace('/"([^"]+)"s*:s*/', '$1: $2',Mage::helper('core')->jsonEncode($_params));
		$param = str_replace(',',','.chr(13),$param);
		$param = str_replace('^',',',$param);
		$param = str_replace('{','{'.chr(13),$param);
		$param = str_replace('}',chr(13).'}',$param);
		// Return parameters as an array
		return $param;	
	}
	
	/**
	 * Collect the data from current category
	 */
	private function collectCurrentCategoryData(){
		$_category = Mage::registry('current_category');
		if($_category && $_category instanceof Mage_Catalog_Model_Category){
			$_productCollection = $this->getLayout()->getBlockSingleton('catalog/product_list')->getLoadedProductCollection();
			$products = array();
			foreach ($_productCollection as $_product){
				 $products[] = $this->getProdId($_product, 'catalog');
			}
			$_params['ecomm_product_ids'] = $products;
			return $_params;
			
		}
		return false;
	}
	
	/**
	 * Collect the data from current product
	 */
	private function collectCurrentProductData(){
		$_product = Mage::registry('current_product');
		if($_product && $_product instanceof Mage_Catalog_Model_Product){
			$price = $this->formatPrice(Mage::helper('scommerce_googletagmanagerpro')->getProductPrice($_product));
			$_params = array();
			$_params['ecomm_name'] = $this->jsQuoteEscape(str_replace(',','^',$_product->getName()));
			$_params['ecomm_prodid'] = $this->getProdId($_product, 'catalog');
			$_params['ecomm_totalvalue'] = $price;
			$_params['ecomm_pvalue'] = $price;
			
			if(Mage::registry('current_category')){
				$_params['ecomm_category'] = Mage::registry('current_category')->getName();
			}
			
			return $_params;
			
		}
		return false;
	}
	
	/**
	 * Collect data from the shopping cart page
	 */
	private function collectCurrentCartData(){
		$_quotation = Mage::getSingleton('checkout/session')->getQuote();
		if($_quotation && $_quotation instanceof Mage_Sales_Model_Quote){
			$qty   		= 0;
			$id	   		= "";
			$qtys		= array();
			$products 	= array();
			
			foreach($_quotation->getAllItems() as $_product){
				$price = $_product->getBasePriceInclTax();
				if ($price>0) {
					$qty = number_format($_product->getQty(),0);
					$id = $this->getProdId($_product, 'sales');
					$qtys[] = $qty;
					$products[] = $id;
					$this->_cartItems[] = array ("id" => $id,
												"price" => $this->formatPrice($price,2),
												"price_excl_tax" => $this->formatPrice($_product->getBasePrice(),2),
												"qty" => (int)$qty);
				}
			}
			
			$_params = array();
			$_params['ecomm_prodid'] = $products;
			$_params['ecomm_totalvalue'] = $this->formatPrice($_quotation->getGrandTotal());
			$_params['ecomm_quantity'] = $qtys;	
			return $_params;
		}
		return false;
	}
	
	public function getCartItems()
	{
		$param = preg_replace('/"([^"]+)"s*:s*/', '$1: $2',Mage::helper('core')->jsonEncode($this->_cartItems));
		$param = str_replace(',',','.chr(13),$param);
		$param = str_replace('^',',',$param);
		$param = str_replace('{','{'.chr(13),$param);
		$param = str_replace('}',chr(13).'}',$param);		
		return $param;
	}
	
	/**
	 * Collect data from the current order
	 */
	private function collectCurrentOrderData(){
		$_order = Mage::getSingleton('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
		if($_order && $_order instanceof Mage_Sales_Model_Order){
			$price = 0;
			$qty   = 0;
			$id	   = "";
			$qtys = array();
			$products = array();
			$prices = array();
			
			foreach($_order->getAllItems() as $_product){
				$price = $_product->getBasePriceInclTax();
				if ($_product->getBasePrice()>0) {
					$qty 	= number_format($_product->getQtyOrdered(),0);
					$id 	= $this->getProdId($_product, 'sales');
					$price 	= $this->formatPrice($price);
					$products[] = $id;
					$qtys[] 	= $qty;
					$prices[]	= $price;
					$this->_cartItems[] = array ("id" => $id,
												"price" => $this->formatPrice($price,2),
												"price_excl_tax" => $this->formatPrice($_product->getBasePrice(),2),
												"qty" => (int)$qty);
				}
			}
			
			$_params = array();
			$_params['ecomm_prodid'] = $products;
			$_params['ecomm_totalvalue'] = $this->formatPrice($_order->getGrandTotal());			
			$_params['ecomm_quantity'] = $qtys;
			$_params['ecomm_pvalue'] =  $prices;
			$_params['hasaccount'] = $_order['customer_is_guest'] == 1 ? 'N' : 'Y';
		
			return $_params;

		}
		
		return false;
	}
	
	/**
	 * Formats a price in store currency settings
	 */
	private function formatPrice($price){
		return Mage::helper('core')->currency($price, false, false);
	}
	
	/**
     * Gets prodid attribute string from product object
     */
    private function getProdId($_product, $type){
		if ($type =="sales"){
			$product = Mage::getModel('catalog/product')->load($_product->getData($this->_saleAttribute));
			return $product->getData($this->_productAttribute);
		}
		else{
			return $_product->getData($this->_productAttribute);
		}
    }
	
}