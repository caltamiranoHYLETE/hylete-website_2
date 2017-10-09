<?php

use GlobalE\SDK\Core;

class Globale_Base_Model_Api2_Auth_Adapter extends Mage_Api2_Model_Auth_Adapter_Abstract {

	/**
	 * const of globale user
	 * @TODO change according to install file
	 */
	const GLOBALE_API_USERNAME = 'globale';


	/**
	 * Return stdClass with Admin type and Global-e REST API Admin user_id
	 * @param Mage_Api2_Model_Request $request
	 * @return object
	 * @throws Mage_Api2_Exception
	 */
	public function getUserParams(Mage_Api2_Model_Request $request){

		$GlobaleAdminUser = Mage::getModel('admin/user')->getCollection()->addFieldToFilter('username',self::GLOBALE_API_USERNAME )->getFirstItem()->getData();

		if(!isset($GlobaleAdminUser['user_id'])){
			throw new Mage_Api2_Exception('Global-e Admin identifier is not set',500);
		}
		$GlobaleUserId = $GlobaleAdminUser['user_id'];

		return  (object) array('type' => 'admin', 'id' => $GlobaleUserId);

	}


	/**
	 * Check if request contains authentication info for adapter - contain right MerchantGUID
	 * @param Mage_Api2_Model_Request $request
	 * @return bool
	 */
	public function isApplicableToRequest(Mage_Api2_Model_Request $request) {
		$BodyParams = $request->getBodyParams();

		if(!isset($BodyParams['MerchantGUID'])){
			return false;
		}

        /**@var $Initializer Globale_Base_Model_Initializer */
        $Initializer = Mage::getModel('globale_base/initializer');
        $SDKInitialized = $Initializer->initializeSDKRestMode();

		if ($SDKInitialized && $BodyParams['MerchantGUID'] === Core\Settings::get('MerchantGUID') ){
			return true;
		}
		return false;
	}

}