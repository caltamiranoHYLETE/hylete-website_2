<?php
class Globale_Browsing_Model_Rewrite_Store extends Mage_Core_Model_Store {

	/**
	 * Store Config $path list that should be overrated
	 * @var array
	 */
	private static $ChangedPathArray = array(
		Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON
	);


	/**
	 * Rewriting Default Configs values according to Global-e needs
	 * @param string $path
	 * @return  string|null
	 */
	public function getConfig($path)
	{
		if (Mage::registry('globale_user_supported') && !Mage::app()->getStore()->isAdmin() && in_array($path, self::$ChangedPathArray)) {

			switch ($path){
				//In case of Global-E order tax calculation must be base Origin address
				case Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON :
					return 'origin';
					break;

				default:
					$ConfigValue = parent::getConfig($path);
			}

			return $ConfigValue;
		}
		return parent::getConfig($path);
	}
}