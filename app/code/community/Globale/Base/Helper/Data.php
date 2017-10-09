<?php

/**
 * Class Globale_Base_Helper_Data
 */
class Globale_Base_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @param Mage_Catalog_Model_Product $Product
     * @return string
     */
    public static function buildProductDescription(Mage_Catalog_Model_Product $Product)
    {
        $description = $Product->getShortDescription();
        $description = htmlentities($description);
        return $description;
    }


	/**
	 * Check if Current store use Catalog Price as Fixed Price
	 * has CatalogPriceAsFixedPrice flag on + Store Currency equal to Base Store Currency
	 * @return boolean
	 */
    public function useCatalogPriceAsFixedPrice(){

		/**@var $BaseSetting Globale_Base_Model_Settings */
		$Settings = Mage::getModel('globale_base/settings');
    	$CatalogPriceAsFixedPriceFlag = $Settings->getCatalogPriceAsFixedPrice();

		$CurrentStoreBaseCurrency = Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
		$CurrentStoreCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();

		return ($CatalogPriceAsFixedPriceFlag && $CurrentStoreBaseCurrency == $CurrentStoreCurrency);
	}
}
