<?php

class Ebizmarts_BakerlooReports_Block_Adminhtml_Widget_Grid_Column_Renderer_CustomerName extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = parent::render($row);

        if((int)$row->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')->load($row->getCustomerId());

            if($customer->getId()) {
                $href = Mage::helper('adminhtml')->getUrl('adminhtml/customer/edit', array('id' => $row->getCustomerId()));
                $fullname = $customer->getFirstname() . ' ' . $customer->getLastname();
                $result = '<a href="' . $href . '" target="_blank">' . $fullname . '</a>';
            }
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
        $fullname = '';

        if((int)$row->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')->load($row->getCustomerId());

            if($customer->getId())
                $fullname = $customer->getFirstname() . ' ' . $customer->getLastname();
        }

        return $fullname;
    }
}