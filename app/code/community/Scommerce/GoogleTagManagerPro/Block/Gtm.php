<?php
/**
 * 
 * @category    Scommerce
 * @package     Scommerce_GoogleTagManagerPro
 * @author		Scommerce Mage (core@scommerce-mage.co.uk)
 */
class Scommerce_GoogleTagManagerPro_Block_Gtm extends Mage_Core_Block_Template
{
	
	/**
     * Retrieve current page URL
     *
     * @return string
     */
    public function getPageName()
    {
        if (!$this->hasData('page_name')) {
            $this->setPageName(Mage::getSingleton('core/url')->escape($_SERVER['REQUEST_URI']));
        }
        return $this->getData('page_name');
    }
	
}