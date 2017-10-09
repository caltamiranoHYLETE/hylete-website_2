<?php
class Globale_Base_Model_Adminhtml_System_Config_Source_Currency extends Mage_Adminhtml_Model_System_Config_Source_Currency {

	/**
	 * Create Option array of Currencies with Empty first option - for Non selected state
	 * @return array
	 */
	public function toOptionArray($isMultiselect)
	{
		$EmptyOption = array(
			"value" => "0",
			"label" => " "
		);

		$parentOptions = parent::toOptionArray($isMultiselect);
		$this->_options = array_merge(array($EmptyOption),$parentOptions);
		return $this->_options;
	}
}