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
 * Class Effy_Freshdesk_Block_Field_Abstract
 */
class Effy_Freshdesk_Block_Field_Text extends Effy_Freshdesk_Block_Field_Abstract
{
    const CLASS_NAME_INPUT_TEXT = 'input-text';

    public function _construct()
    {
        $this->setTemplate('freshdesk/field/text.phtml');

        parent::_construct();
    }

    protected function _beforeToHtml()
    {
        $this->addFieldClass(self::CLASS_NAME_INPUT_TEXT);

        return parent::_beforeToHtml();
    }
}