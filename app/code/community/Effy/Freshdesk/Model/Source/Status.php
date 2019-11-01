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
 * Class Effy_Freshdesk_Model_Source_Status
 */
class Effy_Freshdesk_Model_Source_Status extends Effy_Freshdesk_Model_Source_Abstract
{
    public function toOptionArray()
    {
        $statuses = Mage::getModel('effy_freshdesk/ticket')
            ->getCollection()
            ->getStatuses();

        $return = array();
        foreach ($statuses as $value => $label) {
            $return[] = array('value' => $value, 'label' => $label);
        }

        return $return;
    }
}
