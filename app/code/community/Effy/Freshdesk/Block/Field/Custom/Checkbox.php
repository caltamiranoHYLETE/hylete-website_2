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
 * Class Effy_Freshdesk_Block_Field_Custom_Checkbox
 */
class Effy_Freshdesk_Block_Field_Custom_Checkbox extends Effy_Freshdesk_Block_Field_Abstract
{
    const CLASS_NAME_CHECKBOX = 'checkbox';

    protected $_fieldRowTemplate = 'freshdesk/field/row_inline.phtml';
    protected $_fieldRowAdminTemplate = 'freshdesk/field/row_1_column.phtml';

    public function _construct()
    {
        $this->setTemplate('freshdesk/field/checkbox.phtml');

        parent::_construct();
    }

    protected function _beforeToHtml()
    {
        $this->addFieldClass(self::CLASS_NAME_CHECKBOX);

        return parent::_beforeToHtml();
    }
}