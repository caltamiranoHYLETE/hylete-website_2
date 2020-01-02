<?php
	class Itoris_Installer_IndexController extends Mage_Adminhtml_Controller_Action{

        protected function _construct(){
            $this->setUsedModuleName('Itoris_Installer');
            parent::_construct();
        }
		public function indexAction(){
			try{
				require_once dirname(__FILE__).DS.'..'.DS.'code'.DS.'IInstaller.php';
				$conn = IInstaller::testItorisApiConnection();

				if($conn['ping'] == 'false'){
					throw new Exception('Cannot connect to the IToris update server. Please make sure your web '
							.'server can establish the Internet connection with <a href="http://www.itoris.com"'
							.'>http://www.itoris.com</a>');
				}

				$at = $this->getRequest()->get('at');
				$at = $at == null ? 'p' : $at;

				IInstaller::checkDatabaseIntegrity();
				IInstaller::checkRegistrations();
				$installedProducts = IInstaller::getInstalledProducts();
				$avaliableProducts = '<textarea id="ii_avaliable_products" style="display:none;" cols="0" rows="0">'
						.htmlspecialchars(IInstaller::getAvailableProducts()).'</textarea>';

				//Mage::app()->getCache()->clean();
				$this->loadLayout();
				$text = $this->getLayout()->createBlock('core/text');

				$defaultHostsJsArray = Zend_Json::encode($this->getDefaultHosts());

				$text->setText(

				$avaliableProducts.'
					<script type="text/javascript">
						var INSTALLER_BASE_URL = "'.Mage::getBaseUrl('web').'";
						var INSTALLER_PATH_BASE = \''.Mage::getBaseUrl('web').'app/code/local/Itoris/Installer/\';
						var INSTALLER_CALL_URL = \''.$this->getUrl('adminhtml/itorisinstaller_call/').'\';
							
						var _at = \''.$at.'\';
						var _installedProducts = eval(\'[\'+\''.json_encode($installedProducts).'\'+\']\')[0];
						var _avaliableProducts = document.getElementById("ii_avaliable_products").value;
						IKeysManager._defaultHosts = '.$defaultHostsJsArray.'
					</script>
					
					<div id="installer_container"></div>
					<div id="loading-process" style="display:none;visibility:hidden;"></div>
					
					<!--[if IE 7]>
						<style>
							.info-part{
								width:95%;
							}
						</style>
					<![endif]-->
				');
				$this->getLayout()->getBlock('content')->append($text);

				$installerVersion = implode('',IInstaller::getVersion('installer'));
				$jss = array(
					'itoris/installer/js/core.js',
					'itoris/installer/js/uibuilder.js',
					'itoris/installer/js/dialog.js',
					'itoris/installer/js/installation.js',
					'itoris/installer/js/product.js',
					'itoris/installer/js/progressbar.js',
					'itoris/installer/js/update.js',
					'itoris/installer/js/refresh.js',
					'itoris/installer/js/keys_manager.js'
				);

				$shouldMergeJs = Mage::getStoreConfigFlag('dev/js/merge_files');
				$v = !$shouldMergeJs ? '?v='.$installerVersion : '';

				foreach($jss as $js){
					$this->getLayout()->getBlock('head')->addJs($js.$v);
				}

				$csss = array(
					'itoris/installer/css/main.css',
				);
				$shouldMergeCss = Mage::getStoreConfigFlag('dev/css/merge_css_files');
				$v = !$shouldMergeCss ? '?v='.$installerVersion : '';

				foreach($csss as $css){
					$this->getLayout()->getBlock('head')->addItem('js_css', $css.$v);
				}

				$this->renderLayout();

			}catch(Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->loadLayout();
				$this->renderLayout();
			}
		}

		protected function _isAllowed(){
			return Mage::getSingleton('admin/session')->isAllowed('system/itoris_extensions/itoris_installer');
		}

		protected function getDefaultHosts() {
			$websites = Mage::app()->getWebsites(true);
			$hosts = array();
			foreach ($websites as $website) {
				$url = $this->getBaseUrl($website);
				if (!in_array($url, $hosts)) {
					$hosts[] = $url;
				}
				foreach ($website->getStores() as $store) {
					$url = $this->getBaseUrl($store);
					if (!in_array($url, $hosts)) {
						$hosts[] = $url;
					}
				}
			}

			return $hosts;
		}

		protected function getBaseUrl($obj) {
			$url = $obj->getConfig('web/unsecure/base_url');
			if (false !== strpos($url, '{{base_url}}')) {
				$baseUrl = Mage::getConfig()->substDistroServerVars('{{base_url}}');
				$url = str_replace('{{base_url}}', $baseUrl, $url);
			}
			return rtrim($url, '/') . '/';
		}
	}
?>