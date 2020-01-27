<?php
/**
 * User: Vadim
 * Date: 9/9/11
 * Time: 11:52 AM
 */
 
class Itoris_Installer_Helper_Data{

	/**
	 * Adds platform information to the params array (magento version and edition)
	 *
	 * @param array $params
	 * @return void
	 */
	public function addPlatformInfo(array &$params){
		$params['magento_version'] = Mage::getVersion();
		$params['magento_edition'] = $this->detectEdition();
		$params['client_version'] = Itoris_Installer_Client::getVersionStr('installer');
	}

	private function detectEdition() {
		
		$filesList = array(
			'LICENSE_EE.txt',
			'app/code/core/Enterprise'
		);

		foreach($filesList as $file){
			if(file_exists(Mage::getBaseDir().'/'.$file)){
				return Itoris_Installer_Helper_Data::$MAGENTO_EDITION_EE;
			}
		}
		return Itoris_Installer_Helper_Data::$MAGENTO_EDITION_CE;
	}

	public static $MAGENTO_EDITION_CE = 'CE';
	public static $MAGENTO_EDITION_EE = 'EE';
}

?>