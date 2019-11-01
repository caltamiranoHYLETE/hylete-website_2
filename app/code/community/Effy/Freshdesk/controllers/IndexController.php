<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2014 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

/**
 * Class Effy_Freshdesk_IndexController
 */
class Effy_Freshdesk_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if (!Mage::helper('freshdesk')->isSSOEnabled()) {
            return $this->_redirect('*/ticket/list');
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $name     = $customer->getName();
            $email    = $customer->getEmail();
            $ssoUrl   = Mage::getSingleTon('effy_freshdesk/freshdesk')->getSSOUrl($name, $email);
            Mage::log($ssoUrl);
            $this->_redirectUrl($ssoUrl);
            $this->getResponse()->sendResponse();
            exit;
        } else {
            $this->_redirectUrl(Mage::helper('freshdesk/customer')->getLoginUrl());
        }

        return;
    }
}