<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderNumber extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = parent::render($row);

        if((int)$row->getOrderId()) {
            $href   = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getOrderId()));

            $incrementId = $row->getOrderIncrementId();
            if(!$incrementId)
                $incrementId = Mage::getModel('sales/order')->load($row->getOrderId())->getIncrementId();

            $result = '<a href="' . $href . '" target="_blank">' . $incrementId . '</a>';
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