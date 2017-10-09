<?php

abstract class Globale_Base_Model_Api2_Restapi extends Mage_Api2_Model_Resource {

	/**
	 * Process POST route_entity request as Global-e request, otherwise as regular Magento API REST request.
	 */
	public function dispatch(){

		switch  ($this->getActionType() . $this->getOperation()) {
			/* Create */
			case self::ACTION_TYPE_ENTITY . self::OPERATION_CREATE:
				$OutputData = $this->createOutputData();
				$this->_render($OutputData);
				break;

			default:
				parent::dispatch();
				break;
		}
	}

	/**
	 * Create Output data from SDK
	 * @return array
	 */
	abstract protected function createOutputData();
}