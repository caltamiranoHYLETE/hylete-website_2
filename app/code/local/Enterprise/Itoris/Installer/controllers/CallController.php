<?php
	class Itoris_Installer_CallController extends Mage_Adminhtml_Controller_Action {

		public function __construct($request, $response, $invokeArgs = array()) {
			parent::__construct($request, $response, $invokeArgs);
		}

		public function indexAction() {
			header("HTTP/1.0 200 OK");
			header("Content-Type: text/xml");

			$task = $this->getRequest()->getPost('task');
			if ($task == 'apiRequest') {
				$action = $this->getRequest()->getPost('action');
				if (method_exists('IInstaller',$action)) {
					ini_set("pcre.recursion_limit", "524");
					IInstaller::$action($this->getRequest()->getPost());
				} else {
					echo 'Error. Unknown Api request.';
				}
				
			} else if ($task == 'ping') {
				echo json_encode(IInstaller::testItorisApiConnection());
			} else if ($task == 'getInstalledProducts') {
				echo json_encode(IInstaller::getInstalledProducts());
			}

			exit;
		}
		
		protected function _isAllowed() {
    		return Mage::getSingleton('admin/session')->isAllowed('system/itoris_extensions/itoris_installer');
    	}
	}

	require_once dirname(__FILE__).DS.'..'.DS.'code'.DS.'IInstaller.php';
?>