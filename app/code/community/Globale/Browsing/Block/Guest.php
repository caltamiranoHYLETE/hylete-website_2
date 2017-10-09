<?php
/**
 * Manage all the "Proceed to checkout" button
 * Class Globale_Browsing_Block_Guest
 */
class Globale_Browsing_Block_Guest extends Mage_Core_Block_Template {


    /**
     * Validation for display/not display the proceed to checkout button
     * @return bool
     */
    public function IsDisplayProceedToCheckoutButton() {

        $IsOperatedByGlobale = Mage::registry('globale_user_supported');
        $IsProceedCheckout = Mage::app()->getRequest()->getParam('ptc');
        /** @var Globale_Browsing_Model_Checkout $Checkout */
        $Checkout = Mage::getModel('globale_browsing/checkout');
        if($IsOperatedByGlobale && $Checkout->isAllowLoginBeforeCheckout() && $IsProceedCheckout) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get the link for the international checkout page
     */
    public function getInternationalCheckoutPageLink() {

        $FrontName = (string)Mage::getConfig()->getNode('frontend/routers/browsing/args/frontName');
        return Mage::getUrl("{$FrontName}/checkout", array('_secure' => true,'ptc' => 1));
    }
}