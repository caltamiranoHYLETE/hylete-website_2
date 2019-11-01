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
 * Class Effy_Freshdesk_Model_Resource_Note
 */
class Effy_Freshdesk_Model_Resource_Note extends Varien_Object
{
    public function getFreshdeskModel()
    {
        return Mage::getSingleTon('effy_freshdesk/freshdesk_notes');
    }

    /**
     * @param Effy_Freshdesk_Model_Note $note
     * @param int                            $id
     * @param null                           $field
     *
     * @return array|null
     */
    public function load($note, $id, $field = null)
    {
        return $note->addData(
            $this->getFreshdeskModel()
                ->getNote($id)
        );
    }

    public function save(Effy_Freshdesk_Model_Note $note)
    {
        $this->getFreshdeskModel()
            ->setDataFromArray($note->getData())
            ->saveNote();

        return $this;
    }
}