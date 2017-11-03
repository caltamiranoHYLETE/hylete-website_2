<?php

class Devweb_PaypalRedirect_Model_PaypalUk_Api_Nvp extends Mage_PaypalUk_Model_Api_Nvp
{
    protected function _handleCallErrors($response)
    {
        if ($response['RESULT'] != self::RESPONSE_CODE_APPROVED && preg_match('/(10486|10417|10422)/', $response['RESPMSG'])) {
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::helper('paypalredirect')->getRedirectUrl());
            Mage::app()->getResponse()->sendResponse();
        }
        
        parent::_handleCallErrors($response);
    }
}
