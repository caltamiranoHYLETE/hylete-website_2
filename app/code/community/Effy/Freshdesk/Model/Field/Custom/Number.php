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
 * Class Effy_Freshdesk_Model_Field_Custom_Number
 */
class Effy_Freshdesk_Model_Field_Custom_Number extends Effy_Freshdesk_Model_Field
{
    public function checkFieldValue($value, &$allValues, &$skipValues)
    {
        parent::checkFieldValue($value, $allValues, $skipValues);

        if ($value && !is_numeric($value)) {
            throw new Effy_Freshdesk_Exception(Mage::helper('freshdesk')->__('Field "%s" is not valid, please re-enter', $this->getLabel()));
        }

        return true;
    }
}