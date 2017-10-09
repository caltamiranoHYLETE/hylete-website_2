<?php

/**
 * Class Globale_Browsing_International_CheckoutController
 */
class Globale_Browsing_CheckoutController extends Mage_Core_Controller_Front_Action
{
    /**
     * Globale checkout index controller
     */
    public function indexAction() {

        // Send API sendCart Request
        // Load Checkout Page

        $Session = Mage::getSingleton("core/session");

        /** @var Globale_Browsing_Model_Checkout $Checkout */
        $Checkout = Mage::getModel('globale_browsing/checkout');

        // Redirect the customer to the customer login page,
        // in case of Login/Register before proceed to checkout flag is enabled
        if($Checkout->redirectCustomerToLoginPage()){
            // redirect to login/register page
            $CustomerLoginURL = Mage::getUrl('customer/account/login/ptc/1');
            Mage::app()->getResponse()->setRedirect($CustomerLoginURL);
            return false;
        }

        // $CartToken can be received via $_GET
        $CartToken = $this->getRequest()->getParam('token');

        // Check empty cart and redirect the user to Shopping Cart
        if($Checkout->isCartEmpty()) {
            Mage::app()->getResponse()->setRedirect(Mage::helper('checkout/cart')->getCartUrl());
            return false;
        }

        if(!$CartToken){

            $CartToken = $Session->getData('globale_cartToken');

            $Checkout = Mage::getModel('globale_browsing/checkout');
            $SendCart = $Checkout->SendCart($CartToken);

            if(!$SendCart->getSuccess()){
                Mage::getSingleton('checkout/session')->addError($this->__('Error occurred in international checkout.'));
                Mage::app()->getResponse()->setRedirect(Mage::helper('checkout/cart')->getCartUrl());
            }
            else{
                $CartToken = $SendCart->getData()->getCartToken();
            }
        }

        $Session->setData('globale_cartToken', $CartToken);

        $this->loadLayout();
        $this->renderLayout();
    }

}