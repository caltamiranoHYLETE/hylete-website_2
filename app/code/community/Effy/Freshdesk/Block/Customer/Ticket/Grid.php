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
 * Class Effy_Freshdesk_Block_Customer_Ticket_Grid
 */
class Effy_Freshdesk_Block_Customer_Ticket_Grid extends Effy_Freshdesk_Block_Customer_Ticket_Abstract
{
    public function isSubjectColumnVisible() {
        return $this->isSubjectVisible();
    }

    public function isIdColumnVisible() {
        return $this->isFieldVisible(Effy_Freshdesk_Model_Field::FIELD_DISPLAY_ID);
    }

    public function isDateCreatedColumnVisible() {
        return $this->isFieldVisible(Effy_Freshdesk_Model_Field::FIELD_CREATED_AT);
    }

    public function isAgentColumnVisible() {
        return $this->isFieldVisible(Effy_Freshdesk_Model_Field::FIELD_AGENT);
    }

    public function isStatusColumnVisible() {
        return $this->isStatusVisible();
    }

    public function isPriorityColumnVisible() {
        return $this->isFieldVisible(Effy_Freshdesk_Model_Field::FIELD_PRIORITY);
    }

    public function getOrderUrl($orderId)
    {
        return $this->getUrl('sales/order/view', array('order_id' => $orderId));
    }
}
