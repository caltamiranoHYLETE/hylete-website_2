<?php
class Vaimo_Gotoadmin_Block_Gotoadmin extends Mage_Core_Block_Template
{   
    private $initUrl = 'gotoadmin/index/';
    
    
    protected function _toHtml()
    {
        if(Mage::helper('gotoadmin')->isAllowed()) {
            return parent::_toHtml();
        }
        return '';
    }
    
	public function __construct() 
	{
   		$this->setTemplate('vaimo/gotoadmin/gotoadmin.phtml');
	}
	
	
	public function getCmsBlocks() {
    	return array_unique(Mage::getSingleton('gotoadmin/observer')->getCmsBlocks());
    }
    
    
    public function getCmsBlockUrl($identifier) {
        return $this->getUrl($this->initUrl .'editcmsblock', array('identifier' => $identifier));
    }
	

    public function getProductUrl($productId)
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $productId));
    }
	
	
	public function getDashboardUrl()
	{
    	return Mage::helper('adminhtml')->getUrl('adminhtml/dashboard');
	}
	
	
	public function getProductListUrl()
	{
    	return $this->getUrl($this->initUrl . 'productlist');
	}
	
	
	public function getSystemConfigurationUrl()
	{
    	return $this->getUrl($this->initUrl .'systemconfiguration');
	}
    
    
    public function getCategoryUrl($categoryId)
    {
        return $this->getUrl($this->initUrl .'editcategory', array('id' => $categoryId));
    }
    
    
    public function getCmsPageUrl($cmsPageId)
    {
        return $this->getUrl($this->initUrl .'editcms', array('page_id' => $cmsPageId));        
    }
    
    
    public function getCurrentUrl()
    {
        $result = array();
        
        if ($currentProduct = Mage::registry('current_product')) {
            $result[0] = 'Current product';
            $result[1] = $this->getProductUrl($currentProduct->getId());
            
        } elseif ($currentCategory = Mage::registry('current_category')) {
            $result[0] = 'Current category';
            $result[1] = $this->getCategoryUrl($currentCategory->getId());
            
        } elseif ($currentCmsPage = Mage::getSingleton('cms/page')->getId()) {
            $isVaimoCmsInstalled = Mage::helper('core')->isModuleOutputEnabled('Vaimo_Cms');
            if ($isVaimoCmsInstalled) {
                if (Mage::getBlockSingleton('page/html_header')->getIsHomePage()) {
                    $currentRootId = Mage::app()->getStore(Mage::app()->getStore()->getCode())->getRootCategoryId();
                    
                    $result[0] = 'Startpage category';
                    $result[1] = $this->getCategoryUrl($currentRootId);
                    
                    return $result;
                }
            }
            
            $result[0] = 'Current CMS Page';
            $result[1] = $this->getCmsPageUrl($currentCmsPage);
        }
        
        return $result;
    }
}