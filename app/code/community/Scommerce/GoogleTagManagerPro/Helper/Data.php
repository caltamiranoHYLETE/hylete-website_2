<?php
/**
 * 
 * This class is required for admin to define configuration settings.
 * @category    Scommerce
 * @package     Scommerce_GoogleTagManagerPro
 * @author		Scommerce Mage (core@scommerce-mage.co.uk)
 */
class Scommerce_GoogleTagManagerPro_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Admin configuration paths
     *
     */     
    const XML_PATH_ENABLED 					= 'scommerce_googletagmanagerpro/options/enabled';
	const XML_PATH_LICENSE_KEY				= 'scommerce_googletagmanagerpro/options/license_key';
	const XML_PATH_ACCOUNT_ID				= 'scommerce_googletagmanagerpro/options/account_id';
	const XML_PATH_EE_ENABLED				= 'scommerce_googletagmanagerpro/options/enhanced_ecommerce';
	const XML_PATH_ENHANCED_BRAND_DROPDOWN  = 'scommerce_googletagmanagerpro/options/brand_dropdown';
    const XML_PATH_ENHANCED_BRAND_TEXT      = 'scommerce_googletagmanagerpro/options/brand_text';
	const XML_PATH_BASE 					= 'scommerce_googletagmanagerpro/options/base';
	const XML_PATH_ENABLE_DYNAMIC 			= 'scommerce_googletagmanagerpro/options/enable_dynamic';
	const XML_PATH_ATTRIBUTE_KEY			= 'scommerce_googletagmanagerpro/options/attribute_key';
	const XML_PATH_SPOT         			= 'scommerce_googletagmanagerpro/options/send_phone_order_transaction';
	const XML_PATH_ENHANCED_SOURCE_TEXT     = 'scommerce_googletagmanagerpro/options/admin_source';
	const XML_PATH_ENHANCED_MEDIUM_TEXT     = 'scommerce_googletagmanagerpro/options/admin_medium';
	
	/**
     * returns whether module is enabled or not
     *
     * @return boolean
     */
    public function isEnabled($storeId = null) {
		return Mage::getStoreConfig(self::XML_PATH_ENABLED, $storeId) && $this->isLicenseValid() && strlen($this->getAccountId($storeId));
    }
    
	/**
     * returns license key administration configuration option
     *
     * @return string
     */
    public function getLicenseKey($storeId = null){
        return Mage::getStoreConfig(self::XML_PATH_LICENSE_KEY, $storeId);
    }	
	
	/**
     * returns fb pixel id
     *
     * @return string
     */
    public function getAccountId($storeId = null) {
		return Mage::getStoreConfig(self::XML_PATH_ACCOUNT_ID, $storeId);
    }
	
	/**
     * returns whether enhanced ecommerce is enabled or not
     *
     * @return boolean
     */
    public function isEEEnabled($storeId = null) {
		return Mage::getStoreConfig(self::XML_PATH_EE_ENABLED, $storeId) && $this->isEnabled();
    }
	
	/**
     * returns attribute id of brand
     * @param int $storeId Store view ID
     * @return int
     */
    public function getBrandDropdown($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENHANCED_BRAND_DROPDOWN, $storeId);
    }
	
	/**
     * returns brand static text
     * @param int $storeId Store view ID
     * @return string
     */
    public function getBrandText($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENHANCED_BRAND_TEXT, $storeId);
    }
	
	/**
     * returns brand value using product or text
     * @param $product Mage_Catalog_Product
     * @return string
     */
    public function getBrand($product)
    {		
        if ($attribute = $this->getBrandDropdown()){
            $data = $product->getAttributeText($attribute);
			if (is_array($data)) $data = end($data);
			if (strlen($data)==0){
				$data = $product->getData($attribute);
			}
            return $data;
        }
        return $this->getBrandText();
    }
	
	/**
     * returns product price
     * @param $_product Mage_Catalog_Product
     * @return float
     */
    public function getProductPrice($_product)
	{
		if ($_product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
			$_priceModel  = $_product->getPriceModel();

			//list($_minimalPriceTax, $_maximalPriceTax) = $_priceModel->getTotalPrices($_product, null, null, false);
			list($_minimalPriceInclTax, $_maximalPriceInclTax) = $_priceModel->getTotalPrices($_product, null, true, false);
			
			return $_minimalPriceInclTax;
		}
		else{
			return $_product->getFinalPrice();
		}
	}
	
	/**
     * returns category name
     * @param $product Mage_Catalog_Product
     * @return string
     */
	public function getProductCategoryName($_product)
    {
        $_cats = $_product->getCategoryIds();
        $_categoryId = array_pop($_cats);

        $_cat = Mage::getModel('catalog/category')->load($_categoryId);
        return $_cat->getName();
    }

	/**
     * returns category name
     * @param $quoteItem Mage_Quote_Item
     * @return string
     */
    public function getQuoteCategoryName($quoteItem)
    {
        if ($_catName = $quoteItem->getCategory()){
            return $_catName;
        }

        $_product = $quoteItem->getProduct();

        return $this->getProductCategoryName($_product);
    }
	
	/**
     * returns whether base order data is enabled or not
     *
     * @return boolean
     */
    public function sendBaseData($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_BASE, $storeId);
    }
	
	/**
     * returns whether transaction data should go to GA on admin order creation or not
     * @param int $storeId Store view ID
     * @return boolean
     */
    public function sendPhoneOrderTransaction($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SPOT, $storeId);
    }

	/**
     * returns source static text
     * @param int $storeId Store view ID
     * @return string
     */
    public function getSourceText($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENHANCED_SOURCE_TEXT, $storeId);
    }
	
	/**
     * returns source static text
     * @param int $storeId Store view ID
     * @return string
     */
    public function getMediumText($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ENHANCED_MEDIUM_TEXT, $storeId);
    }
	
	
	/**
     * checks to see if the extension is enabled for advanced tagging in admin
     *
     * @return boolean
     */
	
	public function getDynamicRemarketingEnabled()
	{
		return Mage::getStoreConfig( self::XML_PATH_ENABLE_DYNAMIC) && $this->isEnabled();
	}

	/**
     * returns domain cookie
     *
     * @return string
     */
	public function getCookieDomain()
	{
		$cookie = Mage::getSingleton('core/cookie');
		$domain = $cookie->getDomain();
		if (substr($domain,0,1)=="."){
			return $domain;
		}
		else{
			return '.'.$domain;
		}
	}
	
	/**
     * returns true when registration is confirmed
     *
     * @return bool
     */
    public function bRegistered() {
		if (Mage::getSingleton('core/session')->getRegistered()=='1'){
			Mage::getSingleton('core/session')->setRegistered('0');
			return true;
		}
		return false;
    }
	
	/**
     * returns true when contact us is posted
     *
     * @return string
     */
    public function bContactCompleted() {
		if (Mage::getSingleton('core/session')->getContactPost()=='1'){
			Mage::getSingleton('core/session')->setContactPost('0');
			return true;
		}
		return false;
    }
	
	/**
     * returns product attribute key
     *
     * @return string
     */
    public function getProductAtributeKey(){
        return (string)Mage::getStoreConfig(self::XML_PATH_ATTRIBUTE_KEY);
    }
	
	/**
     * returns whether license key is valid or not
     *
     * @return bool
     */
    public function isLicenseValid(){
		$sku = strtolower(str_replace('_Helper_Data','',str_replace('Scommerce_','',get_class($this))));
		return Mage::helper("scommerce_core")->isLicenseValid($this->getLicenseKey(),$sku);
	}
}