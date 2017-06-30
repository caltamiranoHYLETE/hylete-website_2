<?php

class Ebizmarts_BakerlooReports_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderNumber extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = parent::render($row);

        if((int)$row->getOrderId()) {
            $href = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getOrderId()));

            $result = '<a href="' . $href . '" target="_blank">' . $row->getOrderIncrementId() . '</a>';

        }

        return $result;
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row) {
        return $row->getOrderIncrementId();
    }
}