<?php

class Ebizmarts_BakerlooReports_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderPaymentMethod extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = parent::render($row);

        $label = Mage::helper('bakerloo_reports')->getLabelByCode($row['payment_method']);

        $result = "[{$row['payment_method']}]<br /> $label";

        return $result;
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row) {

        return $row['payment_method'];
    }
}