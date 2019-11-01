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
 * Class Effy_Freshdesk_Block_Customer_Ticket_Recent
 *
 * @method Effy_Freshdesk_Block_Customer_Ticket_Recent setTickets
 * @method array getTickets
 */
class Effy_Freshdesk_Block_Customer_Ticket_Recent extends Effy_Freshdesk_Block_Customer_Ticket_Grid
{
    public function __construct()
    {
        parent::__construct();

        if (Mage::helper('freshdesk')->isRecentTicketsGridEnabled()
            && Zend_Validate::is($this->getCustomerEmail(), 'EmailAddress')
        ) {
            $tickets = Mage::getResourceModel('freshdesk/ticket_collection')
                ->setRequester($this->getCustomerEmail())
                ->setOrder('created_at', 'DESC')
                ->setPageSize('5')
                ->setCurPage(1)
                ->load();

            $this->setTickets($tickets);
        }
    }

    public function getViewAllUrl()
    {
        return $this->getUrl('freshdesk/ticket/list');
    }

    protected function _toHtml()
    {
        if (Mage::helper('freshdesk')->isRecentTicketsGridEnabled() && is_object($this->getTickets()) && $this->getTickets()->getSize() > 0) {
            return parent::_toHtml();
        }

        return '';
    }
}
