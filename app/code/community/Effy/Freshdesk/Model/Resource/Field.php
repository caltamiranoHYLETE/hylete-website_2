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
 * Class Effy_Freshdesk_Model_Resource_Field
 */
class Effy_Freshdesk_Model_Resource_Field extends Varien_Object
{
    public function getFreshdeskModel()
    {
        return Mage::getSingleTon('effy_freshdesk/freshdesk_fields');
    }
}