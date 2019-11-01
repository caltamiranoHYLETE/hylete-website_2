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
 * Class Effy_Freshdesk_Model_Note
 *
 * @method Effy_Freshdesk_Model_Note setTicketId
 * @method Effy_Freshdesk_Model_Note setBody
 * @method Effy_Freshdesk_Model_Note setUserName
 * @method int|null getTicketId
 * @method int|null getUserId
 * @method string getUserName
 * @method Effy_Freshdesk_Model_User getUser
 * @method Effy_Freshdesk_Model_Note setUser
 */
class Effy_Freshdesk_Model_Note extends Effy_Freshdesk_Model_Abstract
{
    const NOTE = Effy_Freshdesk_Model_Freshdesk_Notes::NOTE;

    protected function _construct()
    {
        parent::_construct();

        $this->_init('effy_freshdesk/note');
    }

    /**
     * @param array                            $notes
     * @param Effy_Freshdesk_Model_Ticket $ticket
     *
     * @return array
     */
    public function parseTicketNotes($ticket, $notes = null)
    {
        $this->setTicketId($ticket->getId());

        $notesParsed = array();

        if (null === $notes) {
            $notes = $ticket->getNotes();
        }

        foreach ($notes as $note) {
            $noteModel = clone $this;

            if (!empty($note[self::NOTE])) {
                $noteModel->addData($note[self::NOTE]);
            } else {
                $noteModel->addData($note);
            }

            if ($noteModel->getUserId() > 0) {
                $user = Mage::getModel('effy_freshdesk/user')->load($noteModel->getUserId());
                $noteModel->setUser($user);
                $noteModel->setUserName($user->getName());
                unset($user);
            }

            $notesParsed[] = $noteModel;
        }

        return $notesParsed;
    }
}