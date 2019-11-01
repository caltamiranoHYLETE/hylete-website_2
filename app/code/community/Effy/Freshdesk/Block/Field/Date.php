<?php
/**
 * Class Effy_Freshdesk_Block_Field_Abstract
 */
class Effy_Freshdesk_Block_Field_Date extends Effy_Freshdesk_Block_Field_Abstract
{
    public function _construct()
    {
        $this->setTemplate('freshdesk/field/date.phtml');
        parent::_construct();
    }
}