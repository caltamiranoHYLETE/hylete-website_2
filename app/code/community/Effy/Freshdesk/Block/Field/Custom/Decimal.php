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
 * Class Effy_Freshdesk_Block_Field_Custom_Decimal
 */
class Effy_Freshdesk_Block_Field_Custom_Decimal extends Effy_Freshdesk_Block_Field_Text
{
    const CLASS_NAME_VALIDATE_NUMBER = 'validate-number';


    protected function _beforeToHtml()
    {
        $this->addFieldClass(self::CLASS_NAME_VALIDATE_NUMBER);

        return Effy_Freshdesk_Block_Field_Abstract::_beforeToHtml();
    }
}