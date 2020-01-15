<?php


/**
 * This class is responsible of check for new version of extensions
 * and write them to the magento notifications db table
 */
class Itoris_Installer_Model_Notifications {

	/**
	 * Check for new versions.
	 * Pre-dispatch event observer.
	 * @param \Varien_Event_Observer $observer
	 * @return void
	 */
	public function checkUpdates(Varien_Event_Observer $observer) {
		try {
			Itoris_Installer_Client::init();

			if (!$this->isNeedUpdate()) {
				return;
			}

			$this->prepareNotifications();
			$this->postNotifications();
			$this->setLastUpdateTime();
			IInstaller::checkRegistrations();
		} catch (Exception $e) {
			Mage::logException($e);
		}
	}

	public function isNeedUpdate() {
		return ($this->getLastUpdateTime() + $this->getFrequencyUpdate()) < time();
	}

	public function getLastUpdateTime() {
		return (int) $this->getConfig()->load(self::$CONFIG_ID_LAST_UPDATE);
	}

	public function setLastUpdateTime() {
		$this->getConfig()->save(self::$CONFIG_ID_LAST_UPDATE, time());
	}

	public function getFrequencyUpdate() {
		return (int) Mage::getConfig()->getModuleConfig(self::$MODULE_NAME)->update_frequency;
	}

	/**
	 * Returns config helper of installer
	 * 
	 * @return Itoris_Installer_Helper_Config
	 */
	public function getConfig() {
		return Mage::helper('itoris_installer/config');
	}

	public function setAvailableProductsXml($availableProductsXml) {
		$this->availableProductsXml = $availableProductsXml;
	}

	public function getAvailableProductsXml() {
		if ($this->availableProductsXml == null) {
			$this->availableProductsXml = IInstaller::getAvailableProducts();
		}

		return $this->availableProductsXml;
	}


	/**
	 * Prepares notification based on installed and available
	 * version of products. You cat get result of this method
	 * by calling getPreparedNotifications();
	 *
	 * @return void
	 */
	public function prepareNotifications() {
		$this->preparedNotifications = array();
		$available = $this->getAvailableProductVersions();
		$installed = $this->getInstalledProducts();

		$notifyAbout = array();

		foreach ($installed as $installedProduct) {
			if (version_compare(
						$installedProduct->version,
						$available[$installedProduct->pid],
						'<'
			)) {
				$notifyAbout[] = $installedProduct->pid;
			}
		}

		if (count($notifyAbout) == 0) {
			return;
		}

		foreach($notifyAbout as $pid) {
			$productData = $this->getProductData($pid);

			$url = $productData['link'].'?version='.$available[$pid];

			$msg = "New version {$productData['version']} of {$productData['name']} is available.";
			$description = "Go to <strong>System | IToris Extensions | IToris Installer</strong> to update.";
			$this->preparedNotifications[] = array(
						'severity'      => (int) self::$NOTIFICATION_SEVERITY,
						'date_added'    => gmdate('Y-m-d H:i:s', time()),
						'title'         =>  (string)$msg,
						'description'   => (string)$description,
						'url'           =>  (string)$url,
					);
		}
	}

	/**
	 * Returns array with pids as keys and versions as values of
	 * all available products
	 * 
	 * @return array
	 */
	public function getAvailableProductVersions() {
		$data = $this->getParsedAvailableProducts();
		
		$result = array();
		foreach($data['response']['products']['product'] as $productData) {
			$result[$productData['pid']] = $productData['version'];
		}

		return $result;
	}

	public function setInstalledProducts($installedProducts) {
		$this->installedProducts = $installedProducts;
	}

	public function getInstalledProducts() {
		if ($this->installedProducts == null) {
			$this->installedProducts = IInstaller::getInstalledProducts();
		}
		return $this->installedProducts;
	}

	/**
	 * Posts notifications to the notifications inbox
	 *
	 * @return void
	 */
	public function postNotifications() {
		/** @var $inbox Mage_AdminNotification_Model_Inbox */
		$inbox = Mage::getModel('adminnotification/inbox');

		if ($this->preparedNotifications !== null) {
			$inbox->parse($this->preparedNotifications);
		}
	}

	public function getPreparedNotifications() {
		return $this->preparedNotifications;
	}

	/**
	 * This method parse available products xml to the array
	 * using Itoris_Installer_Helper_Xml parser. Also caches result.
	 * 
	 * @return array
	 */
	public function getParsedAvailableProducts() {
		if ($this->availableProducts == null) {
			$parser = new Itoris_Installer_Helper_Xml();
			$parser->loadXML($this->getAvailableProductsXml());
			$data = $parser->xmlToArray();
			$this->availableProducts = $data;
		}
		return $this->availableProducts;
	}

	/**
	 * Returns array, which contains information about product with pid = $pid
	 * 
	 * @param $pid - id of the product
	 * @return bool|array
	 */
	public function getProductData($pid) {
		$data = $this->getParsedAvailableProducts();
		foreach($data['response']['products']['product'] as $productData) {
			if ($productData['pid'] == $pid) {
				return $productData;
			}
		}

		return false;
	}

	protected $availableProductsXml;
	protected $availableProducts;
	protected $installedProducts;
	protected $preparedNotifications;
	
	public static $MODULE_NAME = 'Itoris_Installer';
	public static $CONFIG_ID_LAST_UPDATE = 'itoris/installer/notifications/last_update';
	public static $NOTIFICATION_SEVERITY = 4;
}

?>
