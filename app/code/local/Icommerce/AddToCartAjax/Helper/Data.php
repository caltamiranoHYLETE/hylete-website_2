<?php

class Icommerce_AddToCartAjax_Helper_Data extends Mage_Core_Helper_Url
{
    /**
     * Retrieve compare list url
     *
     * @return string
     */
    public function getListUrl()
    {
         $itemIds = array();
         foreach (Mage::helper('catalog/product_compare')->getItemCollection() as $item) {
             $itemIds[] = $item->getId();
         }

         $params = array(
            'items'=>implode(',', $itemIds),
//            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
         );

         return $this->_getUrl('catalog/product_compare', $params);
    }

    /**
     * Retrieve remove item from compare list url
     *
     * @param   $item
     * @return  string
     */
    public function getRemoveUrl($item)
    {
        $params = array(
            'product'=>$item->getId(),
//            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
        );
        return $this->_getUrl('catalog/product_compare/remove', $params);
    }

    /**
     * Retrieve clear compare list url
     *
     * @return string
     */
    public function getClearListUrl()
    {
        $params = array(
//            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
        );
        return $this->_getUrl('catalog/product_compare/clear', $params);
    }

	/**
	  * Get redirect url
	  *
	  * @param   none
	  * @return  String
	*/
	public function getRedirectUrl($_addBaseUrl = false)
	{
		$url = Mage::getStoreConfig('addtocartajax/settings/redirect_url');

		if($_addBaseUrl){
			$url = Mage::getUrl($url);
		}

		return $url;
	}

	/**
	  * Get overlay hex color code
	  *
	  * @param   none
	  * @return  String
	*/
	public function getOverlayHexColorCode()
	{
		return Mage::getStoreConfig('addtocartajax/settings/overlay_hex_color_code');
	}

	/**
	  * Get overlay hex color code
	  *
	  * @param   none
	  * @return  String
	*/
	public function getOverlayOpacity()
	{
		return Mage::getStoreConfig('addtocartajax/settings/overlay_opacity');
	}

	/**
	  * Get popup timeout
	  *
	  * @param   none
	  * @return  String
	*/
	public function getPopupTimeout()
	{
		return Mage::getStoreConfig('addtocartajax/settings/popup_timeout');
	}

	/**
	  * Get popup fadeout duration
	  *
	  * @param   none
	  * @return  String
	*/
	public function getPopupFadeoutDuration()
	{
		return Mage::getStoreConfig('addtocartajax/settings/popup_fadeout_duration');
	}

	/**
	  * Get popup width
	  *
	  * @param   none
	  * @return  String
	*/
	public function getPopupWidth()
	{
		return Mage::getStoreConfig('addtocartajax/settings/popup_width');
	}

	/**
	  * Get setting
	  *
	  * @param   none
	  * @return  bool
	*/
	public function productCheckoutButton()
	{
		return (bool)Mage::getStoreConfig('addtocartajax/settings/product_checkout_button');
	}

	/**
	  * Get setting
	  *
	  * @param   none
	  * @return  bool
	*/
	public function showRelatedProducts()
	{
		return (bool)Mage::getStoreConfig('addtocartajax/settings/show_related_products');
	}

	/**
	  * Get setting
	  *
	  * @param   none
	  * @return  String
	*/
	public function showNumberOfRelatedProducts()
	{
		return Mage::getStoreConfig('addtocartajax/settings/number_of_products');
	}

	/**
	  * Get setting
	  *
	  * @param   none
	  * @return  String
	*/
	public function getShowPopupWhenAdding()
	{
		return Mage::getStoreConfig('addtocartajax/settings/show_popup_when_adding');
	}

    /**
     * Get setting
     *
     * @param   none
     * @return  bool
     */
    public function getShowPopupWhenDeleting()
    {
        return Mage::getStoreConfig('addtocartajax/settings/show_popup_when_deleting');
    }

	/**
	  * Get setting (be aware of that Icommerce_HeaderCart is using this
	  *
	  * @param   none
	  * @return  bool
	*/
	public function useIcommerceHeaderCartXml()
	{
		return Mage::getStoreConfig('addtocartajax/settings/use_headercart_xml');
	}

    /**
     * Get cart sidebar xml block name
     *
     * @param   none
     * @return  String
     */
    public function getCartSidebarBlockName()
    {
        if (self::useIcommerceHeaderCartXml()) {
            return 'headerCart';
        } else {
            return 'cart.sidebar.addtocartajax';
        }
    }

    /**
     * Are any of the product's custom options required?
     *gh 
     * @param $product
     * @return bool
     */
    public function hasRequiredCustomOptions($product)
    {
        $options = $product->getOptions();
        $requiredCustomOptions = false;

        if ($options) {
            foreach ($options as $option) {
                if ($option->getIsRequire()) {
                    $requiredCustomOptions = true;
                    break;
                }
            }
        }
        return $requiredCustomOptions;
    }
}
