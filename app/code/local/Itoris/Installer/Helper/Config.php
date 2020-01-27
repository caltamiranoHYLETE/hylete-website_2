<?php

/**
 * The purpose of this class is to provide convenient way
 * to store and load data from magento config
 */
class Itoris_Installer_Helper_Config{

	public function __construct() {
		$this->configResource = Mage::getResourceModel('core/config');
	}

	/**
	 * Saves string to the config
	 * @param $id
	 * @param $data
	 * @return void
	 */
	public function save($id, $data) {
		$this->configResource->saveConfig($id, $data, 'default', 0);
	}

	/**
	 * Loads string from config with id = $id.
	 * Returns null if nothing found.
	 * @param $id
	 * @return null|string
	 */
	public function load($id) {
		
		/** @var $db Varien_Db_Adapter_Pdo_Mysql */
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$select = $db->select()
			->from($this->configResource->getMainTable(), '*')
			->where('path = ?', $id)
			->where('scope = ?', 'default')
			->where('scope_id = ?', 0);

		$row = $db->fetchRow($select);

		if($row) {
			return $row['value'];
		} else {
			return null;
		}
	}

	/**
	 * Remove config entry
	 * @param $id
	 * @return void
	 */
	public function delete($id) {
		$this->configResource->deleteConfig($id, 'default', 0);
	}

	/**
	 * Serializes array data to string and saves to the db
	 * @param $id
	 * @param array $data
	 * @return void
	 */
	public function saveArray($id, array $data) {
		$data = serialize($data);
		$this->save($id, $data);
	}

	/**
	 * Loads data from config and tries to unserialize it to array
	 * @param $id
	 * @return array|null
	 */
	public function loadArray($id) {
		$result = $this->load($id);
		if ($result == null) {
			return null;
		}

		return unserialize($result);
	}

	/**
	 * @var \Mage_Core_Model_Resource_Config
	 */
	protected $configResource;
}

?>