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
 * Class Effy_Freshdesk_Model_Source_Ticketview
 */
class Effy_Freshdesk_Model_Source_Ticketview extends Effy_Freshdesk_Model_Source_Abstract
{
    const NO  = 0;
    const YES = 1;

    public function toOptionArray()
    {
        $return   = array();
        $return[] = array('value' => self::NO, 'label' => $this->_getHelper()->__('No, they will have to use your Freshdesk portal'));
        $return[] = array('value' => self::YES, 'label' => $this->_getHelper()->__('Yes, My Account will have My Tickets to view, reply and close tickets'));

        return $return;
    }
}
