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
 * Class Effy_Freshdesk_Block_Adminhtml_Ticket_Grid_Column_Renderer_Ordersincrements
 */
class Effy_Freshdesk_Block_Adminhtml_Ticket_Grid_Column_Renderer_Orderincrements
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        if ($index = $row->getData($this->getColumn()->getIndex())) {
            $orderId = $row->getData(Effy_Freshdesk_Model_Ticket::ORDER_ID);
            if ($orderId) {
                return '<a href="' . Mage::getUrl('*/sales_order/view/', array('order_id' => $orderId)) . '">#' . $index . '</strong>';
            } else {
                return $index;
            }
        }

        return '---';
    }
}
