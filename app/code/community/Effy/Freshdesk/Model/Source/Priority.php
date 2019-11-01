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
 * Class Effy_Freshdesk_Model_Source_Priority
 */
class Effy_Freshdesk_Model_Source_Priority extends Effy_Freshdesk_Model_Source_Abstract
{
    public function toOptionArray()
    {
        $priorities = Mage::getModel('effy_freshdesk/ticket')
            ->getCollection()
            ->getPriorities();

        $return = array();
        foreach ($priorities as $value => $label) {
            $return[] = array('value' => $value, 'label' => $label);
        }

        return $return;
    }
}