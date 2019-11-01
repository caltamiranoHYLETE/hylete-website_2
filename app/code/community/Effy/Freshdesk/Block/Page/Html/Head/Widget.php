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
 * Class Effy_Freshdesk_Block_Page_Html_Head_Widget
 */
class Effy_Freshdesk_Block_Page_Html_Head_Widget extends Effy_Freshdesk_Block_Customer_Ticket_Abstract
{
    protected function _construct()
    {
        parent::_construct();
    }

    public function getFeedbackWidgetCode()
    {
        return Mage::helper('freshdesk')->getFeedbackWidgetCode();
    }

    protected function _toHtml()
    {
        if (Mage::helper('freshdesk')->isFeedbackWidgetEnabled()) {
            return parent::_toHtml();
        }

        return '';
    }
}
