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
 * Class Effy_Freshdesk_Model_Resource_Ticket
 */
class Effy_Freshdesk_Model_Resource_Ticket extends Varien_Object
{
    protected $_requester;

    public function getFreshdeskModel()
    {
        return Mage::getSingleTon('effy_freshdesk/freshdesk_tickets');
    }

    public function setRequester($requester)
    {
        $this->_requester = $requester;

        return $this;
    }

    public function getRequester()
    {
        return $this->_requester;
    }

    public function getIdFieldName()
    {
        return Effy_Freshdesk_Model_Freshdesk_Tickets::FIELD_DISPLAY_ID;
    }

    /**
     * @param Effy_Freshdesk_Model_Ticket $ticket
     * @param int                              $id
     * @param null                             $field
     *
     * @return array|null
     */
    public function load($ticket, $id, $field = null)
    {
        $requester = $this->getRequester() ? $this->getRequester() : $ticket->getRequester();
        return $ticket->addData(
            $this->getFreshdeskModel()
                ->getTicket($id, $requester)
        );
    }

    public function save(Effy_Freshdesk_Model_Ticket $ticket)
    {
        $this->getFreshdeskModel()
            ->setTicketFromArray($ticket->getData())
            ->createTicket();

        return $this;
    }
}