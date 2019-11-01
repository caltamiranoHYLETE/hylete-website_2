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
 * Class Effy_Freshdesk_Model_Observer
 */
class Effy_Freshdesk_Model_Observer
{
    public function processCoreBlockAbstractToHtmlBefore($observer)
    {
        /*if ($this->isTicketTabEnabled() && ($observer->getBlock() instanceof Mage_Customer_Block_Account_Navigation)) {
            $observer->getBlock()->addLink(
                'freshdesk_tickets',
                'freshdesk/ticket/list',
                Mage::helper('freshdesk')->__('My Tickets')
            );
        }*/
    }

    public function processControllerActionPostdispatchContacts($event)
    {        
        if (!Mage::helper('freshdesk')->isContactUsFormEnabled()) {
            return;
        }        
        try {            
            $post = $event->getControllerAction()->getRequest()->getPost();
            if ($post) {
                $messages = $this->_getCustomerSession()->getMessages();
                if ($messages->getLastAddedMessage()->getType() == Mage_Core_Model_Message::SUCCESS) {
                    $check = Mage::getModel('effy_freshdesk/ticket')
                        ->setSubject(Mage::helper('freshdesk')->__('Contact Us form ticket from %s', $post['name']))
                        ->setEmail($post['email'])
                        ->setDescription($post['comment'] . "\n" . Mage::helper('contacts')->__('Telephone') . ': ' . $post['telephone'])
                        ->setStatus('Open')
                        ->setPriority(1)
                        ->save();

                    if ($check) {
                        $this->_getCustomerSession()->addSuccess(Mage::helper('freshdesk')->__('Ticket was created'));
                    } else {
                        $this->_getCustomerSession()->addError(Mage::helper('freshdesk')->__('Ticket wasn\'t created'));
                    }
                }
            }
        } catch (Effy_Freshdesk_Exception $mfe) {
            $this->_getCustomerSession()->addError(Mage::helper('freshdesk')->__('Ticket wasn\'t created'));
            Mage::logException($mfe);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function processSaveCustomer($observer)
    {
        if(!$this->isTicketTabEnabled()) {
            return;
        }

        try {
            $customer = $observer->getEvent()->getCustomer();
            if ($customer instanceof Mage_Customer_Model_Customer) {
                Mage::getModel('effy_freshdesk/user')->syncCustomer($customer);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    public function processSavedConfig($observer) {
        Mage::getModel('effy_freshdesk/freshdesk_tickets')->cleanCache();
        Mage::getModel('effy_freshdesk/freshdesk_users')->cleanCache();
        Mage::app()->removeCache('freshdesk_order_fields');
        Mage::app()->removeCache('freshdesk_ticket_fields');
    }

    protected function isTicketTabEnabled()
    {
        static $enabled;

        if (null === $enabled) {
            $enabled = Mage::helper('freshdesk')->isTicketTabEnabled();
        }

        return $enabled;
    }

    protected function isFeedbackWidgetEnabled()
    {
        static $enabled;

        if (null === $enabled) {
            $enabled = Mage::helper('freshdesk')->isFeedbackWidgetEnabled();
        }

        return $enabled;
    }

    protected function isSupportLinkEnabled()
    {
        static $enabled;

        if (null === $enabled) {
            $enabled = Mage::helper('freshdesk')->isSupportLinkEnabled();
        }

        return $enabled;
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }
}