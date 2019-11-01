<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2014 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

/**
 * Class Effy_Freshdesk_Model_Source_Abstract
 */
abstract class Effy_Freshdesk_Model_Source_Abstract
{
	abstract public function toOptionArray();

	protected function _getHelper()
	{
		return Mage::helper('freshdesk');
	}

	public function toOptionHash()
	{
		$hash = array();
		foreach($this->toOptionArray() as $item) {
			$hash[$item['value']] = $item['label'];
		}
		
		return $hash;
	}
}