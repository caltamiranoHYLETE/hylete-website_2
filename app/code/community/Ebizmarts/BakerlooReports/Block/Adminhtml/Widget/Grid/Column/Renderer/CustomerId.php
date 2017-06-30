<?php

class Ebizmarts_BakerlooReports_Block_Adminhtml_Widget_Grid_Column_Renderer_CustomerId extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = parent::render($row);

        if((int)$row->getCustomerId()) {
            $href = Mage::helper('adminhtml')->getUrl('adminhtml/customer/edit', array('id' => $row->getCustomerId()));

            $result = '<a href="' . $href . '" target="_blank">' . $row->getCustomerId() . '</a>';
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

        return (int)$row->getCustomerId();
    }
}