<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2013 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

interface Effy_Freshdesk_Model_Freshdesk_Interface
{
	/**
	 * @return Effy_Freshdesk_Model_Freshdesk
	 */
	public function getFreshdesk();

	public function request($method = null);
}