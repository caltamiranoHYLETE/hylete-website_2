<?php
 
class Itoris_Installer_Model_Module{
	
	public function __construct($pid) {
		$this->pid = (int) $pid;
	}

	public function enable() {
		$this->setActivityTo(true);
	}

	public function disable() {
		$this->setActivityTo(false);
	}

	protected function setActivityTo($activity) {
		if ($this->pid == self::$INSTALLER_PID) {
			return;
		}
		
		$activity = $activity ? 'true' : 'false';
		$configFileName = $this->getMainConfigFilePath();
		$moduleName = $this->getModuleName($configFileName);
		$configFileName = Mage::getBaseDir('etc').'/modules/'.$configFileName;
		if (!($ex = file_exists($configFileName) && $w = is_writable($configFileName))) {
			$reasons = array();
			if (!$ex) {
				$reasons[] = 'does not exists';
			}

			if (!$w) {
				$reasons[] = 'not writable';
			}
			throw new Exception("Main config file of module pid {$this->pid} "
								.implode(' and ', $reasons)." in $configFileName");
		}

		$doc = new DOMDocument();
		if ($doc->load($configFileName) === false) {
			throw new Exception("Unable to parse config file xml $configFileName");
		}

		$xp = new DOMXPath($doc);
		$result = $xp->query("/config/modules/$moduleName/active");
		if ($result->length != 1) {
			throw new Exception("Node 'active' not found in main config file $configFileName");
		}

		$result->item(0)->nodeValue = $activity;
		if ($doc->save($configFileName) === false) {
			throw new Exception("Error on writing config to the '$configFileName' file");
		}
	}

	protected function getModuleName($mainConfigFileName) {
		return str_replace('.xml', '', $mainConfigFileName);
	}

	protected function getMainConfigFilePath() {
		$files = $this->getFiles();
		$xp = new DOMXPath($files);
		$result = $xp->query("//folder[@name='app']/folder[@name='etc']/folder[@name='modules']/file");
		if ($result->length !== 1) {
			throw new Exception("Unable to find main module config file in the files list");
		}

		$filename = $result->item(0)->attributes->getNamedItem('name')->textContent;

		if (preg_match('/^Itoris_.+.xml$/', $filename)) {
			return $filename;
		} else {
			throw new Exception("File in the app/etc/modules dir has invalid name");
		}

	}

	protected function loadFiles() {
		/** @var $db Varien_Db_Adapter_Pdo_Mysql */
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$table = Mage::getSingleton('core/resource')->getTableName('itoris_installer_products');
		$data = $db->query("select `files` from $table where `pid`=?", array($this->pid))->fetchColumn();
		if (!$data) {
			throw new Exception("Module with pid {$this->pid} does not have any files record");
		}

		$doc = new DOMDocument();
		$doc->loadXML($data);
		$this->files = $doc;
	}

	public function getFiles() {
		if ($this->files == null) {
			$this->loadFiles();
		}

		return $this->files;
	}

	public function setFiles(DOMDocument $files) {
		$this->files = $files;
	}

	protected $pid;

	/**
	 * @var DOMDocument
	 */
	protected $files;

	public static $INSTALLER_PID = 1;
}

?>