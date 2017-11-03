<?php

class Devweb_PaypalRedirect_Model_Paypal_Api_Nvp extends Mage_Paypal_Model_Api_Nvp
{
    protected function _construct()
    {
        parent::_construct();
        
        if ($this->isRequiredVersion()) {
            $this->_requiredResponseParams[static::DO_EXPRESS_CHECKOUT_PAYMENT] = array('ACK', 'CORRELATIONID');
        }
    }
    
    protected function _handleCallErrors($response)
    {
        try {
            parent::_handleCallErrors($response);
        } catch (Exception $e) {
            if (in_array($this->_callErrors[0], array(10486, 10417, 10422))) {
                Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::helper('paypalredirect')->getRedirectUrl());
                Mage::app()->getResponse()->sendResponse();
            }
            
            if ($this->isRequiredVersion()) {
                Mage::throwException(Mage::helper('paypal')->__('There was an error processing your order. Please contact us or try again later.'));
            } else {
                throw $e;
            }            
        }
    }
    
    protected function isRequiredVersion()
    {
        return version_compare(Mage::getVersion(), '1.9', '>=');
    }
}
