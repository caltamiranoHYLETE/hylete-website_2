<?php

/**
 * Class Globale_Browsing_Block_Checkout
 */
class Globale_Browsing_Block_Checkout extends Mage_Core_Block_Template {

    public function generateCartIframe(){

        /**@var $GlobaleSDK \GlobalE\SDK\SDK */
        $GlobaleSDK = Mage::registry('globale_sdk');

        $Session = Mage::getSingleton("core/session");
        $CartToken = $Session->getData('globale_cartToken');

        // Generate checkout page
        $CheckoutPage = $GlobaleSDK->Checkout()->GenerateCheckoutPage($CartToken);
        if(!$CheckoutPage->getSuccess()){
            Mage::getSingleton('checkout/session')->addError($this->__('Error occurred in international checkout.'));
            Mage::app()->getResponse()->setRedirect(Mage::helper('checkout/cart')->getCartUrl());
        }else{
            return $CheckoutPage->getData();
        }
    }

    public function getJsOnSuccess(){
        /**@var $BaseSetting Globale_Base_Model_Settings */
        $Settings = Mage::getModel('globale_base/settings');
        return $Settings->getJsOnSuccess();
    }


    /**
     * Get the Global-e checkout page URL
     * @return string
     * @access public
     */
    public function getGlobaleCheckoutPageURL() {
        /** @var Globale_Browsing_Helper_Checkout $CheckoutHelper */
        $CheckoutHelper = Mage::helper('globale_browsing/checkout');
        return $CheckoutHelper->getGlobaleCheckoutPageURL();
    }

    /**
     * Redirect the customer to Global-e checkout page after Login/Register
     * @access public
     */
    public function setRedirectCustomerToCheckoutPage() {
        /** @var Globale_Browsing_Model_Checkout $Checkout */
        $Checkout = Mage::getModel('globale_browsing/checkout');
        $Checkout->redirectCustomerToCheckoutPage();
    }
}