<?php

use GlobalE\SDK\Models\Common;


class Globale_Base_Model_Api2_Rest_Admin_V1 extends Globale_Base_Model_Api2_Restapi  {

	/**
	 * Create Output data per international/cache/:action request
	 * @return array
	 */
	protected function createOutputData(){

		$Action = $this->getRequest()->getParam('action');
		$requestData = $this->getRequest()->getBodyParams();

		// @TODO IMPLEMENT  LOGIC  !!!

		$Success = true;
		$Message = 'Base Message';

		$response = new Common\Response($Success, $Message);

		$OutputData = $response->getObjectVars();
		return $OutputData;
	}








}