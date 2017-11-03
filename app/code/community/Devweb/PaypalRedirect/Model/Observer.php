<?php

class Devweb_PaypalRedirect_Model_Observer
{
	public function catchRedirect($observer)
	{
		if (Mage::app()->getResponse()->isRedirect()) {
			exit;
		}
	}
}
