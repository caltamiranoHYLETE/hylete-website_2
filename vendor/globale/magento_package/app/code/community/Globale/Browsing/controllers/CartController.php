<?php

/**
 * Class Globale_Browsing_International_CheckoutController
 */
class Globale_Browsing_CartController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Protecting Quote object after order has been successfully created in GE side.
	 * Disengaging Quote from user session, will allow Quote to remain untouched until
	 * GE order create will convert the Quote into order in Magento
	 */
    public function clearAction() {
        Mage::getSingleton('checkout/session')->setQuoteId(null);
        return;
    }

}