<?php

class Bestworlds_KlaviyoExtend_MainController extends Mage_Core_Controller_Front_Action
{

    protected function _ajaxResponse($result = array())
    {
        $this->getResponse()->setBody(Zend_Json::encode($result));
        return;
    }

    public function registeremailAction()
    {
        $response   = [];
        $email      = Mage::app()->getRequest()->getParam('email');

        if ($email && Zend_Validate::is($email, 'EmailAddress')) {
            //save email to cookie
            $observer       = Mage::getModel('klaviyoextend/observer');
            $encodedEmail   = Mage::helper('klaviyoextend')->encryptMe($email);
            Mage::getModel("core/cookie")->set($observer->getCookieName(), $encodedEmail, 0, "/", null, null, false);
            $response= ['success' => 'Klaviyo email saved into cookie'];
        } else {
            $response= ['error' => 'Please try again later'];
        }
        $this->_ajaxResponse($response);
    }
}

