<?php

	class IInstaller{
		
		public static function init(){
			self::$PATH_BASE = dirname(__FILE__).DS;
			self::$HOST = self::getHost();

			/** @noinspection PhpIncludeInspection */
			require_once self::$PATH_BASE.'IDBAdapter.php';

			if(isset($_SERVER['ITORIS_API_URL'])){
				self::$API_URL = $_SERVER['ITORIS_API_URL'];
			}

			self::$PRODUCTS_TABLE_NAME = Mage::getSingleton('core/resource')
					->getTableName(self::$PRODUCTS_TABLE_NAME);
			self::$LICENSES_TABLE_NAME = Mage::getSingleton('core/resource')
					->getTableName(self::$LICENSES_TABLE_NAME);
		}
	
		/**
		 * @throws rethrows Exception on IToris connection error.
		 */
		public static function checkConfiguration(){
			$db = new IDBAdapter();
			
			if (count($db->fetchAll("SHOW TABLES LIKE '%".self::$PRODUCTS_TABLE_NAME."'"))) return;
			
			$db->setQuery('
				CREATE TABLE IF NOT EXISTS `'.self::$PRODUCTS_TABLE_NAME.'` (
				`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`pid` INT NOT NULL ,
				`status` varchar(30) NOT NULL,
				`version` varchar(30) NOT NULL,
				`files` TEXT NOT NULL,
				`upgrade_files` TEXT NOT NULL,
				`alias` varchar(30) NOT NULL
				) ENGINE = InnoDB;
			');
			$db->query();
			
			$db->setQuery('
				CREATE TABLE IF NOT EXISTS `'.self::$LICENSES_TABLE_NAME.'` (
				`pid` INT NOT NULL ,
				`host` VARCHAR( 255 ) NOT NULL ,
				`secret` VARCHAR( 255 ) NOT NULL ,
				PRIMARY KEY ( `pid` , `host` )
				) ENGINE = InnoDB;
			');
			$db->query();

			self::updateDbStructure();
			self::updateDbStructure2();
			
			$installerPid = $db->fetchOne('SELECT `pid` FROM `'.self::$PRODUCTS_TABLE_NAME.'` WHERE `alias` = :alias',
					array(':alias' => 'installer'));
			
			if($installerPid === false){
				$files = '<?xml version="1.0" encoding="UTF-8"?><response><folder name="root"><folder name="app"><folder name="code"><folder name="local"><folder name="Itoris"><folder name="Installer"><folder name="code"><file md5="2995357d52f124224797697105d1dcab" size="1809" name="IDBAdapter.php"/><file md5="b73147b36d27579d130f0aeb3da42f72" size="29601" name="IInstaller.php"/></folder><folder name="controllers"><file md5="a7c137f06b01a5f68a73fab42286ab09" size="897" name="CallController.php"/><file md5="e3c7370d146a03c318f36492a7eaafeb" size="1148" name="IndexController.php"/></folder><folder name="etc"><file md5="b26cea1f564f4aa86e6e0e13a9ef6a30" size="880" name="config.xml"/><file md5="d349a7e022e7384eb1c9fdd999663cec" size="1765" name="adminhtml.xml"/></folder></folder></folder></folder></folder><folder name="design"><folder name="adminhtml"><folder name="default"><folder name="default"><folder name="layout"><file md5="e896fee4d6a08e07b779071d3a40e301" size="1071" name="itoris_installer.xml"/></folder></folder></folder></folder></folder><folder name="etc"><folder name="modules"><file md5="f1f79dc667d94485eb4a972c9a4a2c2b" size="186" name="Itoris_Installer.xml"/></folder></folder></folder><folder name="js"><folder name="itoris_installer"><folder name="js"><file md5="47bd2ef83905d37c23c977f3c2492d3d" size="7940" name="core.js"/><file md5="8f89758cd25733dc063c9f406aa10c1d" size="4367" name="dialog.js"/><file md5="da9becb493a5d4c38e17a10696bdd74b" size="25933" name="installation.js"/><file md5="b6a4bdfefb9eb5222baf4ca5752cc02c" size="13023" name="product.js"/><file md5="0f68325cb6cf6305190b65fd5007eaef" size="841" name="progressbar.js"/><file md5="8038ae650a62d4ae7b1a412b7d3596c7" size="3510" name="uibuilder.js"/><file md5="957f5ad7ab1ee40a9fc5b64cc3aee5b6" size="11757" name="update.js"/></folder></folder></folder><folder name="skin"><folder name="adminhtml"><folder name="default"><folder name="default"><folder name="itoris_installer"><folder name="css"><file md5="b07829776fdbe9efd3e696aa56f16971" size="1469" name="dialog.css"/><file md5="a94535513d9b444c5616e5e09ece951e" size="166" name="main.css"/><file md5="c6ad7b335921e4f94e5fbc480632d4b4" size="1187" name="product.css"/><file md5="8b50c6750873facf51ce2ca344ed0eb2" size="214" name="progressbar.css"/></folder><folder name="img"><file md5="37c51a4d48a92da9648dcd3ca011039f" size="148" name="btn_bg.gif"/><file md5="c3d9005304a677ce4900b1e064bee0de" size="922" name="cover.png"/><file md5="b9db45323c814173b941d4fbfd80de49" size="1324" name="fail.png"/><file md5="11196d8aa4d2c31d0d93f1f64a5f64c5" size="1469" name="ok.png"/><file md5="351a5febe8f16efec0b9f5e98f8edc9b" size="2053" name="process.gif"/><file md5="de1097cc74667ed2d261bf485d3b9a97" size="461" name="uninstall.png"/><file md5="ff6c05ce0901da83275491c5ef296084" size="439" name="update.png"/><file md5="f392cbb4784cd270b20a19f929b36a9e" size="460" name="upgrade.png"/><file md5="49a3e89f60bdf2832d723c9d4b88a861" size="1281" name="wait.png"/></folder></folder></folder></folder></folder></folder></folder></response>';
				
				$bind = array(
					'pid' 		=> self::$INSTALLER_PID,
					'status' 	=> 'installed', 
					'alias' 	=> 'installer',
					'version'	=> self::getInstallerVersionInConfig(),
					'files'		=> $files  
				);
				$db->insert(self::$PRODUCTS_TABLE_NAME, $bind);

				$lic = self::getAnyModuleLicense(self::$INSTALLER_PID);
				$params = array(
					'action'	=>	self::$API_ACTION_GET_LIST_OF_FILES,
					'pid'		=>	self::$INSTALLER_PID,
					'host'		=>	$lic['host'],
					'secret'	=>	$lic['secret']
				);
				self::apiRequest($params);

				$lic = self::getAnyModuleLicense(self::$INSTALLER_PID);
				$params = array(
					'action'	=>	self::$API_ACTION_SEND_STATUS,
					'host'		=>	$lic['host'],
					'pid'		=>	self::$INSTALLER_PID,
					'secret'	=>	$lic['secret'],
					'status'	=>	self::$API_STATUS_INSTALLED
				);
				self::apiRequest($params);
			}
		}

		/**
		 * Updates Installer DB Structure to handle multiple licenses
		 * @since 1.2.0
		 */
		private static function updateDbStructure(){
			$db = new IDBAdapter();

			$db->setQuery("show columns from `".self::$PRODUCTS_TABLE_NAME."` like '%secret%'");
			$result = $db->loadResult();

			if($result == false){
				return;
			}

			$data = $db->fetchAll('SELECT `pid`, `secret` FROM `'.self::$PRODUCTS_TABLE_NAME.'`'
					.' WHERE CHAR_LENGTH(`secret`) > 0');
			$rows = array();

			foreach($data as $row){
				$row = array(
					'pid' => $row['pid'],
					'host' => self::$HOST,
					'secret' => $row['secret'],
				);
				$rows[] = $row;
			}
			if(count($rows) > 0){
				$db->insertArray(self::$LICENSES_TABLE_NAME, array('pid', 'host', 'secret'), $rows);
			}
			$db->query('ALTER TABLE `'.self::$PRODUCTS_TABLE_NAME.'` DROP `secret`');
		}

		/**
		 * Moves data from tables without prefixes to the tables with prefixes and drops
		 * the tables without prefixes
		 * @since 2.0.3
		 * @static
		 * @return void
		 */
		private static function updateDbStructure2() {
			$prefix = (string) Mage::getConfig()->getTablePrefix();
			if ($prefix == '') {
				return;
			}
			
			$db = new IDBAdapter();

			$tables = $db->listTables();
			if (in_array('itoris_installer_products', $tables)) {
				$data = $db->query('select * from itoris_installer_products where pid <> 1', true);
				$data = $data->fetchAll();

				$db->insertMultiple(self::$PRODUCTS_TABLE_NAME, $data);
				$db->dropTable('itoris_installer_products');
			}

			if (in_array('itoris_installer_licenses', $tables)) {
				$data = $db->query('select * from itoris_installer_licenses', true);
				$data = $data->fetchAll();

				$db->insertMultiple(self::$LICENSES_TABLE_NAME, $data);
				$db->dropTable('itoris_installer_licenses');
			}
		}

		public static function getInstallerVersionInConfig() {
			return (String) Mage::getConfig()->getModuleConfig('Itoris_Installer')->version;
		}

		public static function checkDatabaseIntegrity(){
			$products = self::getAvailableProducts();
			$doc = new DOMDocument();
			$doc->loadXML($products);

			$xp = new DOMXPath($doc);
			$result = $xp->query('//pid');
			$validPids = array();
			/** @var $pid DOMNode */
			foreach($result as $pid){
				$validPids[] = (int) $pid->nodeValue;
			}

			/** @var $db Varien_Db_Adapter_Pdo_Mysql */
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');

			try{
				$validPids = implode(', ', $validPids);

				$db->query("delete from `".self::$PRODUCTS_TABLE_NAME."` where pid not in ($validPids)");
				$db->query("delete from `".self::$LICENSES_TABLE_NAME."` where pid not in ($validPids)");
			} catch (Exception $e) {
				Mage::logException($e);
			}
		}
		
		/**
		 * @return array('ping' => {bool}), where {bool} = 'true' or 'false'
		 */
		public static function testItorisApiConnection(){
			$buffer = false;
			$result = 'true';
			try{
				$buffer = self::getAvailableProducts();
			}catch(Exception $e){
				$result = 'false';
			}
			
			if($result == 'true'){
				$doc = new DOMDocument();
				if(!$doc->loadXML($buffer, LIBXML_NOERROR | LIBXML_NOWARNING)){
					$result = 'false';
				}
			}
			
			return array('ping' => $result);
		}
		
		/**
		 * @return stdClass installed products data
		 */
		public static function getInstalledProducts(){
			$db = new IDBAdapter();
			
			$data = $db->fetchAll('select e.`id`, e.`pid`, e.`version` from `'.self::$PRODUCTS_TABLE_NAME.'` as e '
					.'where e.`status`=:status', array(':status' => 'installed'), Zend_Db::FETCH_OBJ);
			$hosts = $db->fetchAll('select e.`pid`, e.`host` from `'.self::$LICENSES_TABLE_NAME.'` as e');
			
			foreach($data as $key => $pr){
				$data[$key]->hosts = array();
			}
			
			foreach($data as $key => $pr){
				foreach($hosts as $h){
					/** @noinspection PhpUndefinedFieldInspection */
					if($pr->pid == $h['pid']){
						$data[$key]->hosts[] = $h['host'];
					}
				}
			}
			return $data;
		}
		
		/**
		 * @static
		 * @throws Exception
		 * @param null $alias
		 * @param array $hosts
		 * @return void
		 */
		public static function checkRegistrations($alias = null, array $hosts = array()){
			$db = new IDBAdapter();
			
			$fakeSecrets = array(
				md5(self::$HOST.self::$TRIAL_POSTFIX_TRIAL),
				md5(self::$HOST.self::$TRIAL_POSTFIX_NOT_TRIAL),
				md5(self::$HOST.self::$TRIAL_POSTFIX_ERROR_CONNECTION),
			);
			
			$sql = 'select e.`pid`, p.`alias`, e.`host`, e.`secret` from `'.self::$LICENSES_TABLE_NAME.'` as e';
			$sql .= ' inner join `'.self::$PRODUCTS_TABLE_NAME.'` as p on e.pid = p.pid ';
			$sql .= ' where p.`status` = :status and e.`secret` not in ('.$db->Quote($fakeSecrets).')';

			if(count($hosts) > 0){
				$sql .= ' and e.`host` in ('.$db->Quote($hosts).')';
			}

			if($alias !== null){
				$sql .= ' and p.`alias`='.$db->Quote($alias);
			}

			$data = $db->fetchAll($sql, array(
				':status' => 'installed'
			));
			foreach($data as $prod){
				try{
					$alias = $prod['alias'];
					$pid = (int)self::getPidByAlias($alias);
					$host = $prod['host'];
					$secret = $prod['secret'];
					
					$params = array(
						'action'	=> self::$API_ACTION_CHECK_REGISTRATION,
						'host'		=> $host,
						'pid'		=> $pid,
						'secret'	=> $secret
					);
					
					try{
						$response = self::apiRequest($params);
					}catch(Exception $e){
						Mage::logException($e);
						continue;
					}
		
					$doc = new DOMDocument();
					if(!$doc->loadXML($response, LIBXML_NOERROR | LIBXML_NOWARNING)){
						continue;
					}
					$xp = new DOMXPath($doc);
					$errorNodes = $xp->query('/response/error/code');
					if($errorNodes->length > 0){
						$errorMsg = intval($errorNodes->item(0)->textContent);
						
						if ($errorMsg == 0) {
							continue;
						} else if($errorMsg == 5 || $errorMsg == 6){
						//5  - ERROR_REGISTER 6 - ERROR_NOT_REGISTERED
							self::dropLicense($alias, $host);
							continue;
						}
					}else{
						throw new Exception('No registration information in the response.');
					}
				}catch(Exception $e){
					Mage::logException($e);
				}
			}
		}
		
		private static function getApiUrl(){
			return self::$API_URL;
		}

		/**
		 * @param array $params
		 * @throws Exception when connection error
		 * @return string|bool
		 */
		public static function apiRequest(array $params){

			self::getDataHelper()->addPlatformInfo($params);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 				self::getApiUrl()	);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,	1					);
			curl_setopt($ch, CURLOPT_TIMEOUT, 			250					);
			curl_setopt($ch, CURLOPT_POST, 			true				);
			curl_setopt($ch, CURLOPT_POSTFIELDS, 		$params				);
			
			$attempt = 1;
			$errorCode = CURLE_OK;
			$buffer = false;
			while($attempt++ <= self::$CONNECT_ATTEMPTS){
				$buffer = curl_exec($ch);
				$errorCode = curl_errno($ch);
				if($errorCode == CURLE_OK){
					break;
				}
			}
			
			if($errorCode != CURLE_OK){
				$error = curl_error($ch);
				curl_close($ch);
				Mage::logException(new Exception($error, $errorCode));
				throw new Exception('Unable to connect to the IToris update server.');
			}
			
			curl_close($ch);
			return $buffer;
		}
		
		public static function getAvailableProducts(){
			if(!isset(self::$cache['available_products'])){
				self::$cache['available_products'] =
						self::apiRequest(array( 'action' => self::$API_ACTION_GET_PRODUCTS));
			}

			return self::$cache['available_products'];
		}

		public static function get_products(){
			echo self::getAvailableProducts();
		}
		
		public static function registerProduct($pid, $serial){
			$post = array();
			$post['pid'] = $pid;
			$post['serial'] = $serial;
			$post['host'] = self::$HOST;
			$response =  self::_register($post);
			
			$dom = new DOMDocument();
			$dom->loadXML($response);
			$xp = new DOMXPath($dom);
			$error = (int) $xp->query('/response/error/code')->item(0)->nodeValue;
			return $error;
		}
		
		public static function register($post){

			if(isset($post['host'])){
				/** @var $collection Mage_Core_Model_Mysql4_Store_Collection */
				$collection = Mage::getModel('core/store')->getCollection();
				$collection->setLoadDefault(true);
				$collection = self::convertStoresCollectionToTheInternal($collection);
				$hosts = self::getHostsList($collection);
				if(!in_array($post['host'], $hosts)){
					echo '<?xml version="1.0" encoding="UTF-8"?><response><error><code>5</code><msg>There is no store with such a host configured on your Magento installation.</msg></error></response>';
					return;
				}
			}
			echo self::_register($post);
		}

		public static function getHostsList(Itoris_Installer_Store_Collection $collection) {
			$result = array();
			/** @var $store Mage_Core_Model_Store */
			foreach ($collection as $store) {
				for ($i = 0; $i < 2; $i++) {
					$url = self::getHost($store->getBaseUrl('web', (bool) $i));

					if (!in_array($url, $result)) {
						$result[] = $url;
					}
				}
			}

			return $result;
		}

		public static function convertStoresCollectionToTheInternal($collection){
			$result = new Itoris_Installer_Store_Collection();
			foreach($collection as $store){
				$result->addItem($store);
			}

			return $result;
		}
		
		private static function _register($post){
			$pid 	= intval($post['pid']);
			$serial = trim($post['serial']);
			$host 	= isset($post['host']) ? trim($post['host']) : self::$HOST;
			$isSetRegisteredStatus = !isset($post['host']);
			
			$params = array(
				'action'	=> self::$API_ACTION_REGISTER,
				'host' 		=> $host,
				'pid' 		=> $pid,
				'isecret' 	=> md5('itoris'.$serial)
			);
			//TODO handle exception
			$response =  self::apiRequest($params);

			$dom = new DOMDocument();
			//TODO handle invalid xml
			$dom->loadXML($response);
			$xp = new DOMXPath($dom);
			$error = (int) $xp->query('/response/error/code')->item(0)->nodeValue;
			if($error === 0){
				$db = new IDBAdapter();
				
				$secret = md5($host.$serial);
				
				$data = array(
					'pid'		=> $pid,
					'host'		=> $host,
					'secret'	=> $secret 
				);
				
				$onDuplicate = array(
					'secret' => new Zend_Db_Expr($db->Quote($secret))
				);				
				
				$db->insertOnDuplicate(self::$LICENSES_TABLE_NAME, $data, $onDuplicate);
				
				if($isSetRegisteredStatus){
					$db->update(self::$PRODUCTS_TABLE_NAME, array('status'=> 'registered'), '`pid`='.$pid);
				}
			}
			
			return $response; 
		}
		
		public static function check_configuration($post){

			if (defined('COMPILER_INCLUDE_PATH')) {
				echo "Compiler is enabled. Installation can not continue. "
					 ."Please disable compiler and try again (System - Tools - Compilation). "
					 ."Run compilation process after installation.";
				return;
			}

			$pid = intval($post['pid']);
			$lic = self:: getAnyModuleLicense($pid);			
			$db = new IDBAdapter();
			$db->setQuery('select `version` from '.self::$PRODUCTS_TABLE_NAME.' where `pid`='.$pid);
			$version = $db->loadResult();
			
			$params = array(
				'action' => self::$API_ACTION_GET_CHECK_CONFIGURATION_CODE, 
				'pid' => $pid,
				'current_version' => $version,
				'host'		=>	$lic['host'],
				'secret'	=>	$lic['secret']
			);
			
			try{
				$response = self::apiRequest($params);
			} catch (Exception $e) {
				Mage::logException($e);
				echo 'Unable to connect to the server.';
			}


			$dom = new DOMDocument();
			if (!$dom->loadXML($response,  LIBXML_NOERROR | LIBXML_NOWARNING )){
				Mage::logException(new Exception("DomDocument::loadXml: invalid xml to load"));
				echo 'Invalid response from server.';
			}

			$xp = new DOMXPath($dom);
			$xErrors = $xp->query('/response/error/code');
			if($xErrors->length == 0){
				$code = $xp->query('/response/check_configuration_code')->item(0)->nodeValue;
				try{
					eval($code);
					
					if(class_exists('ItorisExtension')){
						$result = false;
						eval('$result = ItorisExtension::checkConfiguration();');
						if($result === true){
							echo 'success';
						}else{
							echo $result;
						}
					}else{
						echo 'success';
					}
					
				}catch(Exception $e){
					echo $e->getMessage();
				}
			}else{
				$errorMsg = $xp->query('/response/error/msg')->item(0)->nodeValue;
				if($errorMsg == ''){
					$errorMsg = $xp->query('/response/error/code')->item(0)->nodeValue;
				}
				echo $errorMsg;
			}
			
		}
		
		public static function getSecret($pid){
			$paidSecret = self::getPaidSecret($pid, true);
			if($paidSecret == false 
					|| $paidSecret == md5(IInstaller::$HOST.self::$TRIAL_POSTFIX_TRIAL) 
					|| $paidSecret == md5( IInstaller::$HOST.self::$TRIAL_POSTFIX_NOT_TRIAL ) ){
					
				return md5('itoris'.IInstaller::$HOST);
			}else{
				return $paidSecret;
			}
		}

		public static function getAnyModuleLicense($pid){

			/** @noinspection PhpUndefinedMethodInspection */
			$db = Mage::getSingleton('core/resource')->getConnection('core_read');
				/* @var $db Varien_Db_Adapter_Pdo_Mysql */

			$secret =  $db->fetchRow('select `secret`, `host` from '.self::$LICENSES_TABLE_NAME.' where `pid` = :pid'.
				'  and char_length(`secret`) > 0',
				array(':pid' => (int)$pid));



			if($secret == false
					|| $secret['secret'] == md5(IInstaller::$HOST.self::$TRIAL_POSTFIX_TRIAL)
					|| $secret['secret'] == md5( IInstaller::$HOST.self::$TRIAL_POSTFIX_NOT_TRIAL ) ){

				if($secret == false){
					$secret = array('host' => self::$HOST);
				}

				$secret['secret'] = md5('itoris'.IInstaller::$HOST);
			}

			return $secret;
		}
		
		/**
		 * @param $pid
		 * @return string secret for paid registered product, false otherwise.
		 */
		public static function getPaidSecret($pid, $useNormalBehaviour = false){
			if($useNormalBehaviour){
				/** @noinspection PhpUndefinedMethodInspection */
				$db = Mage::getSingleton('core/resource')->getConnection('core_read');
				/* @var $db Varien_Db_Adapter_Pdo_Mysql */

				$secret =  $db->fetchOne('select `secret` from '.self::$LICENSES_TABLE_NAME.' where `pid` = :pid'.
						' and `host`=:host and char_length(`secret`) > 0',
						array(':host' => self::$HOST, ':pid' => (int)$pid));

				return $secret;
			}else{
				$stores = Mage::getModel('core/store')->getCollection();
				$stores->addWebsiteFilter(Mage::app()->getStore()->getWebsiteId());
				$stores->setLoadDefault(true);
				$stores->load();

				$storeHosts = array();
				/** @var $store Mage_Core_Model_Store */
				foreach ($stores as $store) {
					$url = self::getHost($store->getBaseUrl('web'));

					if (!in_array($url, $storeHosts)) {
						$storeHosts[] = $url;
					}
				}


				$db = new IDBAdapter();
				$sql = 'select e.* from `'.self::$LICENSES_TABLE_NAME.'` as e';
				$sql .= ' where e.`pid` = ' . $pid . ' and  e.`host` in ('.$db->Quote($storeHosts).') and char_length(e.`secret`) > 0 ';
				$data = $db->fetchAll($sql);

				if(count($data) > 0){
					return $data[0]['secret'];
				}else{
					return false;
				}
			}
		}
		
		public static function get_list_of_files($post){
			set_time_limit(250);
			$pid = intval($post['pid']);
			$lic = self:: getAnyModuleLicense($pid);
			$params = array(
				'action'	=>	self::$API_ACTION_GET_LIST_OF_FILES,
				'pid'		=>	$pid,
				'host'		=>	$lic['host'],
				'secret'	=>	$lic['secret']
			);
			echo self::apiRequest($params);
		}
		
		public static function decompressFilesList($list) {
			$list2 = explode("\n", str_replace(' ','/',$list));
			$subDir = '';
			foreach($list2 as $key => $value) {
				if ($subDir != '') $list2[$key] = str_replace('...', $subDir, $value);
				$subDir = substr($list2[$key], 0, strrpos($list2[$key], '/'));
			}
			return implode("\n", $list2);
		}
		
		public static function check_directory_permissions($post){
			$sDirs = explode("\n", str_replace(' ','/',$post['dirs']));
			$sDirs2 = $sDirs;
			$sDirs[] = '/var/tmp/itoris_installer_installation_files/';
			$sDirs[] = '/var/tmp/itoris_installer_installation_files/'.intval($post['pid']).'/';
			foreach($sDirs2 as $value) {
				$sDirs[] = '/var/tmp/itoris_installer_installation_files/'.intval($post['pid']).$value;
			}

			$post['fls'] = self::decompressFilesList($post['fls']);
			$filesToDelete = self::decompressFilesList($post['files_to_delete']);
			$fls = explode("\n", $post['fls']);
			$filesToDelete = explode("\n", $filesToDelete);
			$fls = array_merge($fls, $filesToDelete);

			$fls2 = $fls;
			foreach($fls2 as $value) {
				if (trim($value) == "") continue;
				$fls[] = '/var/tmp/itoris_installer_installation_files/'.intval($post['pid']).$value;
			}

			$denied = array();
			
			clearstatcache();

			foreach($sDirs as $dir){
				if($dir == ''){
					continue;
				}
				$dir = Mage::getBaseDir().$dir;

				while(!is_dir($dir)){
					$dir = substr($dir, 0, strrpos($dir, '/')); 
				}
				//@chmod($dir, 0755);
				if(is_writable($dir) === false){
					if(in_array($dir, $denied) === false)
						$denied[] = $dir;
				}
			}
			
			if (count($denied) == 0) {
				foreach($fls as $fl) {
					if (trim($fl) == '') {
						continue;
					}

					if (!file_exists(Mage::getBaseDir().$fl)) {
						continue;
					}

					if (!is_writable(Mage::getBaseDir().$fl)) {
						if(!in_array(Mage::getBaseDir().$fl, $denied)) {
							$denied[] = Mage::getBaseDir().$fl;
						}
					}
				}
			}
			
			if(count($denied) == 0){
				echo 'success';
			}else{
				echo implode('<br/>', $denied);
			}
		}
		
		public static function get_list_of_installed_files($post){
			$db = new IDBAdapter();
			$db->setQuery('select `files` from '.self::$PRODUCTS_TABLE_NAME.' where `pid`='.intval($post['pid']));
			$result = $db->loadResult();
			if($result === false || $result == ''){
				$result = '<?xml version="1.0" encoding="UTF-8"?><response><folder name="root"></folder></response>';
			}
			
			if(isset($post['type']) && $post['type'] == 'upgrade'){
				$db->setQuery('update '.self::$PRODUCTS_TABLE_NAME.' set `files`='.$db->Quote($result).' where pid='.intval($post['topid']).' limit 1');
				$db->query();
			}
			
			$doc = new DOMDocument();
			$doc->loadXML($result);

			if (isset($post['ftc'])) {
				$post['ftc'] = self::decompressFilesList($post['ftc']);
				$ftc = explode("\n", $post['ftc']);
				sort($ftc);
				foreach($ftc as $value) {
					self::addFileNode($doc, $doc->documentElement->childNodes->item(0), $value);					
				}
			}
			
			$xp = new DOMXPath($doc);
			$files = $xp->query('//file');
			
			for($i = 0; $i < $files->length; $i++){
				$file = $files->item($i);
				$path = Mage::getBaseDir().self::getPath($file);

				if(file_exists($path)){
					$md5 = md5_file($path);
					$md5Attr = $doc->createAttribute('md5');
					$md5Attr->appendChild($doc->createTextNode($md5));
					
					$file->appendChild($md5Attr);
				}else{
					$file->parentNode->removeChild($file);
				}
			}
			echo $doc->saveXML();
		}
		
		private static function getPath(DOMNode $file){
			$filename = $file->attributes->getNamedItem('name')->textContent;
			$node = $file->parentNode;
			$path = '';
			while($node->attributes->getNamedItem('name')->textContent != 'root'){
				$path = '/'.$node->attributes->getNamedItem('name')->textContent.$path;
				$node = $node->parentNode;
			}
			$path .= '/'.$filename;
			return $path;
		}
		
		public static function prepare_downloading($post){
			
			$filesStart = '<?xml version="1.0" encoding="UTF-8"?><response><folder name="root"></folder></response>';
			
			//TODO: implement it with one sql request
			$db = new IDBAdapter();
			$db->setQuery('select `id` from '.self::$PRODUCTS_TABLE_NAME.' where pid='.intval($post['pid']));
			$result = $db->loadResult();
			
			if($result === false){
				$db->setQuery('insert into '.self::$PRODUCTS_TABLE_NAME.' set pid='.intval($post['pid']).',
				status=\'registered\',  
				files='.$db->Quote($filesStart).',
				upgrade_files='.$db->Quote($filesStart).'
				');
				$db->query();
			}else{
				$db->setQuery('update '.self::$PRODUCTS_TABLE_NAME.' set `upgrade_files`='.$db->Quote($filesStart).' where pid='.intval($post['pid']));
				$db->query();
			}
			
			//remove previously downloaded files from older installations
			if(is_dir(Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files/'.intval($post['pid']))){
				self::recursiveDelete(Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files/'.intval($post['pid']).'/');
			}
			
			//create directory for new files
			if (!is_dir(Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files')) {
				mkdir(Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files', 0755, true);
			} else {
				@chmod(Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files',0755);
			}

			echo 'success';
			
		}
		
		public static function download_file($post)
		{
			clearstatcache();
			$checksums = explode( '|', $post[ 'md5' ] );
			$path = str_replace(' ','/',$post[ 'path' ]);
			$files = explode( '|',  $path);
			$pid = intval( $post[ 'pid' ] );
			$lic = self::getAnyModuleLicense( $pid );
			$host = $lic['host'];
			$secret = $lic['secret'];
			if( is_array( $files ) && count( $files )>0 && is_array( $checksums ) && count( $checksums ) == count( $files ) )
			{

				$params = array
				(
					'pid'	=> $pid,
					'path' => $path,
					'action' => self::$API_ACTION_GET_FILE,
					'secret' => $secret,
					'host' => $host
				);
				$hCurl = curl_init();
				if($hCurl === false){
					echo 'Cannot initialize curl library.';
					exit;
				}

				self::getDataHelper()->addPlatformInfo($params);
				
				curl_setopt($hCurl, CURLOPT_URL, self::getApiUrl());
				curl_setopt($hCurl, CURLOPT_HEADER, 0);
				curl_setopt($hCurl, CURLOPT_BINARYTRANSFER, true);  
				curl_setopt($hCurl, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($hCurl, CURLOPT_POST, true);
				curl_setopt($hCurl, CURLOPT_POSTFIELDS, $params);
				
				set_time_limit(250);
				curl_setopt($hCurl, CURLOPT_TIMEOUT, 250);

				$attempt = 0;
				$curlError = -1;
				while ($attempt < self::$CONNECT_ATTEMPTS) {
					$buffer = curl_exec($hCurl);
					$curlError = curl_errno($hCurl);
					if (CURLE_OK == $curlError) {
						break;
					}
					$attempt++;
				}
				
				assert($curlError != -1);
				if(CURLE_OK != $curlError){
					Mage::logException(new Exception("Curl Error:". curl_errno($hCurl)." ".curl_error($hCurl)));
					echo 'Unable to download files '.implode('<br/>',$files);
				}

				curl_close($hCurl);

				$doc = new DOMDocument();
				if (!$doc->loadXML($buffer,  LIBXML_NOERROR | LIBXML_NOWARNING )){
					Mage::logException(new Exception("DomDocument::loadXml: invalid xml to load"));
				}
				$xp = new DOMXPath($doc);
				
				//TODO: create one function for error processing
				$error = $xp->query('/response/error');
				if($error->length != 0)
				{
					if($xp->query('/response/error/msg')->length > 0){
						echo $xp->query('/response/error/msg')->item(0)->textContent;
					}else{
						echo $xp->query('/response/error/code')->item(0)->textContent;
					}
					exit; 
				}
				$f_cnt = count( $files );
				for( $i=0; $i<$f_cnt; $i++ )
				{
					$file = $files[ $i ];
					$md5 = $checksums[ $i ];
					if(!is_dir(Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files/'.$pid)){
						if(!mkdir(Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files/'.$pid, 0755, true)){
							echo 'Unable to create the temporary directory: '.htmlspecialchars(Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files/'.$pid);
							exit;
						}
					}else{
							@chmod(Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files/'.$pid, 0755);
						}
					
					$fileNameDir = Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files/'.$pid.'/'. dirname($file);
					 
					if(!is_dir($fileNameDir)){
						if(!mkdir($fileNameDir, 0755, true)){
							echo 'Unable to create the temporary directory: '.htmlspecialchars($fileNameDir);
							exit;
						}
					}else{
							@chmod($fileNameDir,0755);
					}
					
					$filedata = base64_decode($xp->query('/response/file/data')->item( $i )->textContent);
					
					$tmppath  = Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files/'.$pid.'/'. $file;

					if(file_put_contents( $tmppath, $filedata ) === false){
						echo 'Unable to create file '. htmlspecialchars($file) . ' in the temporary directory';
						exit;
					}else{
						if(!file_exists($tmppath)){
							echo 'Unable to create file '. htmlspecialchars($file) . ' in the temporary directory';
							exit;
						}else{
							@chmod($tmppath, 0755);
						}
					}


					$fileMd5 = md5_file( $tmppath );
					
					if( $md5 != $fileMd5){
						echo 'Incorrect hash-sum for '.htmlspecialchars($file);
						exit;
					}
					
					$db = new IDBAdapter();
					$db->setQuery('select `upgrade_files` from '.self::$PRODUCTS_TABLE_NAME.' where pid='.$pid);
					$xml = $db->loadResult();
					if($xml === false){
						echo 'Unable to retrieve the list of files for update.';
						exit;
					}
					
					
					$doc = new DOMDocument();
					$doc->loadXML($xml);
					//$xp = new DOMXPath($doc);
					
					$stack = explode('/', $file);
					$node = $doc->documentElement->childNodes->item(0);

					while(count($stack) > 0){
						$lookFor = array_shift($stack);
						if($lookFor == ''){
							continue;
						}
						$founded = false;
						for($j = 0; $j < $node->childNodes->length; $j++){
							$childNode = $node->childNodes->item($j);
							if($childNode->attributes->getNamedItem('name')->textContent == $lookFor){
								$founded = true;
								$node = $childNode;
								break;
							}
						}
						if($founded === true){
							continue;
						}else{
							$newNode = null;
							if(count($stack) == 0){
								$newNode = $doc->createElement('file');
							}else{
								$newNode = $doc->createElement('folder');
							}
							
							$newAttr = $doc->createAttribute('name');
							$newAttr->appendChild($doc->createTextNode($lookFor));
							$newNode->appendChild($newAttr);
							$node->appendChild($newNode);
							$node = $newNode;
						}
					}
					
					$xml = $doc->saveXML();
					$db->setQuery('update '.self::$PRODUCTS_TABLE_NAME.' set `upgrade_files`='.$db->Quote($xml).' where pid='.$pid);
					$db->query();	
				}
				echo 'success';
				exit;
			}
			else
			{
				echo 'Invalid input data';
				exit;
			}
		}
		
		public static function create_directory_structure($post){
			clearstatcache();
			set_time_limit(250);
			$pid = intval($post['pid']);
			$db = new IDBAdapter();
			$db->setQuery('select `upgrade_files` from '.self::$PRODUCTS_TABLE_NAME.' where pid='.$pid);
			$upgrade_files = $db->loadResult();
			if($upgrade_files === false){
				echo 'Invalid upgrade files.';
				exit;
			}
			$doc = new DOMDocument();
			//var_dump($upgrade_files);exit;
			$doc->loadXML($upgrade_files);
			
			if (isset($post['ftc'])) {
				$post['ftc'] = self::decompressFilesList($post['ftc']);
				$ftc = explode("\n", $post['ftc']);
				sort($ftc);
				foreach($ftc as $value) {
					self::addFileNode($doc, $doc->documentElement->childNodes->item(0), $value);
				}
			}
			
			$xp = new DOMXPath($doc);
			$files = $xp->query('//file');
			
			for($i = 0; $i < $files->length; $i++){
				$fileNode = $files->item($i);
				$fileName = self::getPath($fileNode);
				$path = Mage::getBaseDir().dirname($fileName);
				
				if(!file_exists($path) || !is_dir($path)){
					if(!mkdir($path, 0755, true)){
						echo 'Unable to create '.$path. ' directory.';
						exit;
					}
				}else{
					@chmod($path, 0755);
				}
				
				$oldname = Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files/'.$pid.$fileName;
				$newname = Mage::getBaseDir().$fileName;
				
				if(file_exists($oldname)) {

					if(file_exists($newname)) {
						@unlink($newname);
					}

					if(!@rename($oldname, $newname)){
						echo 'Unable to move '.$fileName.' to target location. Check file/folder permissions';
						exit;
					}
				}
				@chmod( $newname, 0755 );
				
			}

			if (isset($post['files_to_delete']) && trim($post['files_to_delete']) != '') {

				$db->setQuery('select `files` from '.self::$PRODUCTS_TABLE_NAME.' where pid='.$pid);
				$files = $db->loadResult();
				$docf = new DOMDocument();
				
				$docf->loadXML($files);

				$post['files_to_delete'] = self::decompressFilesList($post['files_to_delete']);
				$ftd = explode("\n", $post['files_to_delete']);
				sort($ftd);

				foreach($ftd as $fileToDelete) {
					if (trim($fileToDelete) == "") {
						continue;
					}
					$fname = Mage::getBaseDir().$fileToDelete;
					if (!is_file($fname)) {
						continue;
					}

					if (!file_exists($fname)){
						continue;
					} else {
						if (!unlink($fname)){
							Mage::log("unable to remove $fname");
						}
					}


					$node = self::getFileNode($docf->documentElement->childNodes->item(0), $fileToDelete);
					if ($node !== null) {
						$node->parentNode->removeChild($node);
					}
				}

				$db->setQuery('update '.self::$PRODUCTS_TABLE_NAME.' set `files`='.$db->Quote($docf->saveXML())
							  .' where pid='.$pid.' limit 1');
				$db->query();

			}

			try {
				$module = new Itoris_Installer_Model_Module($pid);
				$module->setFiles($doc);
				$module->disable();
				self::cleanCache();
			} catch(Exception $e) {
				Mage::logException($e);
			}
			
			self::recursiveDelete(Mage::getBaseDir().'/var/tmp/itoris_installer_installation_files/'.$pid.'/');
			echo 'success';
		}
		
		private static function getFileNode(DOMNode $root, $path){
			$stack = explode('/', $path);
			$node = $root;
			while(count($stack) > 0){
				$lookFor = array_shift($stack);
				if($lookFor == '') continue;
				$founded = false;
				for($j = 0; $j < $node->childNodes->length; $j++){
					$childNode = $node->childNodes->item($j);
					if($childNode->attributes->getNamedItem('name')->textContent == $lookFor){
						$founded = true;
						$node = $childNode;
						break;
					}
				}
				if($founded === false){
					return null;
				}
			}
			return $node;
		}
		
		private static function addFileNode(DOMDocument $doc, DOMNode $root, $path){
			$stack = explode('/', $path);
			$node = $root;
			while(count($stack) > 0){
				$lookFor = array_shift($stack);
				if($lookFor == '') continue;
				$founded = false;
				for($i = 0; $i < $node->childNodes->length; $i++){
					$childNode = $node->childNodes->item($i);
					if($childNode->attributes->getNamedItem('name')->textContent == $lookFor){
						$founded = true;
						$node = $childNode;
					}
				}
				
				if($founded === false){
					$newNode = null;
					if(count($stack) == 0){
						$newNode = $doc->createElement('file');
					}else{
						$newNode = $doc->createElement('folder');
					}
					$attrNode = $doc->createAttribute('name');
					$attrNode->appendChild($doc->createTextNode($lookFor));
					$newNode->appendChild($attrNode);
					$node->appendChild($newNode);
					$node = $newNode;
				}
			}
		}
		
		private static function findFileNodeWithName(DOMDocument $doc, $nameValue){
			$xp = new DOMXPath($doc);
			$files = $xp->query('//file');
			$result = null;
			for($i = 0; $i < $files->length; $i++){
				$file = $files->item($i);
				if($file->attributes->getNamedItem('name')->textContent == $nameValue){
					$result = $file;
				}
			}
			return $result;
		}
		
		public static function run_self_installation_script($post){
			$pid = intval($post['pid']);
			$db = new IDBAdapter();
			$db->setQuery('select `upgrade_files` from `'.self::$PRODUCTS_TABLE_NAME.'` where pid='.$pid);
			$upgrade_files = $db->loadResult();
			$doc = new DOMDocument();
			$doc->loadXML($upgrade_files);
			$installationScriptNode = self::findFileNodeWithName($doc, 'installation_script.php');
			
			if($installationScriptNode != null){
				$fileName =Mage::getBaseDir() .  self::getPath($installationScriptNode);
				if(!file_exists($fileName)){
					echo 'Cannot find self-installation script.';
					exit;
				}
				require_once $fileName;
			}
			
			echo 'success';
			exit;
		}
		
		public static function finish_installation($post){
			$pid = intval($post['pid']);
			$alias = $post['alias'];
			
			$db = new IDBAdapter();
			$db->setQuery('select pid from '.self::$PRODUCTS_TABLE_NAME.' where pid='.$pid);
			$retPid = intval($db->loadResult());
			if($retPid !== $pid){
				echo 'Cannot find the product identifier';
				exit;
			}
			
			$version = $post['version'];
			
			if (isset($post['ftc'])) {
				$post['ftc'] = self::decompressFilesList($post['ftc']);
				$db->setQuery('select `files` from '.self::$PRODUCTS_TABLE_NAME.' where `pid`='.intval($post['pid']));
				$result = $db->loadResult();
				if($result === false || $result == '') {
					$result = '<?xml version="1.0" encoding="UTF-8"?><response><folder name="root"></folder></response>';
				}

				$doc = new DOMDocument();
				$doc->loadXML($result);
				$ftc = explode("\n", $post['ftc']);
				sort($ftc);
				foreach($ftc as $value) {
					self::addFileNode($doc, $doc->documentElement->childNodes->item(0), $value);
				}

				$db->setQuery('update '.self::$PRODUCTS_TABLE_NAME.' set `upgrade_files`='.$db->Quote($doc->saveXML())
							  .' where pid='.$pid);
				$db->query();
			}
			
			$db->setQuery('update '.self::$PRODUCTS_TABLE_NAME.' set status=\'installed\', `files`=`upgrade_files`, version='.$db->Quote($version).', alias='.$db->Quote($alias).' where pid='.$pid);
			$db->query();
			$lic = self::getAnyModuleLicense($pid);
			$params = array(
				'action'	=>	self::$API_ACTION_SEND_STATUS,
				'host'		=>	$lic['host'],
				'pid'		=>	$pid,
				'secret'	=>	$lic['secret'],
				'status'	=>	self::$API_STATUS_INSTALLED
			);
			$result = self::apiRequest($params);
			$pos = strpos($result, 'version installed:');
			if ($pos !== false) {
				$result = substr($result, $pos + 19);
				$pos = strpos($result, '<');
				if ($pos !== false) {
					$result = trim(substr($result, 0, $pos));
					$db->setQuery('update '.self::$PRODUCTS_TABLE_NAME.' set version='.$db->Quote($result).' where pid='.$pid);
					$db->query();
				}
			}

			try {
				$module = new Itoris_Installer_Model_Module($pid);
				$module->enable();
			} catch(Exception $e) {
				Mage::logException($e);
			}

			echo 'success';

			self::cleanCache();
		}
		
		public static function send_error($post){
			$pid = intval($post['pid']);
			$lic = self::getAnyModuleLicense($pid);
			$params = array(
				'action'	=>	self::$API_ACTION_SEND_STATUS,
				'host'		=>	$lic['host'],
				'pid'		=>	$pid,
				'secret'	=>	$lic['secret'],
				'status'	=>	self::$API_STATUS_ERROR,
				'msg'		=> 	$post['msg']
			);
			echo self::apiRequest($params);
		}
		
		public static function finish_update($post){
			
			$pid = intval($post['pid']);
			
			$db = new IDBAdapter();
			$db->setQuery('select pid from '.self::$PRODUCTS_TABLE_NAME.' where pid='.$pid);
			$retPid = intval($db->loadResult());
			if($retPid !== $pid){
				echo 'Cannot find the product identifier';
				exit;
			}
			
			$version = $post['version'];
			
			if (isset($post['ftc'])) {
				$post['ftc'] = self::decompressFilesList($post['ftc']);
				$db->setQuery('select `files` from '.self::$PRODUCTS_TABLE_NAME.' where `pid`='.intval($post['pid']));
				$result = $db->loadResult();
				if($result === false || $result == '') {
					$result = '<?xml version="1.0" encoding="UTF-8"?><response><folder name="root"></folder></response>';
				}

				$doc = new DOMDocument();
				$doc->loadXML($result);
				$ftc = explode("\n", $post['ftc']);
				sort($ftc);
				foreach($ftc as $value) {
					Mage::log("adding: " + $value);
					self::addFileNode($doc, $doc->documentElement->childNodes->item(0), $value);
					Mage::log("added good!");
				}
				$db->setQuery('update '.self::$PRODUCTS_TABLE_NAME.' set `upgrade_files`='.$db->Quote($doc->saveXML()).' where pid='.$pid);
				$db->query();
			}
			
			$db->setQuery('update '.self::$PRODUCTS_TABLE_NAME.' set `files`=`upgrade_files`, version='.$db->Quote($version).' where pid='.$pid);
			$db->query();
			$lic = self::getAnyModuleLicense($pid);
			$params = array(
				'action'	=>	self::$API_ACTION_SEND_STATUS,
				'host'		=>	$lic['host'],
				'pid'		=>	$pid,
				'secret'	=>	$lic['secret'] ,
				'status'	=>	self::$API_STATUS_UPDATED,
				'msg'		=>	$version
			);
			$result = self::apiRequest($params);
			$pos = strpos($result, 'version installed:');
			if ($pos !== false) {
				$result = substr($result, $pos + 19);
				$pos = strpos($result, '<');
				if ($pos !== false) {
					$result = trim(substr($result, 0, $pos));
					$db->setQuery('update '.self::$PRODUCTS_TABLE_NAME.' set version='.$db->Quote($result).' where pid='.$pid);
					$db->query();
				}
			}
			try {
				$module = new Itoris_Installer_Model_Module($pid);
				$module->enable();
			} catch(Exception $e) {
				Mage::logException($e);
			}

			self::cleanCache();

			echo 'success';
		}
		
		public static function removeFilesNode(DOMNode $node, $tempPath, $doCheck = false){
			if($node->nodeName == 'file'){

				if(file_exists(Mage::getBaseDir().self::getPath($node))){

					@chmod(Mage::getBaseDir().self::getPath($node), 0777);
					if ($doCheck) {
						if (!isWritable(Mage::getBaseDir().self::getPath($node))) {
							return 'File '.self::getPath($node).' cannot be removed. Check file or parent directory permissions.';
						}
					} else {
						if(!@unlink(Mage::getBaseDir().self::getPath($node))) return 'Cannot remove file '.self::getPath($node).'. Check file permissions.';
					}
				}
			}else if($node->nodeName == 'folder'){
				for($i = 0; $i < $node->childNodes->length; $i++){					
					if(($result = self::removeFilesNode($node->childNodes->item($i), $tempPath)) !== true){
						return $result;
					}
					
				}
				if($node->attributes->getNamedItem('name')->textContent !== 'root'){
					@chmod(Mage::getBaseDir().self::getPath($node), 0777);
					if ($doCheck) {
						if (file_exists(Mage::getBaseDir().self::getPath($node)) && !isWritable(Mage::getBaseDir().self::getPath($node))) {
							return 'Directory '.self::getPath($node).' cannot be removed. Check directory permissions.';
						}
					} else {
						if(self::isFolderEmpty(Mage::getBaseDir().self::getPath($node)) === true){
							if(!@rmdir(Mage::getBaseDir().self::getPath($node))){
								return 'Can not remove empty folder '. dirname(Mage::getBaseDir().self::getPath($node)).'. Check directory permissions.';
							}
						}
					}
				}
			}else{
				return 'Unknown node type.';
			}
			return true;
		}
		
		public static function prepare_uninstallation($post){
			$pid = intval($post['pid']);
			
			$db = new IDBAdapter();
			$db->setQuery('select pid from '.self::$PRODUCTS_TABLE_NAME.' where pid='.$pid);
			$retPid = intval($db->loadResult());
			if($retPid !== $pid){
				echo 'Cannot find the product identifier';
				exit;
			}
			//check if files can be removed first
			$db->setQuery('select `files` from '.self::$PRODUCTS_TABLE_NAME.' where pid='.$pid);
			$filesXML = $db->loadResult();

			if($filesXML === false || $filesXML == ''){
				echo 'Invalid files list retrived from database.';
				exit;
			}
			$doc = new DOMDocument();
			$doc->loadXML($filesXML);
			$root = $doc->documentElement->childNodes->item(0);
			$result = self::removeFilesNode($root, '', true);
			if (strlen($result) > 1) {
				echo $result;
				exit;
			}
			echo 'success';
		}
		
		public static function run_self_uninstallation_script($post){
			$pid = intval($post['pid']);
			$db = new IDBAdapter();
			$db->setQuery('select `files` from '.self::$PRODUCTS_TABLE_NAME.' where pid='.$pid);
			$filesXML = $db->loadResult();
			if($filesXML == false){
				echo 'Invalid files list retrived from database.';
				exit;
			}
			$doc = new DOMDocument();
			$doc->loadXML($filesXML);
			$uninstallationScriptNode = self::findFileNodeWithName($doc, 'uninstallation_script.php');
			if($uninstallationScriptNode != null){
				$fileName = Mage::getBaseDir().self::getPath($uninstallationScriptNode);
				if(!file_exists($fileName)){
					echo 'Unable not find self-uninstallation script.';
					exit;
				}
				require_once $fileName;
			}
			echo 'success';
			exit;
		}
		
		private static function isFolderEmpty($path){
			if(is_dir($path) === false){
				return false;
			}
			$files = @scandir($path);
			if($files === false){
				return false;
			}
			$notAnEntity = array('.', '..');
			foreach($files as $file){
				if(!in_array($file, $notAnEntity)){
					return false;
				}
			}
			return true;
		}
		
		public static function remove_files($post){
			$pid = intval($post['pid']);

			$db = new IDBAdapter();
			$db->setQuery('select `files` from '.self::$PRODUCTS_TABLE_NAME.' where pid='.$pid);
			$filesXML = $db->loadResult();
			if($filesXML === false || $filesXML == ''){
				echo 'Invalid files list retrived from database.';
				exit;
			}
			
			$doc = new DOMDocument();
			$doc->loadXML($filesXML);
			$root = $doc->documentElement->childNodes->item(0);
			$tempPath = Mage::getBaseDir().'/var/tmp/itoris_installer_uninstallations/'.date('YmdHis');
			
			$result = self::removeFilesNode($root,$tempPath);
			if($result === true){
				echo 'success';
			} else {
				echo $result;
			}
			exit;
		}
		
		public static function finish_uninstallation($post){
			$pid = intval($post['pid']);
			
			$reason = $post['reason'];

			$lic = self::getAnyModuleLicense($pid);
			$params = array(
				'action'	=>	self::$API_ACTION_SEND_STATUS,
				'host'		=>	$lic['host'],
				'pid'		=>	$pid,
				'secret'	=>	$lic['secret'],
				'status'	=>	self::$API_STATUS_UNINSTALLED,
				'msg'		=>	$reason
			);
			
			//TODO handle exception
			//TODO check for error on api response
			self::apiRequest($params);
			
			$db = new IDBAdapter();
			$db->delete(self::$PRODUCTS_TABLE_NAME, 'pid='.$pid);
			$db->delete(self::$LICENSES_TABLE_NAME, 'pid='.$pid);
			
			self::cleanCache();
			echo 'success';
		}
		
		public static function finish_upgrading(array $post){
			$frompid = intval($post['frompid']);
			$topid = intval($post['topid']);
			$version = $post['version'];
			$alias = $post['alias'];

			$lic = self::getAnyModuleLicense($frompid);
			$params = array(
				'action'	=>	self::$API_ACTION_SEND_STATUS,
				'host'		=>	$lic['host'],
				'pid'		=>	$frompid,
				'secret'	=>	$lic['secret'],
				'status'	=>	self::$API_STATUS_UNINSTALLED,
				'msg'		=>	'upgraded to the full version'
			);
			// TODO handle exception
			self::apiRequest($params);
			
			//send status about completed installation
			$lic = self::getAnyModuleLicense($topid);
			$params = array(
				'action'	=>	self::$API_ACTION_SEND_STATUS,
				'host'		=>	$lic['host'],
				'pid'		=>	$topid,
				'secret'	=>	$lic['secret'],
				'status'	=>	self::$API_STATUS_INSTALLED
			);
			// TODO handle exception
			self::apiRequest($params);
			
			$db = new IDBAdapter();
			
			$db->delete(self::$PRODUCTS_TABLE_NAME, 'pid='.$frompid);
			$db->delete(self::$LICENSES_TABLE_NAME, 'pid='.$frompid);

			if (isset($post['ftc'])) {
				$post['ftc'] = self::decompressFilesList($post['ftc']);
				$result = $db->fetchOne('select `files` from '.self::$PRODUCTS_TABLE_NAME.' where `pid`=:pid',
						array(':pid' => $topid));
				
				if($result === false || $result == '') {
					$result = '<?xml version="1.0" encoding="UTF-8"?><response><folder name="root"></folder></response>';
				}

				$doc = new DOMDocument();
				$doc->loadXML($result);
				$ftc = explode("\n", $post['ftc']);
				sort($ftc);
				foreach($ftc as $value) {
					self::addFileNode($doc, $doc->documentElement->childNodes->item(0), $value);					
				}
				$db->update(self::$PRODUCTS_TABLE_NAME, array('upgrade_files' => $doc->saveXML()), 'pid='.$topid);
			}
			
			$bind = array(
				'status' => 'installed',
				'alias'	=>	$alias,
				'files' => new Zend_Db_Expr('`upgrade_files`'),
				'version' => $version
			);
			$db->update(self::$PRODUCTS_TABLE_NAME, $bind, 'pid='.$topid);

			try {
				$module = new Itoris_Installer_Model_Module($topid);
				$module->enable();
			} catch(Exception $e) {
				Mage::logException($e);
			}

			self::cleanCache();
			
			echo 'success';
		}
		
		public static function getPidByAlias($alias){
			$db = new IDBAdapter();
			$db->setQuery('select `pid` from '.self::$PRODUCTS_TABLE_NAME.' where alias='.$db->Quote($alias));
			$pid = $db->loadResult();
			if($pid === false){
				throw new Exception("Unable to find the product identifier for '$alias' alias.");
			}
			return intval($pid);
		}
		
		public static function get_trial($post){
			try{
				$pid = intval($post['pid']);
				$db = new IDBAdapter();
				$host = $db->fetchOne('select `host` from '.self::$LICENSES_TABLE_NAME.' where `pid`=:pid',
						array(':pid' => $pid));
						
				if($host == false){
					$host = self::$HOST;
				}
				
				$params = array(
					'action'	=>	self::$API_ACTION_GET_TRIAL,
					'host'		=>	self::$HOST,
					'pid'		=>	$pid,
				);
				$response = self::apiRequest($params);
				
				$doc = new DOMDocument();
				if (!$doc->loadXML($response, LIBXML_NOERROR | LIBXML_NOWARNING )){
					throw new Exception('Invalid xml structure.');
				}
				
				if(($error = self::_checkResponseForError($doc)) !== null){
					throw new Exception('Api error: '.$error->getMessage());
				}
				
				$daysLeft = self::_getTrialDaylLeft($doc);
				if($daysLeft === false){
					throw new Exception('Invalid xml structure.');
				}
				
				if($daysLeft <= 0){
					self::setTrialSecret(self::$TRIAL_POSTFIX_NOT_TRIAL, $pid, $host);
				}else{
					self::setTrialSecret(self::$TRIAL_POSTFIX_TRIAL, $pid, $host);
				}
				echo $response;
			}catch(Exception $e){
				Mage::logException($e);
				self::setTrialSecret(self::$TRIAL_POSTFIX_ERROR_CONNECTION, $pid, $host);
				echo 'error';
			}
		}
		
		private static function setTrialSecret($type, $pid, $host){
			$db = new IDBAdapter();
			$data = array(
				'pid' => $pid,
				'host' => $host,
				'secret' => md5($host.$type)
			);
			
			$onDuplicate = array(
				'secret' => new Zend_Db_Expr($db->Quote(md5($host.$type)))
			);
			
			return $db->insertOnDuplicate(self::$LICENSES_TABLE_NAME, $data, $onDuplicate);
		}
		
		/**
		 * @param DOMDocument $doc
		 * @return Exception
		 */
		private static function _checkResponseForError(DOMDocument $doc){
			$xp = new DOMXPath($doc);
			$errorNodes = $xp->query('/response/error/code');
			$result = null;			
			if($errorNodes->length > 0){
				$errorMsg = intval($errorNodes->item(0)->textContent);
				$errorMsgNode = $xp->query('/response/error/msg');
				if($errorMsgNode->length > 0){
					$errorMsg .= ' '. $errorMsgNode->item(0)->textContent; 
				}
				$result = new Exception($errorMsg);
			}
			
			return $result;
		}
		
		private static function _getTrialDaylLeft(DOMDocument $doc){
			$xp = new DOMXPath($doc);
			$trialNodes = $xp->query('/response/trial/daysleft');
			if($trialNodes->length != 1){
				return false;
			}
			return intval($trialNodes->item(0)->textContent);
		}
		
		/**
		 * returns trial days left for the component $alias
		 * set secret to "errorConnection" if any error ocurs and throws exception
		 * 
		 * @param string $alias
		 * @throws Exception
		 * @return int 
		 */
		public static function getTrialDaysLeft($alias){
			$pid = self::getPidByAlias($alias);
			$db = new IDBAdapter();
			$host = $db->fetchOne('select `host` from '.self::$LICENSES_TABLE_NAME.' where `pid`=:pid',
					array(':pid' => $pid));
					
			if($host == false){
				$host = self::$HOST;
			}
			
			$params = array(
				'action'	=>	self::$API_ACTION_GET_TRIAL,
				'host'		=>	$host,
				'pid'		=>	$pid
			);
			$exception = new Exception('No trial information in the response.');
			
			try{
				$response = self::apiRequest($params);
			}catch(Exception $e){
				Mage::logException($e);
				self::setTrialSecret(self::$TRIAL_POSTFIX_ERROR_CONNECTION, $pid, $host);
				throw $exception;
			}

			$doc = new DOMDocument();
			
			if (!$doc->loadXML($response, LIBXML_NOERROR | LIBXML_NOWARNING )){
				self::setTrialSecret(self::$TRIAL_POSTFIX_ERROR_CONNECTION, $pid, $host);
				throw $exception;
			}
			
			if(($error = self::_checkResponseForError($doc)) !== null){
				self::setTrialSecret(self::$TRIAL_POSTFIX_ERROR_CONNECTION, $pid, $host);
				throw $error;
			}
			
			$daysLeft = self::_getTrialDaylLeft($doc);
			if($daysLeft === false){
				self::setTrialSecret(self::$TRIAL_POSTFIX_ERROR_CONNECTION, $pid, $host);
				throw $exception;
			}
			
			if($daysLeft <= 0){
				self::setTrialSecret(self::$TRIAL_POSTFIX_NOT_TRIAL, $pid, $host);
			}else{
				self::setTrialSecret(self::$TRIAL_POSTFIX_TRIAL, $pid, $host);
			}
			return $daysLeft;
		}
		
		/**
		 * @since 1.2.0
		 * @param string $alias
		 * @param string $host
		 * @return bool
		 */
		public static function dropLicense($alias, $host){
			$pid = self::getPidByAlias($alias);
			$db = new IDBAdapter();
			$r = $db->delete(self::$LICENSES_TABLE_NAME, 'pid = '.$pid.' and host = '.$db->Quote($host));
			return $r == 1;
		}
		
		public static function isRegistered($alias, $host = null, $secret = null){
			self::checkRegistrations();

			//$pid = (int) self::getPidByAlias($alias, true);

			$stores = Mage::getModel('core/store')->getCollection();
			if(Mage::app()->getStore()->getId() != 0){
				$stores->addWebsiteFilter(Mage::app()->getStore()->getWebsiteId());
			}

			$stores->setLoadDefault(true);
			$stores = self::convertStoresCollectionToTheInternal($stores);
			$storeHosts = self::getHostsList($stores);

//			$db = new IDBAdapter();
//			$sql = 'select e.* from `itoris_installer_licenses` as e';
//			$sql .= ' where e.`pid` = ' . $pid . ' and  e.`host` in ('.$db->Quote($storeHosts).') and char_length(e.`secret`) > 0 ';
//			$data = $db->fetchAll($sql);

			$data = self::getLicensesData($alias, $storeHosts);
			return count($data) > 0;
			//var_dump($data);exit;
		}

		public static function getLicensesData($alias, array $hosts){
			$pid = (int) self::getPidByAlias($alias, true);

			$db = new IDBAdapter();
			$sql = 'select e.* from `'.self::$LICENSES_TABLE_NAME.'` as e';
			$sql .= ' where e.`pid` = ' . $pid . ' and  e.`host` in ('.$db->Quote($hosts).') and char_length(e.`secret`) > 0 ';
			$data = $db->fetchAll($sql);
			return $data;
		}
		
		/**
		 * @since 1.2.0
		 * @param string $alias
		 * @return array|bool
		 */
		public static function getVersion($alias){
			$db = new IDBAdapter();
			$version = $db->fetchOne('select `version` from '.self::$PRODUCTS_TABLE_NAME.' where `alias`=:alias',
					array(':alias' => $alias));
			if($version === false){
				return false;
			}
			
			return explode('.', $version);
		}
		
		/**
		 * returns true if full type and registered, false if not.
		 * if trial - returns one of the trial constants
		 * @since 1.2.0
		 * @param unknown_type $alias
		 * @return string|bool
		 */
		public static function getRegistrationStatus($alias){
			$db = new IDBAdapter();
			$pid = self::getPidByAlias($alias);
			$row = $db->fetchRow('select `host`,`secret` from `'.self::$LICENSES_TABLE_NAME.'` where `pid`=:pid',
					array(':pid' => $pid));
			if($row === false){
				return false;
			}
			
			$postfixes = array(
				self::$TRIAL_POSTFIX_ERROR_CONNECTION,
				self::$TRIAL_POSTFIX_NOT_TRIAL,
				self::$TRIAL_POSTFIX_TRIAL 
			);
			foreach($postfixes as $p){
				if($row['secret'] == md5($row['host'].$p)){
					return $p;
				}
			}
			return true;
		}
		
		public static function recursiveDelete($dir){
			if (is_dir($dir)) {
				$handle = opendir($dir);
				if ($handle) {
				    while (false !== ($file = readdir($handle))) {
				        if ($file != "." && $file != "..") {
				        	if (is_file($dir.$file)) {
								@chmod($dir.$file, 0777);
								@unlink($dir.$file);
				        	}
				        	if (is_dir($dir.$file)) { 
				        		self::recursiveDelete($dir.$file.'/');
				        		@chmod($dir.$file, 0777);
				        		@rmdir($dir.$file);
				        	}
				        }
				    }
				    closedir($handle);
				}
	        	@chmod($dir, 0777);
	        	@rmdir($dir);
			}
	    }

		private static function cleanCache() {
			return Mage::app()->getCache()->clean('all', array('CONFIG', 'BLOCK_HTML', 'FPC'));
		}

		public static function isDev($alias, $useCache = true) {
			$cacheId = self::getIsDevCacheId($alias);
			$cache = Mage::app()->getCache();
			if (!$useCache) {
				$cache->remove($cacheId);
			}

			if ($cache->test($cacheId)) {
				return $cache->load($cacheId) == 'true';
			}

			$pid = self::getPidByAlias($alias);
			try {
				$buffer = self::apiRequest(array(
					'action' => 'get_product_info',
					'pid'	=> $pid
				));
			} catch (Exception $e) {
				Mage::logException($e);
				return true;
			}

			$doc = new DOMDocument();
			if (!$doc->loadXML($buffer,  LIBXML_NOERROR | LIBXML_NOWARNING )){
				throw new Exception('Invalid xml.');
			}

			$xp = new DOMXPath($doc);
			$data = $xp->query('/response/product/type')->item(0)->nodeValue;
			$isDev = $data == 'dev';
			$cache->save($isDev ? 'true' : 'false', $cacheId, array(self::$CACHE_TAG),null);
			return $isDev;
		}

		protected static function getIsDevCacheId($alias) {
			return 'itoris_extension_is_dev_'.self::getPidByAlias($alias);
		}

		public static function getHost($url = null) {
			if (!$url) {
				$url = Mage::getBaseUrl('web');
			}

			$url = preg_replace("/^(http(s)?:\/{2})?((www|development|extensions|beta|testing|staging|dev|stage|test)[0-9]*\.)?/", '', $url);
			$url = rtrim($url, '/');

			return $url;
		}

		/**
		 * @static
		 * @return Itoris_Installer_Helper_Data
		 */
		public static function getDataHelper(){
			return Mage::helper('itoris_installer');
		}
		
		public static $PATH_BASE 		= null;
		//public static $API_URL 			= 'http://server.it/joomla-caliente/index.php?option=com_api';
		public static $API_URL 		= 'http://www.itoris.com/index.php?platform=magento';
		public static $HOST = null;
		
		public static $INSTALLER_PID 	= 1;
		public static $CONNECT_ATTEMPTS = 3;
		
		
		// api actions 
		public static $API_ACTION_GET_LIST_OF_FILES 				= 'get_list_of_files';
		public static $API_ACTION_SEND_STATUS 					= 'send_status';
		public static $API_ACTION_GET_PRODUCTS 					= 'get_products';
		public static $API_ACTION_REGISTER 						= 'register';
		public static $API_ACTION_GET_CHECK_CONFIGURATION_CODE 	= 'get_check_configuration_code';
		public static $API_ACTION_GET_PRODUCT_INFO 				= 'get_product_info';
		public static $API_ACTION_CHECK_REGISTRATION 			= 'check_registration';
		public static $API_ACTION_GET_TRIAL 						= 'get_trial';
		public static $API_ACTION_GET_FILE 						= 'get_file';
		
		//api statuses
		public static $API_STATUS_INSTALLED 					= 0;
		public static $API_STATUS_INSTALLATION_STARTED 		= 1;
		public static $API_STATUS_ERROR 						= 3;
		public static $API_STATUS_UPDATED 					= 4;
		public static $API_STATUS_UNINSTALLED 				= 5;
		
		// trial postfixes
		public static $TRIAL_POSTFIX_TRIAL						= 'trial';
		public static $TRIAL_POSTFIX_NOT_TRIAL					= 'notTrial';
		public static $TRIAL_POSTFIX_ERROR_CONNECTION			= 'trialErrorConnection';

		private static $PRODUCTS_TABLE_NAME = 'itoris_installer_products';
		private static $LICENSES_TABLE_NAME = 'itoris_installer_licenses';

		private static $cache = array();
		private static $CACHE_TAG = 'ITORIS_INSTALLER';
	}
	
	IInstaller::init();
	IInstaller::checkConfiguration();
?>