<?php
/**
 * Best Worlds
 * http://www.bestworlds.com
 * 888-751-5348
 * 
 * Need help? contact us:
 *  http://www.bestworlds.com/contact-us
 * 
 * Want to customize or need help with your store?
 *  Phone: 888-751-5348
 *  Email: info@bestworlds.com
 *
 * @category    Bestworlds
 * @package     Bestworlds_AbandonedCart
 * @copyright   Copyright (c) 2018 Best Worlds
 * @license     http://www.bestworlds.com/software_product_license.html
 */

/**
 * Main controller
 *
 * @category   Bestworlds
 * @package    Bestworlds_AbandonedCart
 * @author     Best Worlds Team <info@bestworlds.com>
 */
class Bestworlds_AbandonedCart_MainController extends Mage_Core_Controller_Front_Action
{
    public function getSavedEmailAction()
    {
        $data       = array();
        $observer   = Mage::getModel('abandonedcart/observer');
        $cookie     = Mage::getModel('core/cookie')->get($observer->getCookieName());
        $cookie     = Mage::helper('abandonedcart')->decryptMe($cookie);

        if ($cookie) {
            $data['emailValue'] = $cookie;
        }

        echo json_encode($data);
    }

    protected function _ajaxResponse($result = array())
    {
        $this->getResponse()->setBody(Zend_Json::encode($result));
        return;
    }

    public function registeremailAction()
    {
        $response   = [];
        $email      = Mage::app()->getRequest()->getParam('email');
        $type       = Mage::app()->getRequest()->getParam('type');

        if ($type && $email && Zend_Validate::is($email, 'EmailAddress')) {

            //save email to cookie
            $observer       = Mage::getModel('abandonedcart/observer');
            $encodedEmail   = Mage::helper('abandonedcart')->encryptMe($email);
            Mage::getModel("core/cookie")->set($observer->getCookieName(), $encodedEmail, 0, "/", null, null, false);

            $quote = Mage::getSingleton('checkout/session')->getQuote();

            switch ($type) {
                case 'checkout':
                default:
                    //REGISTER EMAIL CAPTURED FROM DURING_CHECKOUT
                    $this->_updateQuote($quote, $email, Bestworlds_AbandonedCart_Model_Capturetypes::DURING_CHECKOUT);
                    break;
                case 'add2cart':
                    //REGISTER EMAIL CAPTURED FROM ADD TO CART PROMPT
                    if($quote->getId() && !$quote->getCustomerEmail()){
                        $this->_updateQuote($quote, $email, Bestworlds_AbandonedCart_Model_Capturetypes::ADD2CARTPROMPT);
                    }else{
                        //add session to indicate that we recover this email via add2cart prompt, in case the quote doesn't exist yet
                        Mage::getSingleton("core/session")->setBwCapture(Bestworlds_AbandonedCart_Model_Capturetypes::ADD2CARTPROMPT);
                    }
                    $response= ['success' => 'Email Registered'];
                    break;
            }
        } else {
            $response= ['error' => 'Please try again later'];
        }
        $this->_ajaxResponse($response);
    }

    protected function _updateQuote($quote=false, $email= false, $type= false)
    {
        if (!$quote) { return false; }
        if (!$type)  { return false; }

        if (($quote->getCustomerEmail() != $email) || ($quote->getData('email_captured_from')=='') ) {
            if($email) $quote->setCustomerEmail($email);
            $quote->setData('email_captured_from', $type);
            $quote->save();
            $observer = Mage::getModel('abandonedcart/observer');
            if($quote->getData('email_captured_from') == Bestworlds_AbandonedCart_Model_Capturetypes::ADD2CARTPROMPT) {
                $observer->sendKlaviyoTrack($quote);
            }
        }
        return true;
    }
}
