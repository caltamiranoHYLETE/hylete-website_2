<?php

class Globale_Base_Model_Adminhtml_System_Config_Source_Store extends Mage_Adminhtml_Model_System_Config_Source_Store {

	/**
	 * Create Option array of Stores with Empty first option - for Non selected state
	 * @return array
	 */
	public function toOptionArray(){

		$EmptyOption = array(
			"value" => "0",
			"label" => " "
		);

		$parentOptions = parent::toOptionArray();
		$this->_options = array_merge(array($EmptyOption),$parentOptions);
		return $this->_options;
	}
}