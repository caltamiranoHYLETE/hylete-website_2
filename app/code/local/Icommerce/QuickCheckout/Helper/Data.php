<?php

class Icommerce_QuickCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_expandLoginForm;
    protected $_enableSeparateShippingAddress;
    protected $_autoFillCustomerAddress;
    protected $_showForgotPasswordLink;
    protected $_agreementsUrl;
    protected $_enableAcceptTermsPopup;
    protected $_paymentAsFirstStep;

	/**
	  * Checks if we should expand loginform or not
	  *
	  * @param   none
	  * @return  bool
	*/
	public function expandLoginForm()
	{
        if( is_null($this->_expandLoginForm) ){
            $this->_expandLoginForm = Mage::getStoreConfig('quickcheckout/settings/expand_login_form');
        }
		return $this->_expandLoginForm;
	}

	/**
	  * Checks if we should enable separate shipping address
	  *
	  * @param   none
	  * @return  bool
	*/
	public function enableSeparateShippingAddress()
	{
        if( is_null($this->_enableSeparateShippingAddress) ){
            $this->_enableSeparateShippingAddress = Mage::getStoreConfig('quickcheckout/settings/enable_separate_shipping_address');
        }
		return $this->_enableSeparateShippingAddress;
	}

	/**
	  * Checks if we should expand loginform or not
	  *
	  * @param   none
	  * @return  bool
	*/
	public function autofillCustomerAddress()
	{
        if( is_null($this->_autoFillCustomerAddress) ){
            $this->_autoFillCustomerAddress = Mage::getStoreConfig('quickcheckout/settings/autofill_customer_address');
        }
		return $this->_autoFillCustomerAddress;
	}

	/**
	  * Checks if we should show 'Forgot password' link in checkout
	  *
	  * @param   none
	  * @return  bool
	*/
	public function showForgotPasswordLink()
	{
        if( is_null($this->_showForgotPasswordLink) ){
            $this->_showForgotPasswordLink = Mage::getStoreConfig('quickcheckout/settings/show_forgot_password_link');
        }
		return $this->_showForgotPasswordLink;
	}

    public function getAgreementsUrl(){
        if( is_null($this->_agreementsUrl) ){
            $this->_agreementsUrl = Mage::getUrl('quickcheckout/Agreements/showAgreements');
        }
        return $this->_agreementsUrl;
    }

    public function enableAcceptTermsPopup(){
        if( is_null($this->_enableAcceptTermsPopup) ){
            $this->_enableAcceptTermsPopup = Mage::getStoreConfig('quickcheckout/settings/enable_accept_terms_popup');
        }
        return $this->_enableAcceptTermsPopup;
    }

    /**
     * Check if payment should be the first step.
     * If Klarna is enabled payment will always be the first step.
     *
     * @return bool
     */
    public function paymentAsFirstStep(){
        if( is_null($this->_paymentAsFirstStep) ){
            $this->_paymentAsFirstStep = (bool)Mage::getStoreConfig('quickcheckout/settings/payment_as_first_step');
            $isKlarnaActive = (bool) Mage::getStoreConfig('payment/kreditor_invoice/active') || (bool) Mage::getStoreConfig('payment/kreditor_partpayment/active');

            if ($this->_paymentAsFirstStep === false && $isKlarnaActive === true) {
                $this->_paymentAsFirstStep = true;
            }
        }
        return $this->_paymentAsFirstStep;
    }
}