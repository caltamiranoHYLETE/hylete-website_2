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
 * Class Effy_Freshdesk_Adminhtml_Freshdesk_TicketController
 */
class Effy_Freshdesk_Adminhtml_Freshdesk_TicketController extends Mage_Adminhtml_Controller_Action
{

    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::helper('freshdesk')->isConfigSet()) {
            $this->_getSession()->addError($this->__('Please fill in Freshdesk configuration options first'));
            $this->_redirect('*/system_config/edit/section/freshdesk');
            $this->getResponse()->sendResponse();
            exit;
        }

        return $this;
    }

    /**
     * Displays the tickets overview grid.
     */
    public function indexAction()
    {
        return $this->_redirect('*/freshdesk/ticket');
    }

    /**
     * Displays the tickets overview grid.
     */
    public function refreshAction()
    {
        $this->_refreshCache();

        return $this->_redirect('*/freshdesk/ticket');
    }

    /**
     * Displays the tickets overview grid.
     */
    public function createAction()
    {
        try {
            return $this->loadLayout()
                ->_setActiveMenu('freshdesk/ticket')
                ->_title($this->__('Freshdesk'))
                ->_title($this->__('Create Ticket'))
                ->_addContent($this->getLayout()->createBlock('effy_freshdesk/adminhtml_ticket_edit'))
                ->renderLayout();

        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        return $this->_redirect('*/freshdesk/ticket');
    }

    public function saveAction()
    {
        try {
            $post = $this->getRequest()->getPost();
            if (empty($post)) {
                throw new Effy_Freshdesk_Exception($this->__('Wrong request data'));
            }

            Mage::getModel('effy_freshdesk/ticket')
                ->setData($post)
                ->save();

            $this->_getSession()->addSuccess($this->__('Ticket was successfully created'));

            $this->_refreshCache(false);

            if (!$this->getRequest()->getParam('back')) {
                return $this->_redirect('*/freshdesk/ticket');
            }

        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        return $this->_redirect('*/freshdesk_ticket/create');
    }

    public function editAction()
    {
        $ticketId = (int)$this->getRequest()->getParam('ticket_id');
        $this->_redirectUrl(Mage::getSingleTon('effy_freshdesk/freshdesk')->getTicketEditUrl($ticketId));
        Mage::app()->getResponse()->sendResponse();
        exit;
    }

    public function viewAction()
    {
        $ticketId = (int)$this->getRequest()->getParam('ticket_id');
        $this->_redirectUrl(Mage::getSingleTon('effy_freshdesk/freshdesk')->getTicketViewUrl($ticketId));
        Mage::app()->getResponse()->sendResponse();
        exit;
    }


    public function closeAction()
    {
        $ticketId   = (int)$this->getRequest()->getParam('ticket_id');
        $customerId = (int)$this->getRequest()->getParam('customer_id');

        try {
            if (Mage::getModel('effy_freshdesk/ticket')->close($ticketId)) {
                $this->_getSession()->addSuccess($this->__('Ticket was successfully closed'));
                $this->_refreshCache(false);
            } else {
                $this->_getSession()->addError($this->__('Ticket wasn\'t closed'));
            }

        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        if ($customerId) {
            return $this->_redirect('*/customer/edit', array('id' => $customerId));
        } else {
            return $this->_redirect('*/freshdesk/ticket');
        }
    }

    protected function _refreshCache($message = true)
    {
        Mage::getModel('effy_freshdesk/cache')->clean();
        Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => Effy_Freshdesk_Model_Cache::CACHE_TYPE));

        if ($message) {
            $this->_getSession()->addSuccess($this->__('Cache was successfully refreshed'));
        }
    }
}