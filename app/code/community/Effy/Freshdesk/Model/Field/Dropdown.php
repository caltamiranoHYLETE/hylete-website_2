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
 * Class Effy_Freshdesk_Model_Field_Custom_Dropdown
 */
class Effy_Freshdesk_Model_Field_Dropdown extends Effy_Freshdesk_Model_Field
{
    public function checkFieldValue($value, &$allValues, &$skipValues)
    {
        parent::checkFieldValue($value, $allValues, $skipValues);

        if ($value !== '') {
            $check = false;
            foreach ($this->getChoices() as $label => $choice) {
                if(!is_numeric($label)) {
                    if($value == $choice) {
                        $check = true;
                        break;
                    }
                }
                else {
                    if ($value == $choice[0]) {
                        $check = true;
                        break;
                    }
                }
            }

            if (!$check) {
                throw new Effy_Freshdesk_Exception(Mage::helper('freshdesk')->__('Field "%s" is not valid, please re-enter', $this->getLabel()));
            }
        }

        return true;
    }
}